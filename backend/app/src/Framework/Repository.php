<?php

namespace App\Framework;

use App\Config;
use PDO;

class Repository
{
    protected PDO $connection;

    public function __construct()
    {
        try {
            $connectionString = 'mysql:host=' . Config::DB_HOST . ';dbname=' .
                Config::DB_NAME . ';charset=utf8mb4';

            $this->connection = new PDO(
                $connectionString,
                Config::DB_USERNAME,
                Config::DB_PASSWORD
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die('Database Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
