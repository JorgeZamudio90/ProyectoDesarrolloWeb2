<?php

namespace Alexc\ProyectoAgustin\Core;

use PDO;
use PDOException;

class Database
{
    private static $connection;

    public static function getConnection()
    {
        if (!self::$connection) {
            $host = DB_HOST;
            $dbname = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;

            try {
                self::$connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}