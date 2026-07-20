<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        // If no connection yet, or the connection has gone away — (re)connect.
        if (self::$pdo === null || !self::isAlive()) {
            self::$pdo = null; // reset stale singleton before reconnecting
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Database connection failed. Import database/centexs_itop_ims.sql and check config/config.php.');
            }
        }

        return self::$pdo;
    }

    /**
     * Check whether the current PDO connection is still alive.
     * Returns false if MySQL has gone away (error 2006) or any other error.
     */
    private static function isAlive(): bool
    {
        if (self::$pdo === null) {
            return false;
        }
        try {
            self::$pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

