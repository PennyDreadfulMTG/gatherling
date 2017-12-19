<?php

if (file_exists('../lib.php')) {
  require_once '../lib.php';
}
else {
  require_once 'lib.php';
}

if (PHP_SAPI != "cli"){
  session_start();
  ini_set('max_execution_time', 300);
  if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
    redirect("index.php");
  }
}

set_time_limit(0);
updateStandard();
set_time_limit(0);
updateModern();
set_time_limit(0);
updatePennyDreadful("Penny Dreadful", "http://pdmtgo.com/legal_cards.txt");

function info($text, $newline = true){
  if ($newline) {
    if (PHP_SAPI == "cli"){
      echo "\n";
    }
    else {
      echo "<br/>";
    }
  }
  echo $text;
}

function addSet($set) {
  if (PHP_SAPI == "cli") {
    info("Please add {$set} to the database");
  }
  else
  {
    redirect("util/insertcardset.php?cardsetcode={$set}&return=util/updateDefaultFormats.php");
  }
}

function LoadFormat($format){
  if (!Format::doesFormatExist($format))
  {
    $active_format = new Format("");
    $active_format->name = $format;
    $active_format->type = "System";
    $active_format->series_name = "System";
    $active_format->min_main_cards_allowed = 60;
    $success = $active_format->save();
  }
  return new Format($format);
}

function updateStandard(){
  info("Updating Standard...");
  $fmt = LoadFormat("Standard");
  $legal = json_decode(file_get_contents("http://whatsinstandard.com/api/v5/sets.json"));
  if (!$legal)
  {
    info("Unable to load WhatsInStandard API.  Aborting.");
    return;
  }
  $expected = array();
  foreach ($legal->sets as $set){
    $enter = strtotime($set->enter_date);
    $exit = strtotime($set->exit_date);
    $now = time();
    if ($exit == NULL)
      $exit = $now + 1;
    if ($exit < $now)
    {
      // Set has rotated out.
    }
    else if ($enter > $now)
    {
      // Set is yet to be released. (And probably not available in MTGJSON yet)
    }
    else
    {
      // The ones we care about.
      $db = Database::getConnection();
      $stmt = $db->prepare("SELECT name, type FROM cardsets WHERE code = ?");
      $stmt->bind_param("s", $set->code);
      $stmt->execute();
      $stmt->bind_result($setName, $setType);
      $success = $stmt->fetch();
      $stmt->close();
      if (!$success){
        addSet($set->code, 0);
        return;
      }
      $expected[] = $setName;
    }
  }
  foreach ($fmt->getLegalCardsets() as $setName) {
    if (!in_array($setName, $expected, true)){
      $fmt->deleteLegalCardSet($setName);
      info("{$setName} is no longer Standard Legal.");
    }
  }

  foreach ($expected as $setName){
    if (!$fmt->isCardSetLegal($setName)) {
      $fmt->insertNewLegalSet($setName);
      info("{$setName} is now Standard Legal.");
    }
  }
}

function updateModern(){
  info("Updating Modern...");
  $fmt = LoadFormat("Modern");

  $legal = $fmt->getLegalCardsets();

  $db = Database::getConnection();
  $stmt = $db->prepare("SELECT name, type, released FROM cardsets WHERE `type` != 'extra' ORDER BY `cardsets`.`released` ASC");
  $stmt->execute();
  $stmt->bind_result($setName, $setType, $setDate);

  $sets = array();
  while ($stmt->fetch()) {
    $sets[] = array($setName, $setType, $setDate);
  }
  $stmt->close();

  $cutoff = strtotime("2003-07-27");
  foreach ($sets as $set)
  {
    $setName = $set[0];
    $release = strtotime($set[2]);
    if ($release > $cutoff)
    {
      if (!$fmt->isCardSetLegal($setName)) {
        $fmt->insertNewLegalSet($setName);
        info("{$setName} is Modern Legal.");
      }
    }
  }
}

function updatePennyDreadful($name, $url)
{
  info("Updating $name...");
  $fmt = LoadFormat($name);

  $legal_cards = parseCards(file_get_contents($url));
  if (!$legal_cards){
    info("Unable to fetch legal_cards.txt");
    return;
  }
  $i = 0;
  foreach ($fmt->card_legallist as $card) {
    if (!in_array($card, $legal_cards, true)) {
      $fmt->deleteCardFromLegallist($card);
      info("{$card} is no longer $name Legal.");
    }

    if ($i++ == 200) {
      set_time_limit(0);
      $i = 0;
      info('.', false);
    }
  }
  info(' ', false);
  foreach ($legal_cards as $card) {
    if (!in_array($card, $fmt->card_legallist)) {
      $success = $fmt->insertCardIntoLegallist($card);
      if(!$success) {
        info("Can't add {$card} to $name Legal list, it is not in the database.");
        $set = findSetForCard($card);
        addSet($set, 4);
        return 0;
      }
    }

    if ($i++ == 200) {
      set_time_limit(0);
      info('.', false);
      $i = 0;
    }
  }
}

function findSetForCard($card) {
  $card = urlencode($card);
  $data = json_decode(file_get_contents("http://api.scryfall.com/cards/named?exact={$card}"));
  return strtoupper($data->set);
}
