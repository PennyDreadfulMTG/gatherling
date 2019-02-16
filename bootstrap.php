<?php

function autoload($class_name)
{
    if (file_exists('models/'.$class_name.'.php')) {
        require_once 'models/'.$class_name.'.php';
    } elseif (file_exists('../models/'.$class_name.'.php')) {
        require_once '../models/'.$class_name.'.php';
    }
}
spl_autoload_register('autoload');
// Fix for MAGIC_QUOTES_GPC

// PHP 5 hacks
if (version_compare(phpversion(), 6) === -1) {
    if (get_magic_quotes_gpc()) {
        function stripinputslashes(&$input)
        {
            if (is_array($input)) {
                foreach ($input as $key => $value) {
                    $input[$key] = stripinputslashes($value);
                }
            } else {
                $input = stripslashes($input);
            }

            return true;
        }
        array_walk_recursive($_GET, 'stripinputslashes');
        array_walk_recursive($_POST, 'stripinputslashes');
        array_walk_recursive($_COOKIE, 'stripinputslashes');
    }

    if (!function_exists('spl_autoload_register')){

        function __autoload($class_name)
        {
            return autoload($class_name);
        }
    }
}

require_once 'config.php';
