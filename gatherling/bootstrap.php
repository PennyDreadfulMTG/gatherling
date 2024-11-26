<?php

declare(strict_types=1);

use Gatherling\Helpers\ErrorHandler;

use function Safe\file_get_contents;

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
    $hash_file = sprintf('../.git/%s', $branch);
    if (file_exists($hash_file)) {
    // On a branch, get the hash
        $CONFIG['GIT_HASH'] = file_get_contents($hash_file);
    } else {
        // On a detached HEAD, just use the branch name
        $CONFIG['GIT_HASH'] = $branch;
    }
}

set_exception_handler(fn (\Throwable $e) => (new ErrorHandler())->handle($e));

Sentry\init([
    'dsn'         => 'https://ed7243cbdd9e47c8bc2205d4ac36b764@sentry.redpoint.games/16',
    'environment' => 'Gatherling',
    'release'     => $CONFIG['GIT_HASH'],
]);
