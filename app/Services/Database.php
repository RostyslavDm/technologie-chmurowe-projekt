<?php

namespace App\Services;

use Medoo\Medoo;

class Database {
    private static ?Medoo $instance = null;

    // singleton, bo jedno polaczenie i tworzone tylko raz (nie wierze, czego uczylam sie naprawde sie przydalo...)
    public static function getInstance(): Medoo {
        if (self::$instance === null) {
            // DB_TYPE wybiera silnik bazy:
            //  - 'sqlite' (domyslnie) -> dziala na Azure bez osobnego serwera bazy
            //  - 'mysql'              -> lokalny XAMPP/MySQL
            if (env('DB_TYPE', 'sqlite') === 'mysql') {
                self::$instance = new Medoo([
                    'type' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'database' => env('DB_DATABASE', 'plant_diary'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                ]);
            } else {
                // Sciezka bazy SQLite. Na Azure ustawiamy ja na /home/... (trwale po restarcie).
                $path = env('DB_DATABASE', database_path('plant_diary.sqlite'));
                if (!self::prepareSqlite($path)) {
                    // awaryjnie: baza wbudowana w obraz (apka dziala, ale dane znikaja po restarcie)
                    $path = database_path('plant_diary.sqlite');
                }
                self::$instance = new Medoo([
                    'type' => 'sqlite',
                    'database' => $path,
                ]);
            }
        }
        return self::$instance;
    }

    // Upewnia sie, ze plik bazy SQLite istnieje, ma utworzone tabele
    // oraz ze istnieje konto administratora (panel admina musi byc demonstrowalny).
    // Gdy pliku brak (np. pierwszy start na trwalym dysku /home) - tworzy go ze schematu.
    private static function prepareSqlite(string $path): bool {
        try {
            $dir = dirname($path);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
                return false;
            }

            $fresh = !is_file($path) || filesize($path) === 0;

            $pdo = new \PDO('sqlite:' . $path);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            // Pierwszy start (brak pliku) -> tworzymy strukture i dane startowe ze schematu.
            if ($fresh) {
                $schema = base_path('database/plant_diary_sqlite.sql');
                if (!is_file($schema)) {
                    return false;
                }
                $pdo->exec(file_get_contents($schema));
            }

            // Idempotentnie: dopilnuj, by istnialo konto admina
            // (tworzy je raz, przy kolejnych startach nic nie robi).
            self::ensureAdmin($pdo);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Tworzy domyslne konto administratora, jezeli jeszcze nie istnieje.
    // Potrzebne, bo rejestracja nadaje tylko role 'user' - bez tego panel admina
    // bylby niedostepny po wdrozeniu. Login: admin@plantcare.app  Haslo: Admin12345
    private static function ensureAdmin(\PDO $pdo): void {
        $email = 'admin@plantcare.app';

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();

        if ($userId === false) {
            $hash = password_hash('Admin12345', PASSWORD_BCRYPT);
            $pdo->prepare('INSERT INTO users (nickname, password, email) VALUES (?, ?, ?)')
                ->execute(['admin', $hash, $email]);
            $userId = (int) $pdo->lastInsertId();
            $pdo->prepare('UPDATE users SET created_by = ? WHERE id = ?')
                ->execute([$userId, $userId]);
        }

        // Przypisz role 'admin' (UNIQUE(user_id, role_id) chroni przed duplikatem).
        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'admin'");
        $roleStmt->execute();
        $roleId = $roleStmt->fetchColumn();
        if ($roleId !== false) {
            $pdo->prepare('INSERT OR IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)')
                ->execute([(int) $userId, (int) $roleId]);
        }
    }
}
