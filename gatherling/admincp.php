<?php

declare(strict_types=1);

use Gatherling\Models\Format;
use Gatherling\Models\Player;
use Gatherling\Models\Ratings;
use Gatherling\Models\Series;
use Gatherling\Models\SetScraper;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Components\ChangePasswordForm;
use Gatherling\Views\Components\CreateNewSeriesForm;
use Gatherling\Views\Components\AddCardSetForm;
use Gatherling\Views\Components\CalcRatingsForm;
use Gatherling\Views\Components\ManualVerificationForm;
use Gatherling\Views\Pages\AdminControlPanel;
use Gatherling\Views\Redirect;

use function Gatherling\Views\get;
use function Gatherling\Views\post;
use function Gatherling\Views\server;

require_once 'lib.php';
include 'lib_form_helper.php';

function main(): void
{
    if (!(Player::getSessionPlayer()?->isSuper() ?? false)) {
        (new Redirect('index.php'))->send();
    }

    $result = '';

    $action = post()->optionalString('action');
    if ($action == 'Change Password') {
        try {
            $username = post()->string('username');
            $password = post()->string('new_password');
            $player = new Player($username);
            $player->setPassword($password);
            $result = "Password changed for user {$player->name} to {$password}";
        } catch (Exception $e) {
            $result = 'User ' . post()->string('username', 'None') . ' is not found.';
        }
    } elseif ($action == 'Verify Player') {
        $player = new Player(post()->string('username'));
        $player->setVerified(true);
        $result = "User {$player->name} is now verified.";
    } elseif ($action == 'Create Series') {
        $newactive = (int) $_POST['isactive'];
        $newtime = $_POST['hour'];
        $newday = $_POST['start_day'];
        $prereg = 0;

        if (isset($_POST['preregdefault'])) {
            $prereg = $_POST['preregdefault'];
        } else {
            $prereg = 0;
        }

        $series = new Series('');
        $newseries = $_POST['seriesname'];
        $playerName = Player::loginName();
        if ($playerName && $series->authCheck($playerName)) {
            $series->name = $newseries;
            $series->active = $newactive;
            $series->start_time = $newtime . ':00';
            $series->start_day = $newday;
            $series->prereg_default = (int) $prereg;
            $series->save();
        }
        $result = "New series $series->name was created!";
    } elseif ($action == 'Re-Calculate All Ratings') {
        $ratings = new Ratings();
        $ratings->deleteAllRatings();
        $ratings->calcAllRatings();
    } elseif ($action == 'Re-Calculate By Format') {
        $ratings = new Ratings();
        $ratings->deleteRatingByFormat(post()->string('format'));
        if ($_POST['format'] == 'Composite') {
            $ratings->calcCompositeRating();
        } else {
            $ratings->calcRatingByFormat(post()->string('format'));
        }
    } elseif ($action == 'Rebuild Tribes') {
        Format::constructTribes('All');
    }

    $view = post()->optionalString('view') ?? get()->optionalString('view') ?? 'change_password';

    $viewComponent = new NullComponent();
    if ($view == 'no_view') {
        // Show Nothing
    } elseif ($view == 'change_password') {
        $viewComponent = new ChangePasswordForm();
    } elseif ($view == 'create_series') {
        $viewComponent = new CreateNewSeriesForm();
    } elseif (($view == 'add_cardset')) {
        $viewComponent = new AddCardSetForm();
    } elseif ($view == 'calc_ratings') {
        $viewComponent = new CalcRatingsForm();
    } elseif ($view == 'verify_player') {
        $viewComponent = new ManualVerificationForm();
    }

    $page = new AdminControlPanel($result, $viewComponent);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
