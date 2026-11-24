<?php

namespace Config;

use PDO;
use PDOException;

class Database
{
    private string $host;
    private string $port;
    private string $database;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->host = (string) Env::get('DB_HOST', 'localhost');
        $this->port = (string) Env::get('DB_PORT', '3306');
        $this->database = (string) Env::get('DB_NAME', 'ElGranoDorado');
        $this->username = (string) Env::get('DB_USER', 'root');
        $this->password = (string) Env::get('DB_PASS', '');
    }

    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

            $connection = new PDO(
                $dsn,
                $this->username,
                $this->password
            );

            $connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            $connection->setAttribute(
                PDO::ATTR_EMULATE_PREPARES,
                false
            );

            return $connection;
        } catch (PDOException $e) {
            throw new PDOException(
                'Error al conectar con la base de datos.'
            );
        }
    }
}