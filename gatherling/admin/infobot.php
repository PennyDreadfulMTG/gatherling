<?php

declare(strict_types=1);

use Gatherling\Models\Player;

use function Gatherling\Views\config;
use function Gatherling\Views\get;
use function Gatherling\Views\request;
use function Gatherling\Views\server;

require_once __DIR__ . '/../lib.php';

if (strncmp(server()->string('HTTP_USER_AGENT', ''), 'infobot', 7) != 0) {
    exit("<error>You're not infobot!</error>");
}

$passKey = get()->optionalString('passkey');
if (!$passKey || $passKey != config()->optionalString('infobot_passkey')) {
    exit('<error>Wrong passkey</error>');
}

$siteName = config()->string('site_name');

// generate a user passkey for verification
$random_num = mt_rand();
$key = sha1((string) $random_num);
$challenge = substr($key, 0, 5);
$player = Player::findByName(get()->optionalString('username') ?? '');
if (!$player) {
    echo "<UaReply>You're not registered on {$siteName}!</UaReply>";
    return;
}

if (strcmp(request()->string('mode', ''), 'verify') == 0) {
    $player->setChallenge($challenge);
    echo "<UaReply>Your verification code for {$siteName} is $challenge</UaReply>";
} elseif (strcmp(request()->string('mode', ''), 'reset') == 0) {
    $player->setPassword($challenge);
    echo "<UaReply>Your temporary password for {$siteName} is $challenge</UaReply>";
} else {
    $mode = request()->string('mode', '');
    echo "<error>Unknown Action {$mode}</error>";
}
