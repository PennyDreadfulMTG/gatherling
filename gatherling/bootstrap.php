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
        if (file_exists('models/' . $class_name . '.php')) {
            require_once 'models/' . $class_name . '.php';
        } elseif (file_exists('../models/' . $class_name . '.php')) {
            require_once '../models/' . $class_name . '.php';
        } elseif (file_exists('gatherling/models/' . $class_name . '.php')) {
            require_once 'gatherling/models/' . $class_name . '.php';
        }
    }
}

spl_autoload_register('autoload');
// Fix for MAGIC_QUOTES_GPC

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    include_once __DIR__ . '/vendor/autoload.php';
} else {
    include_once __DIR__ . '/../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/config.php')) {
    require_once 'config.php';
} else {
    $CONFIG = $_ENV;
}

$CONFIG['GIT_HASH'] = null;
if (file_exists('../.git/HEAD')) {
    $branch = trim(substr(file_get_contents('../.git/HEAD'), 5));
    if ($hash = file_get_contents(sprintf('../.git/%s', $branch))) {
        $CONFIG['GIT_HASH'] = $hash;
    }
}

Sentry\init([
    'dsn'         => 'https://ed7243cbdd9e47c8bc2205d4ac36b764@sentry.redpoint.games/16',
    'environment' => $CONFIG['site_name'],
    'release'     => $CONFIG['GIT_HASH'],
]);
