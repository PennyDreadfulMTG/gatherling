<?php

declare(strict_types=1);

/// Information banner informing user that they have pending actions.
/// Appears at top of each page

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;

require_once 'lib.php';
$player = Player::getSessionPlayer();
if (!is_null($player)) {
    $message = null;
    if ($player->emailAddress == '') {
        $message = '<a href="player.php?mode=edit_email">Add an Email Address</a> to your account.';
    }
    if (isset($_SESSION['DISCORD_ID']) && empty($player->discord_id)) {
        $message = "<a href=\"auth.php\">Link your account to <i class=\"fab fa-discord\"></i> {$_SESSION['DISCORD_NAME']}</a>";
    } elseif (empty($player->discord_id)) {
        $message = '<a href="auth.php">Link your account to <i class="fab fa-discord"></i> Discord</a>';
    }
    foreach ($player->organizersSeries() as $player_series) {
        $series = new Series($player_series);
        if ($series->active) {
            if (is_null($series->nextEvent())) {
                $message = "Your series <a href=\"seriescp.php?series=$player_series\">$player_series</a> doesn't have an upcoming event.<br/>";
                $mostRecentEvent = $series->mostRecentEvent();
                $nameMostRecent = $mostRecentEvent ? $mostRecentEvent->name : null;
                if (is_null($nameMostRecent) || $nameMostRecent == '') {
                    $createLink = 'event.php?mode=Create Next Event&name=' . $series->name . ' 1.00';
                } else {
                    $createLink = 'event.php?mode=Create Next Event&name=' . $nameMostRecent;
                }
                $message = $message . "<a href=\"$createLink\">Create one</a> or set the series as inactive.";
            }
        }
        $recent = $series->mostRecentEvent();
        if ($recent && !$recent->finalized && !$recent->active && !empty($recent->name)) {
            $message = "Your event <a href=\"event.php?event={$recent->id}\">{$recent->name}</a> is ready to start. <br />";
            $reg = count($recent->getPlayers());
            $valid = count($recent->getRegisteredPlayers());
            $message .= "It has $reg entries, of whom $valid have valid decklists.";
        }
    }

    $active_events = Event::getActiveEvents();
    foreach ($active_events as $event) {
        if ($event->authCheck($player->name) && !$event->private && !$event->isLeague()) {
            $message = "Your event <a href=\"event.php?event={$event->id}\">{$event->name}</a> is currently active.";
            // if ($event->current_round > ($event->mainrounds)) {
            //     $subevent_id = $event->finalid;
            // } else {
            //     $subevent_id = $event->mainid;
            // }
            // $matches_remaining = Matchup::unresolvedMatchesCheck($subevent_id, $event->current_round);
            // $message = $message . "There are $matches_remaining unreported matches.";
        }
    }

    $matches = $player->getCurrentMatches();
    foreach ($matches as $match) {
        $event = new Event($match->getEventNamebyMatchid());
        if ($event->client == 1 && empty($player->mtgo_username)) {
            $message = '<a href="player.php?mode=edit_accounts">Set your MTGO username so that your opponent can find you.</a>.';
        } elseif ($event->client == 2 && empty($player->mtga_username)) {
            $message = '<a href="player.php?mode=edit_accounts">Set your MTGA username so that your opponent can Direct Challenge you.</a>.';
        } elseif ($event->isLeague()) {
            // Do nothing
        } elseif ($match->result != 'BYE' && $match->verification == 'unverified') {
            $opp = $match->playera;
            $player_number = 'b';
            if (strcasecmp($player->name, $opp) == 0) {
                $opp = $match->playerb;
                $player_number = 'a';
            }
            $oppplayer = new Player($opp);

            if ($player_number == 'b' and ($match->playerb_wins + $match->playerb_losses) > 0) {
                // Report Submitted
            } elseif ($player_number == 'a' and ($match->playera_wins + $match->playera_losses) > 0) {
                // Report Submitted
            } else {
                $message = "You have an unreported match in $event->name vs. ";
                if ($event->decklistsVisible()) {
                    $opp_entry = Entry::findByEventAndPlayer($event->id, $oppplayer->name);
                    $message = $message . $oppplayer->linkTo($event->client) . ' (' . $opp_entry->deck->linkTo() . ').';
                } else {
                    $message = $message . $oppplayer->linkTo($event->client) . '.';
                }
                if ($match->playerReportableCheck() == true) {
                    $message = $message . '  <a href="report.php?mode=submit_result&match_id=' . $match->id . '&player=' . $player_number . '">(Report Result)</a>';
                }
            }
        } elseif ($match->result != 'BYE' && $match->verification == 'failed') {
            $opp = $match->playera;
            $player_number = 'b';
            if (strcasecmp($player->name, $opp) == 0) {
                $opp = $match->playerb;
                $player_number = 'a';
            }
            $oppplayer = new Player($opp);

            if ($match->playerReportableCheck() == true) {
                $message = "The reported result wasn't consistent with your opponent's, please resubmit $event->name vs. " . $oppplayer->linkTo() . '.';
                $message = $message . '<a href="report.php?mode=submit_result&match_id=' . $match->id . '&player=' . $player_number . '">(Report Result)</a>';
            } else {
                $message = "You have an unreported match in $match->eventname.";
            }
        }
    }

    if (!is_null($message)) {
        echo '<div class="banner_alert">';
        echo $message;
        echo '</div>';
    }
}
