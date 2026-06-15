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
                self::$instance = new Medoo([
                    'type' => 'sqlite',
                    'database' => env('DB_DATABASE', database_path('plant_diary.sqlite')),
                ]);
            }
        }
        return self::$instance;
    }
}
