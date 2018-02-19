<?php
/// Information banner informing user that they have pending actions.
/// Appears at top of each page?
require_once 'lib.php';
session_start();
$player = Player::getSessionPlayer();
if (!is_null($player))
{
  $message = null;

  foreach ($player->organizersSeries() as $player_series)
  {
    $series = new Series($player_series);
    if ($series->active) {
      if (is_null($series->nextEvent())) {
        $message = "Your series <a href=\"seriescp.php?series=$player_series\">$player_series</a> doesn't have an upcoming event.<br/>";
        $createLink = "event.php?mode=Create Next Event&name=" . $series->mostRecentEvent()->name;
        $message = $message . "<a href=\"$createLink\">Create one</a> or set the series as inactive.";
      }
    }
  }

  $matches = $player->getCurrentMatches();
  foreach ($matches as $match)
  {
    $event = new Event($match->getEventNamebyMatchid());
    if ($event->isLeague())
    {
      // Do nothing
    }
    elseif ($match->result != "BYE" && $match->verification == "unverified")
    {
      $opp = $match->playera;
      $player_number="b";
      if (strcasecmp($player->name, $opp) == 0)
      {
        $opp = $match->playerb;
        $player_number="a";
      }
      $oppplayer = new Player($opp);

      if ($player_number=="b" AND ($match->playerb_wins + $match->playerb_losses) > 0)
      {
        // Report Submitted
      }
      else if ($player_number=="a" AND ($match->playera_wins + $match->playera_losses) > 0)
      {
        // Report Submitted
      }
      else
      {
        if ($match->player_reportable_check() == True)
        {
          $message = "You have an unreported match in $event->name vs. " . $oppplayer->linkTo() . ".";
          $message = $message . "<a href=\"player.php?mode=submit_result&match_id=".$match->id."&player=".$player_number ."\">(Report Result)</a>";
        }
        else
        {
          $message = "You have an unreported match in $match->eventname.";
        }
      }
    }
  }

  if (!is_null($message)) {
    echo "<div class=\"banner_alert\">";
    echo $message;
    echo "</div>";
  }
}
