<?php

class Database
{
    public static function getConnection()
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
                echo mysqli_connect_error();
                exit("\nfailed to connect to mysql server");
            }
            $db_selected = $instance->select_db($CONFIG['db_database']);
            if (!$db_selected) {
                // If we couldn't, then it either doesn't exist, or we can't see it.
                $sql = "CREATE DATABASE {$CONFIG['db_database']}";

                self::single_result($sql);
                $db_selected = $instance->select_db($CONFIG['db_database']);
                if (!$db_selected) {
                    exit('Error creating database: '.mysqli_error()."\n");
                }
            }

            $sql = "SET time_zone = 'America/New_York'"; // Ensure EST
            $instance->query($sql);
        }

        return $instance;
    }

    public static function getPDOConnection()
    {
        static $pdo_instance;

        if (!isset($pdo_instance)) {
            global $CONFIG;
            $pdo_instance = new PDO(
                'mysql:hostname='.$CONFIG['db_hostname'].';dbname='.$CONFIG['db_database'],
                $CONFIG['db_username'],
                $CONFIG['db_password']
            );
        }

        return $pdo_instance;
    }

    public static function single_result($sql)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    // Does PHP have an arguments[] property that would allow processing of any number of parameters?
    // could I just make $paramType and $param arrays that would allow a single function to handle any number
    // of parameters? Going to have to play with this.
    public static function single_result_single_param($sql, $paramType, $param)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt || exit($db->error);
        $stmt->bind_param($paramType, $param);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public static function single_result_double_param($sql, $paramTypes, $param1, $param2)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bind_param($paramTypes, $param1, $param2);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public static function list_result($sql)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($result);

        $list = [];
        while ($stmt->fetch()) {
            $list[] = $result;
        }
        $stmt->close();

        return $list;
    }

    public static function list_result_single_param($sql, $paramType, $param)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
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

    public static function list_result_double_param($sql, $paramTypes, $param1, $param2)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bind_param($paramTypes, $param1, $param2);
        $stmt->execute();
        $stmt->bind_result($result);

        $list = [];
        while ($stmt->fetch()) {
            $list[] = $result;
        }
        $stmt->close();

        return $list;
    }

    public static function no_result_single_param($sql, $paramType, $param)
    {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bind_param($paramType, $param);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function db_query()
    {
        $params = func_get_args();
        $query = array_shift($params);
        $paramspec = array_shift($params);

        $db = self::getConnection();
        $stmt = $db->prepare($query);
        $stmt or exit($db->error);

        if (count($params) == 1) {
            list($one) = $params;
            $stmt->bind_param($paramspec, $one);
        } elseif (count($params) == 2) {
            list($one, $two) = $params;
            $stmt->bind_param($paramspec, $one, $two);
        } elseif (count($params) == 3) {
            list($one, $two, $three) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three);
        } elseif (count($params) == 4) {
            list($one, $two, $three, $four) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four);
        } elseif (count($params) == 5) {
            list($one, $two, $three, $four, $five) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five);
        } elseif (count($params) == 6) {
            list($one, $two, $three, $four, $five, $six) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six);
        } elseif (count($params) == 7) {
            list($one, $two, $three, $four, $five, $six, $seven) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven);
        } elseif (count($params) == 8) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight);
        } elseif (count($params) == 9) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
        } elseif (count($params) == 10) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten);
        }
        $stmt->execute() or exit($stmt->error);
        $stmt->close();

        return true;
    }

    public static function db_query_single()
    {
        $params = func_get_args();
        $query = array_shift($params);
        $paramspec = array_shift($params);

        $db = self::getConnection();
        $stmt = $db->prepare($query);
        $stmt or exit($db->error);

        if (count($params) == 1) {
            list($one) = $params;
            $stmt->bind_param($paramspec, $one);
        } elseif (count($params) == 2) {
            list($one, $two) = $params;
            $stmt->bind_param($paramspec, $one, $two);
        } elseif (count($params) == 3) {
            list($one, $two, $three) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three);
        } elseif (count($params) == 4) {
            list($one, $two, $three, $four) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four);
        } elseif (count($params) == 5) {
            list($one, $two, $three, $four, $five) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five);
        } elseif (count($params) == 6) {
            list($one, $two, $three, $four, $five, $six) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six);
        } elseif (count($params) == 7) {
            list($one, $two, $three, $four, $five, $six, $seven) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven);
        } elseif (count($params) == 8) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight);
        } elseif (count($params) == 9) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
        } elseif (count($params) == 10) {
            list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten) = $params;
            $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten);
        }
        $stmt->execute() or exit($stmt->error);
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public static function get_lock($name = 'lock_db', $timeout = 0)
    {
        $sql = "SELECT GET_LOCK('{$name}',{$timeout})";

        return self::single_result($sql);
    }

    public static function release_lock($name = 'lock_db')
    {
        $sql = "SELECT RELEASE_LOCK('{$name}')";

        return self::single_result($sql);
    }
}
