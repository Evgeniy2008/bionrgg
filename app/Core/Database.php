<?php

namespace App\Core;

use mysqli;
use RuntimeException;

class Database
{
    private mysqli $connection;

    public function __construct(private Config $config)
    {
        $host = $this->config->require('DB_HOST');
        $name = $this->config->require('DB_NAME');
        $user = $this->config->require('DB_USER');
        $pass = $this->config->require('DB_PASS');

        $this->connection = @new mysqli($host, $user, $pass, $name);

        if ($this->connection->connect_errno) {
            throw new RuntimeException('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}




