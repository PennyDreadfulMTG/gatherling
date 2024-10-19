<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\Components\DropConfirm;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Components\SubmitLeagueResultForm;
use Gatherling\Views\Components\SubmitResultForm;
use Gatherling\Views\Components\VerifyResultForm;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Pages\Report;
use Gatherling\Views\Redirect;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;

require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $player = Player::getSessionPlayer();
    if (!$player) {
        (new LoginRedirect())->send();
    }

    $action = post()->optionalString('action');
    if ($action == 'verify_result' && !isset($_POST['drop'])) {
        $_POST['action'] = 'finalize_result';
        $_REQUEST['action'] = 'finalize_result';
        if (!isset($_POST['opponent'])) {
            $_POST['opponent'] = '0';
        }
    }

    $result = '';
    $action = post()->optionalString('action');
    if ($action == 'finalize_result') {
        // write results to matches table
        $drop = post()->optionalString('drop') == 'Y';
        if ($drop) {
            $match = new Matchup(post()->int('match_id'));
            $eventname = $match->getEventNamebyMatchid();
            $event = new Event($eventname);
            $event->dropPlayer($player->name);
        }
        if ($_POST['opponent'] != '0') {
            $event = new Event(post()->string('event'));
            if ($event->isLeague()) {
                $player = new Standings($event->name, post()->string('player'));
                $opponent = new Standings($event->name, post()->string('opponent'));
                $new_match_id = $event->addPairing($player, $opponent, $event->current_round, 'P');
                Matchup::saveReport(post()->string('report'), $new_match_id, 'a');
                (new Redirect('player.php'))->send();
            } else {
                $result = 'This is not a league event!';
            }
        } else {
            // Non-league matches
            $match = new Matchup(post()->int('match_id'));
            if ($match->playerLetter($player->name ?? '') == post()->string('player')) {
                Matchup::saveReport(post()->string('report'), post()->int('match_id'), post()->string('player'));
                (new Redirect('player.php'))->send();
            } else {
                $result = 'Results appear to be tampered.  Please only submit your own results.';
            }
        }
    } elseif ($action == 'drop') {
        // drop player from event
        $event = new Event(post()->string('event'));
        $event->dropPlayer($player->name);
        (new Redirect('player.php'))->send();
    }

    $viewComponent = new NullComponent();

    $dispMode = request()->optionalString('mode');

    switch ($dispMode) {
        case 'submit_result':
            if (!isset($_GET['match_id'])) {
                (new Redirect('player.php'))->send();
            }
            $viewComponent = new SubmitResultForm(get()->int('match_id'));
            break;

        case 'submit_league_result':
            $viewComponent = new SubmitLeagueResultForm(request()->string('event'), request()->int('round'), $player, request()->int('subevent'));
            break;

        case 'verify_result':
        case 'verify_league_result':
            if (isset($_POST['report'])) {
                $drop = (isset($_POST['drop'])) ? 'Y' : 'N';
                $opponent = request()->string('opponent', '0');
                $eventName = request()->string('event', '0');
                $viewComponent = new VerifyResultForm(post()->string('report'), post()->int('match_id'), post()->string('player'), $drop, $opponent, $eventName);
            } else {
                $viewComponent = new SubmitResultForm(request()->int('match_id'));
            }
            break;

        case 'drop_form':
            $match = null;
            $matches = $player->getCurrentMatches();
            $eventName = request()->string('event', '');
            $canDrop = true;
            foreach ($matches as $match) {
                if (strcasecmp($eventName, $match->getEventNamebyMatchid()) != 0) {
                    continue;
                }
                if ($match->verification == 'unverified') {
                    $playerLetter = $match->playerLetter($player->name ?? '');
                    if ($playerLetter == 'b' and ($match->playerb_wins + $match->playerb_losses) > 0) {
                        // Fine.
                    } elseif ($playerLetter == 'a' and ($match->playera_wins + $match->playera_losses) > 0) {
                        // Also Fine
                    } else {
                        if ($match->playerReportableCheck() == true) {
                            $canDrop = false;
                        }
                    }
                } elseif ($match->verification == 'failed') {
                    $canDrop = false;
                }
            }

            if ($canDrop) {
                $viewComponent = new DropConfirm($eventName, $player->name ?? '');
            } elseif ($match) {
                $viewComponent = new SubmitResultForm($match->id, true);
            }
            break;
    }
    $page = new Report($result, $viewComponent);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
