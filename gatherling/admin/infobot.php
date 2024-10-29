<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Views\InfobotError;
use Gatherling\Views\InfobotReply;

use function Gatherling\Helpers\config;
use function Gatherling\Helpers\get;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;

require_once __DIR__ . '/../lib.php';

function main(): void
{
    if (strncmp(server()->string('HTTP_USER_AGENT', ''), 'infobot', 7) != 0) {
        (new InfobotError("You're not infobot!"))->send();
    }

    $passKey = get()->optionalString('passkey');
    if (!$passKey || $passKey != config()->optionalString('infobot_passkey')) {
        (new InfobotError('Wrong passkey'))->send();
    }

    $siteName = config()->string('site_name');

    // generate a user passkey for verification
    $random_num = mt_rand();
    $key = sha1((string) $random_num);
    $challenge = substr($key, 0, 5);
    $player = Player::findByName(get()->optionalString('username') ?? '');
    if (!$player) {
        (new InfobotReply("You're not registered on {$siteName}!"))->send();
    }

    if (strcmp(request()->string('mode', ''), 'verify') == 0) {
        $player->setChallenge($challenge);
        (new InfobotReply("Your verification code for {$siteName} is $challenge"))->send();
    } elseif (strcmp(request()->string('mode', ''), 'reset') == 0) {
        $player->setPassword($challenge);
        (new InfobotReply("Your temporary password for {$siteName} is $challenge"))->send();
    } else {
        $mode = request()->string('mode', '');
        (new InfobotError("Unknown Action {$mode}"))->send();
    }
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
