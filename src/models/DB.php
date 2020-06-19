<?php

namespace App\Models;

use PDO;

class DB
{
    /**
     * @return PDO
     */
    public function connect()
    {
        return new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASSWORD,
            [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            ]
        );
    }
}