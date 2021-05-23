<?php

/**
 * @param string $class_name
 *
 * @return void
 */
function autoload($class_name)
{
    $names = explode('\\', $class_name);
    if ($names[0] == 'Gatherling') {
        $class_name = $names[1];
        if (file_exists('models/'.$class_name.'.php')) {
            require_once 'models/'.$class_name.'.php';
        } elseif (file_exists('../models/'.$class_name.'.php')) {
            require_once '../models/'.$class_name.'.php';
        } elseif (file_exists('gatherling/models/'.$class_name.'.php')) {
            require_once 'gatherling/models/'.$class_name.'.php';
        }
    }
}

spl_autoload_register('autoload');
// Fix for MAGIC_QUOTES_GPC

if (file_exists(__DIR__.'/vendor/autoload.php')) {
    include_once __DIR__.'/vendor/autoload.php';
} else {
    include_once __DIR__.'/../vendor/autoload.php';
}

// PHP 5 hacks
if (version_compare(phpversion(), 6) === -1) {
    require_once 'bootstrap_5.php';
}

require_once 'config.php';

Sentry\init([
    'dsn'         => 'https://f8ec94b8d8b24b71b111fe96b0cc22b5@o531055.ingest.sentry.io/5657303',
    'environment' => $CONFIG['site_name'],
]);
