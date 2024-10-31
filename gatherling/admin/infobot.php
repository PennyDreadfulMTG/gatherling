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

function main(): never
{
    if (strncmp(server()->string('HTTP_USER_AGENT', ''), 'infobot', 7) != 0) {
        (new InfobotError("You're not infobot!"))->send();
    }

    $passKey = get()->optionalString('passkey');
    if (!$passKey || $passKey != config()->optionalString('infobot_passkey')) {
        (new InfobotError('Wrong passkey'))->send();
    }

    // generate a user passkey for verification
    $random_num = mt_rand();
    $key = sha1((string) $random_num);
    $challenge = substr($key, 0, 5);
    $player = Player::findByName(get()->optionalString('username') ?? '');
    if (!$player) {
        (new InfobotReply("You're not registered on Gatherling!"))->send();
    }

    $mode = request()->string('mode', '');
    if ($mode === 'verify') {
        $player->setChallenge($challenge);
        (new InfobotReply("Your verification code for Gatherling is $challenge"))->send();
    } elseif ($mode === 'reset') {
        $player->setPassword($challenge);
        (new InfobotReply("Your temporary password for Gatherling is $challenge"))->send();
    } else {
        (new InfobotError("Unknown Action {$mode}"))->send();
    }
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
