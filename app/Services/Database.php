<?php

namespace App\Services;

use Medoo\Medoo;

class Database {
    private static ?Medoo $instance = null;

    // singleton, bo jedno polaczenie i tworzone tylko raz (nie wierze, czego uczylam sie naprawde sie przydalo...)
    public static function getInstance(): Medoo {
        if (self::$instance === null) {
            self::$instance = new Medoo([
                'type' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'database' => env('DB_DATABASE', 'loan_calculator'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
            ]);
        }
        return self::$instance;
    }
}
