<?php
/** Koneksi PDO bersama untuk seluruh model. */
class Database
{
    // Satu koneksi PDO digunakan bersama oleh seluruh model selama request berjalan.
    private static ?PDO $connection = null;

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Sesuaikan kredensial ini dengan instalasi MySQL/MariaDB lokal.
            $host = 'localhost';
            $dbName = 'db_cptracker';
            $username = 'root';
            $password = '';
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

            // Exception memudahkan penanganan error; emulate prepares dimatikan.
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$connection;
    }
}
