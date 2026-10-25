<?php

namespace App\Models;

use PDO;

class PDOFactory
{
    public function create(array $config): PDO
    {
        $dbhost = $config['dbhost'] ?? 'localhost';
        $dbport = $config['dbport'] ?? '5432';
        $dbname = $config['dbname'] ?? 'nhatro_db';
        $dbuser = $config['dbuser'] ?? 'postgres';
        $dbpass = $config['dbpass'] ?? '0000';

        $dsn = "pgsql:host={$dbhost};port={$dbport};dbname={$dbname};";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $dbuser, $dbpass, $options);
    }
}
