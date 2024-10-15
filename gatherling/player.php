<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Views\Components\AllDecks;
use Gatherling\Views\Components\AllRatings;
use Gatherling\Views\Components\ChangePassForm;
use Gatherling\Views\Components\EditAccountsForm;
use Gatherling\Views\Components\EditEmailForm;
use Gatherling\Views\Components\EditTimeZoneForm;
use Gatherling\Views\Components\EventStandings;
use Gatherling\Views\Components\MainPlayerControlPanel;
use Gatherling\Views\Components\ManualVerifyMtgoForm;
use Gatherling\Views\Components\VerifyMtgoForm;
use Gatherling\Views\Components\PlayerMatches;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Pages\PlayerControlPanel;

use function Gatherling\Views\config;
use function Gatherling\Views\get;
use function Gatherling\Views\post;
use function Gatherling\Views\request;
use function Gatherling\Views\server;

require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $player = Player::getSessionPlayer();
    if ($player == null) {
        (new LoginRedirect())->send();
    }

    $result = '';
    $action = post()->optionalString('action');

    // Handle actions
    if ($action == 'changePassword') {
        $result = changePassword($player, post()->string('oldPasssword', ''), post()->string('newPassword'), post()->string('newPassword2'));
    } elseif ($action == 'editEmail') {
        $result = editEmail($player, post()->string('newEmail'), post()->string('newEmail2'), post()->int('emailStatus'));
    } elseif ($action == 'editAccounts') {
        $result = editAccounts($player, post()->optionalString('mtgo_username'), post()->string('mtga_username', ''));
    } elseif ($action == 'changeTimeZone') {
        $result = changeTimeZone($player, post()->float('timezone'));
    } elseif ($action == 'verifyAccount') {
        $result = verifyAccount($player, post()->string('challenge'));
    }

    $dispmode = request()->string('mode', 'playercp');
    switch ($dispmode) {
        case 'submit_result':
        case 'submit_league_result':
        case 'verify_result':
        case 'verify_league_result':
        case 'drop_form':
            throw new InvalidArgumentException('Invalid mode: ' . $dispmode);

        case 'alldecks':
            $viewComponent = new AllDecks($player);
            break;

        case 'allratings':
            $formatName = post()->string('format', 'Composite');
            $viewComponent = new AllRatings($player, $formatName);
            break;

        case 'allmatches':
        case 'Filter Matches':
            $selectedFormat = post()->string('format', '%');
            $selectedSeries = post()->string('series', '%');
            $selectedSeason = post()->string('season', '%');
            $selectedOpponent = post()->string('opp', '%');
            $viewComponent = new PlayerMatches($player, $selectedFormat, $selectedSeries, $selectedSeason, $selectedOpponent);
            break;

        case 'changepass':
            $viewComponent = new ChangePassForm(request()->optionalString('tooshort') === 'true');
            break;

        case 'edit_email':
            $viewComponent = new EditEmailForm($player);
            break;

        case 'edit_accounts':
            $viewComponent = new EditAccountsForm($player);
            break;

        case 'change_timezone':
            $viewComponent = new EditTimeZoneForm($player);
            break;

        case 'standings':
            $viewComponent = new EventStandings(get()->string('event'), Player::loginName() ?: null);
            break;

        case 'verifymtgo':
            $infobotPasskey = config()->string('infobot_passkey', '');
            if ($infobotPasskey == '') {
                $viewComponent = new ManualVerifyMtgoForm($player);
            } else {
                $viewComponent = new VerifyMtgoForm($player, config()->string('infobot_prefix', ''));
            }
            break;

        default:
            $viewComponent = new MainPlayerControlPanel($player);
    }

    $page = new PlayerControlPanel($result, $viewComponent);
    $page->send();
}

function changePassword(Player $player, string $oldPassword, string $newPassword, string $newPassword2): string
{
    if ($newPassword2 != $newPassword) {
        return 'Password *not* changed, your new passwords did not match!';
    }
    if (strlen($newPassword) < 8) {
        return 'Passsword *not* changed, your new password needs to be longer!';
    }
    $authenticated = Player::checkPassword($player->name, $oldPassword);
    if (!$authenticated) {
        return 'Password *not* changed, your old password was incorrect!';
    }
    $player->setPassword($newPassword);
    return 'Password changed.';
}

function editEmail(Player $player, string $newEmail, string $newEmail2, int $emailStatus): string
{
    if ($newEmail != $newEmail2) {
        return 'Email *NOT* Changed, your new emails did not match!';
    }
    $player->emailAddress = $newEmail;
    $player->emailPrivacy = $emailStatus;
    $player->save();
    return 'Email changed.';
}

function editAccounts(Player $player, ?string $mtgoUsername, string $mtgaUsername): string
{
    $player->mtgo_username = $mtgoUsername;
    if (!preg_match('/^.{3,24}#\d{5}$/', post()->string('mtga_username', ''))) {
        $mtgaUsername = null;
    }
    $player->mtga_username = $mtgaUsername;
    $player->save();
    return 'Accounts updated.';
}

function changeTimeZone(Player $player, float $timezone): string
{
    $player->timezone = $timezone;
    $player->save();
    return 'Time Zone Changed.';
}

function verifyAccount(Player $player, string $challenge): string
{
    if ($player->checkChallenge($challenge)) {
        $player->setVerified(true);
        return 'Successfully verified your account with MTGO.';
    }
    $infobotPrefix = config()->string('infobot_prefix', '');
    return "Your challenge is wrong.  Get a new one by sending the message '<code>!verify {$infobotPrefix}</code>' to pdbot on MTGO!";
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
