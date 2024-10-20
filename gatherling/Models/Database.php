<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use mysqli;
use PDO;

// Use PHP7 default error reporting to avoid a complex refactor
mysqli_report(MYSQLI_REPORT_OFF);

define('SLOW_QUERY_MS', 1000);

class Database
{
    public static function getConnection(): mysqli
    {
        static $instance;

        if (!isset($instance)) {
            global $CONFIG;
            $instance = new mysqli(
                $CONFIG['db_hostname'],
                $CONFIG['db_username'],
                $CONFIG['db_password']
            );
            if (mysqli_connect_errno()) {
                throw new Exception((string) mysqli_connect_error());
            }
            $db_selected = $instance->select_db($CONFIG['db_database']);
            if (!$db_selected) {
                throw new \Exception('Error creating database: ' . mysqli_error($instance) . "\n");
            }
            $sql = "SET time_zone = 'America/New_York'"; // Ensure EST
            $instance->query($sql);
        }

        return $instance;
    }

    public static function getPDOConnection(): PDO
    {
        static $pdo_instance;

        if (!isset($pdo_instance)) {
            global $CONFIG;
            $pdo_instance = new PDO(
                'mysql:hostname=' . $CONFIG['db_hostname'] . ';port=3306;dbname=' . $CONFIG['db_database'],
                $CONFIG['db_username'],
                $CONFIG['db_password']
            );
        }

        return $pdo_instance;
    }

    // Does PHP have an arguments[] property that would allow processing of any number of parameters?
    // could I just make $paramType and $param arrays that would allow a single function to handle any number
    // of parameters? Going to have to play with this.
    public static function singleResultSingleParam(string $sql, string $paramType, mixed $param): mixed
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception($db->error, 1);
        }
        $stmt->bind_param($paramType, $param);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    /**
     * @return list<mixed>
     */
    public static function listResultSingleParam(string $sql, string $paramType, mixed $param): array
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception($db->error, 1);
        }
        $stmt->bind_param($paramType, $param);
        $stmt->execute();
        $stmt->bind_result($result);

        $list = [];
        while ($stmt->fetch()) {
            $list[] = $result;
        }
        $stmt->close();

        return $list;
    }
}
