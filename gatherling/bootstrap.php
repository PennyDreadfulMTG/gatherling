<?php

function autoload($class_name)
{
    if (file_exists('models/'.$class_name.'.php')) {
        require_once 'models/'.$class_name.'.php';
    } elseif (file_exists('../models/'.$class_name.'.php')) {
        require_once '../models/'.$class_name.'.php';
    } elseif (file_exists('gatherling/models/'.$class_name.'.php')) {
        require_once 'gatherling/models/'.$class_name.'.php';
    }
}
spl_autoload_register('autoload');
// Fix for MAGIC_QUOTES_GPC

include_once __DIR__.'/vendor/autoload.php';

// PHP 5 hacks
if (version_compare(phpversion(), 6) === -1) {
    require_once 'bootstrap_5.php';
}

require_once 'config.php';
