<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Views\Pages\Profile;

require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $playerName = $_POST['player'] ?? $_GET['player'] ?? $_SESSION['username'] ?? '';
    $profileEdit = (int) ($_REQUEST['profile_edit'] ?? 0);

    $player = Player::findByName($playerName);
    if ($player && $profileEdit == 2) {
        $player->emailAddress = $_GET['email'];
        $player->emailPrivacy = $_GET['email_public'];
        $player->timezone = $_GET['timezone'];
        $player->save();
    }

    $page = new Profile($playerName, $player, $profileEdit);
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
