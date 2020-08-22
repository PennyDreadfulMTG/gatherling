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

if (file_exists(__DIR__.'/vendor/autoload.php'))
    include_once __DIR__.'/vendor/autoload.php';
else
    include_once __DIR__.'/../vendor/autoload.php';

// PHP 5 hacks
if (version_compare(phpversion(), 6) === -1) {
    require_once 'bootstrap_5.php';
}

require_once 'config.php';

Sentry\init(['dsn' => 'https://fdfade4631f84653a606228c18e6922a@o233010.ingest.sentry.io/1414048']);
