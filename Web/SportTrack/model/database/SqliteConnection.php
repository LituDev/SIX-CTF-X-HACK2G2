<?php

class SqliteConnection {
    private static SqliteConnection $instance;

    private function __construct(){ }

    public static function getInstance(): SqliteConnection {
        if (!isset(self::$instance)) {
            self::$instance = new SqliteConnection();
        }
        return self::$instance;
    }

    public function getConnection() : ?\PDO {
        try {
            $pdo = new \PDO('sqlite:'.dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'sport_track.db');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch(\PDOException $e) {
            return null;
        }
    }
}