<?php
declare(strict_types=1);

namespace App;

use PDO;
use Dotenv\Dotenv;

class Database
{
    /**
     * Get a PDO database connection.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        // Load environment variables from the .env file in the root directory
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Retrieve the database connection variables from the environment
        $host = (string)($_ENV['DB_HOST'] ?? 'localhost');
        $dbname = (string)($_ENV['DB_NAME'] ?? 'default_database');
        $username = (string)($_ENV['DB_USER'] ?? 'root');
        $password = (string)($_ENV['DB_PASS'] ?? '');

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
