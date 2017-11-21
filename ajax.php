<?php session_start();
require_once 'lib.php';

$action = '';
if (isset($_REQUEST['action']))
{
  $action = $_REQUEST['action'];
}
// The below is for backwards compat
else if (isset($_GET['deck'])) {
  $action = 'deckinfo';
} elseif (isset($_GET['addplayer']) && isset($_GET['event'])) {
  $action = 'addplayer';
} elseif (isset($_GET['delplayer']) && isset($_GET['event'])) {
  $action = 'delplayer';
} elseif (isset($_GET['dropplayer']) && isset($_GET['event'])) {
  $action = 'dropplayer';
}

$result = array();
switch ($action){
  case 'deckinfo':
  $deckid = $_GET['deck'];
  $deck = new Deck($deckid);
  $result["id"] = $deckid;
  if ($deck->id != 0) {
    $result["found"] = 1;
    $result["name"] = $deck->name;
    $result["archetype"] = $deck->archetype;
    $result["maindeck"] = $deck->maindeck_cards;
    $result["sideboard"] = $deck->sideboard_cards;
  } else {
    $result["found"] = 0;
  }
  break;

  case 'addplayer':
  $event = new Event($_GET['event']);
  if ($event->authCheck($_SESSION['username'])) {
    $result = array();
    $new = $_GET['addplayer'];
    if ($event->addPlayer($new)) {
      $player = new Player($new);
      $result["success"] = true;
      $result["player"] = $player->name;
      $result["verified"] = $player->verified;
      $result["event_running"] = $event->active == 1;
    } else {
      $result["success"] = false;
    }
  }
  else
  {
    $result['error'] = 'Unauthorized';
    $result["success"] = false;
  }
  break;

  case 'delplayer':
  $event = new Event($_GET['event']);
  if ($event->authCheck($_SESSION['username'])) {
    $old = $_GET['delplayer'];
    $result = array();
    $result['success'] = $event->removeEntry($old);
    $result['player'] = $old;
  }
  else
  {
    $result['error'] = 'Unauthorized';
    $result["success"] = false;
  }
  break;

  case 'dropplayer':
  $event = new Event($_GET['event']);
  if ($event->authCheck($_SESSION['username'])) {
    $result = array();
    $playername = $_GET['dropplayer'];
    $event->dropPlayer($playername);
    $result['success'] = true;
    $result['player'] = $playername;
    $result['eventname'] = $event->name;
    $result['round'] = $event->current_round;
    json_headers();
    echo json_encode($result);
  }
  else
  {
    $result['error'] = 'Unauthorized';
    $result["success"] = false;
  }
  break;

  default:
    $result['error'] = "Unknown action '{$action}'";
}

json_headers();
echo json_encode($result);