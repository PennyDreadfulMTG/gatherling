<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Views\Pages\Profile;

use function Gatherling\Views\get;
use function Gatherling\Views\post;
use function Gatherling\Views\request;
use function Gatherling\Views\server;
use function Gatherling\Views\session;

require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $playerName = post()->optionalString('player') ?? get()->optionalString('player') ?? session()->optionalString('username') ?? '';
    $profileEdit = request()->int('profile_edit', 0);

    $player = Player::findByName($playerName);
    if ($player && $profileEdit == 2) {
        $player->emailAddress = $_GET['email'];
        $player->emailPrivacy = get()->int('email_public');
        $player->timezone = (float) $_GET['timezone'];
        $player->save();
    }

    $page = new Profile($playerName, $player, $profileEdit);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
