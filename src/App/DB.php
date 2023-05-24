<?php

namespace src\App;

class DB
{
    private static $db = null;

    public static function getDB()
    {
        if (is_null(self::$db)) {
            self::$db = new \PDO("mysql:host=localhost;dbname=test;charset=utf8mb4;", "root", "");
        }

        return self::$db;
    }

    public static function execute($sql, $data = [])
    {
        $q = self::getDB()->prepare($sql);
        $q->execute($data);
        return $q;
    }
    public static function fetch($sql, $data = [])
    {
        return self::execute($sql, $data)->fetch(\PDO::FETCH_OBJ);
    }
    public static function fetchAll($sql, $data = [])
    {
        return self::execute($sql, $data)->fetchAll(\PDO::FETCH_OBJ);
    }
}
