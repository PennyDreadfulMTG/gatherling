<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Views\Components\MissingTrophies;
use Gatherling\Views\Components\NoSeries;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Components\OrganizerSelect;
use Gatherling\Views\Components\PlayerBanForm;
use Gatherling\Views\Components\PointsForm;
use Gatherling\Views\Components\RecentEventsTable;
use Gatherling\Views\Components\SeasonStandings;
use Gatherling\Views\Components\SeriesAndLogoForms;
use Gatherling\Views\Components\SeriesOrganizersForm;
use Gatherling\Views\Components\TextComponent;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Pages\SeriesControlPanel;
use Gatherling\Views\Redirect;

use function Gatherling\Views\get;
use function Gatherling\Views\post;
use function Gatherling\Views\server;

require_once 'lib.php';
include 'lib_form_helper.php';

function main(): void
{
    if (!Player::isLoggedIn()) {
        (new LoginRedirect())->send();
    }

    $errorMsg = '';

    $playerSeries = Player::getSessionPlayer()?->organizersSeries() ?? [];
    if (count($playerSeries) == 0) {
        $viewComponent = new NoSeries();
        (new SeriesControlPanel(null, null, '', $viewComponent))->send();
    }
    if (isset($_POST['series'])) {
        $_GET['series'] = $_POST['series'];
    }
    if (!isset($_GET['series'])) {
        $_GET['series'] = $playerSeries[0];
    }
    $activeSeriesName = get()->string('series');

    if (isset($_POST['series'])) {
        $seriesname = post()->string('series');
        $series = new Series($seriesname);
        if ($series->authCheck(Player::loginName())) {
            if ($_POST['action'] == 'Update Series') {
                $newactive = post()->int('isactive', 0);
                $newtime = $_POST['hour'];
                $newday = $_POST['start_day'];
                $room = $_POST['mtgo_room'];

                $prereg = post()->int('preregdefault', 0);

                $series = new Series($seriesname);
                if ($series->authCheck(Player::loginName())) {
                    $series->active = $newactive;
                    $series->start_time = $newtime . ':00';
                    $series->start_day = $newday;
                    $series->prereg_default = $prereg;
                    $series->mtgo_room = $room;
                    $series->save();
                }
            } elseif ($_POST['action'] == 'Change Logo') {
                if ($_FILES['logo']['size'] > 0) {
                    $file = $_FILES['logo'];
                    $tmp = $file['tmp_name'];
                    $size = $file['size'];
                    $type = $file['type'];
                    assert(is_string($tmp) && is_string($type) && is_int($size));
                    $series->setLogo($tmp, $type, $size);
                }
            } elseif ($_POST['action'] == 'Update Organizers') {
                $errorMsg = updateOrganizers($series, post()->listString('delorganizers'), post()->optionalString('addorganizer'));
            } elseif ($_POST['action'] == 'Update Banned Players') {
                $errorMsg = updateBannedPlayers($series, post()->listString('removebannedplayer'), post()->string('addbannedplayer', ''), post()->string('reason', ''));
            } elseif ($_POST['action'] == 'Update Points Rules') {
                $season = post()->int('season');
                $new_rules = post()->dictIntOrString('new_rules');
                $series->setSeasonRules($season, $new_rules);
            }
        }
    }

    $view = post()->optionalString('view') ?? get()->optionalString('view') ?? 'settings';

    if ($view != 'no_view') {
        $orientationComponent = new NullComponent();
    } elseif (count($playerSeries) > 1) {
        $orientationComponent = new OrganizerSelect(server()->string('PHP_SELF'), $playerSeries, $activeSeriesName);
    } else {
        $orientationComponent = new TextComponent("Managing {$activeSeriesName}");
    }
    $activeSeries = new Series($activeSeriesName);

    if (!$activeSeries->authCheck(Player::loginName())) {
        $viewComponent = new NoSeries();
    } else {
        switch ($view) {
            case 'no_view':
            case 'settings':
                $viewComponent = new SeriesAndLogoForms($activeSeries);
                break;
            case 'recent_events':
                $viewComponent = new RecentEventsTable($activeSeries);
                break;
            case 'points_management':
                $season = post()->optionalInt('season') ?? get()->optionalInt('season') ?? $activeSeries->currentSeason();
                $viewComponent = new PointsForm($activeSeries, $season);
                break;
            case 'organizers':
                $viewComponent = new SeriesOrganizersForm($activeSeries);
                break;
            case 'bannedplayers':
                $viewComponent = new PlayerBanForm($activeSeries);
                break;
            case 'format_editor':
                $target = 'formatcp.php?series=' . rawurlencode($activeSeriesName);
                (new Redirect($target))->send();
                // Redirect send exits
            case 'trophies':
                $viewComponent = new MissingTrophies($activeSeries);
                break;
            case 'season_standings':
                $viewComponent = new SeasonStandings($activeSeries, $activeSeries->currentSeason());
                break;
            default:
                $viewComponent = new NullComponent();
        }
    }

    $page = new SeriesControlPanel($activeSeries, $orientationComponent, $errorMsg, $viewComponent);
    $page->send();
}

/** @param list<string> $delOrganizers */
function updateOrganizers(Series $series, array $delOrganizers, ?string $addition): string
{
    foreach ($delOrganizers as $deadorganizer) {
        $series->removeOrganizer($deadorganizer);
    }
    if (!$addition) {
        return '';
    }
    $addplayer = Player::findByName($addition);
    if ($addplayer == null) {
        return "Can't add {$addition} to Series Organizers, they don't exist!";
    }
    if ($addplayer->verified == 0 && Player::getSessionPlayer()->super == 0) {
        return "Can't add {$addplayer->name} to Series Organizers, they aren't a verified user!";
    }
    $series->addOrganizer($addplayer->name);
    return '';
}

/** @param list<string> $removeBannedPlayers */
function updateBannedPlayers(Series $series, array $removeBannedPlayers, string $addition, string $reason): string
{
    foreach ($removeBannedPlayers as $playertoremove) {
        $series->removeBannedPlayer($playertoremove);
    }
    $addplayer = Player::findByName($addition);
    if ($addplayer == null) {
        return "Can't add {$addition} to Banned Players, they don't exist!";
    }
    assert($addplayer->name !== null); // Else we would not have found them
    $series->addBannedPlayer($addplayer->name, $reason);
    return '';
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
