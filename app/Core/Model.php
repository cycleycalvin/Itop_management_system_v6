<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Static helper so views/partials can run lightweight queries */
    public static function getDb(): PDO
    {
        return Database::connection();
    }
}

