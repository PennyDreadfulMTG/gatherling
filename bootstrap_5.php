<?php

// Have some PHP 5 specific code
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

if (!function_exists('spl_autoload_register')) {
    function __autoload($class_name)
    {
        return autoload($class_name);
    }
}
