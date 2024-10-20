<?php

declare(strict_types=1);

if (file_exists('/var/www/vendor/autoload.php')) {
    // Docker environment
    /** @phpstan-ignore-next-line */
    require_once '/var/www/vendor/autoload.php';
} else {
    require_once __DIR__ . '/../vendor/autoload.php';
}

global $CONFIG;
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
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
