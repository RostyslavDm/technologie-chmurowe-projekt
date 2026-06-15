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

    // Upewnia sie, ze plik bazy SQLite istnieje i ma utworzone tabele.
    // Gdy go brak (np. pierwszy start na trwalym dysku /home) - tworzy go ze schematu.
    private static function prepareSqlite(string $path): bool {
        try {
            if (is_file($path) && filesize($path) > 0) {
                return true;
            }
            $dir = dirname($path);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
                return false;
            }
            $schema = base_path('database/plant_diary_sqlite.sql');
            if (!is_file($schema)) {
                return false;
            }
            $pdo = new \PDO('sqlite:' . $path);
            $pdo->exec(file_get_contents($schema));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
