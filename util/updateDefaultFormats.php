<?php
require_once('../lib.php');

if (PHP_SAPI != "cli"){
  session_start();
  if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
    redirect("index.php");
  }
}

updateStandard();
updateModern();
updatePennyDreadful();

function info($text){
  if (PHP_SAPI == "cli"){
    echo $text . "\n";
  }
  else{
    echo $text . "<br/>";
  }
}

function LoadFormat($format){
  if (!Format::doesFormatExist($format))
  {
    $active_format = new Format("");
    $active_format->name = $format;
    $active_format->type = "System";
    $active_format->series_name = "System";
    $success = $active_format->save();
  }
  return new Format($format);
}

function do_query($query) {
  global $db;
  echo "Executing Query: $query <br />";
  $result = $db->query($query);
  if (!$result) {
    echo "!!!! - Error: ";
    echo $db->error;
    exit(0);
  }
  return $result;
}

function updateStandard(){
  info("Processing Standard...");
  $fmt = LoadFormat("Standard");
  $legal = json_decode(file_get_contents("http://whatsinstandard.com/api/v5/sets.json"));
  if (!$legal)
  {
    info("Unable to load WhatsInStandard API.  Aborting.");
    return;
  }
  Database::no_result_single_param("DELETE FROM setlegality WHERE format = ?", "s", $fmt->name);
  
  foreach ($legal->sets as $set){
    // $code = $set->code;
    $enter = strtotime($set->enter_date);
    $exit = strtotime($set->exit_date);
    $now = time();
    if ($exit == NULL)
      $exit = $now + 1;
    if ($exit < $now)
    {
      info("{$set->code} has rotated out.");
    }
    else if ($enter > $now)
    {
      info("{$set->code} is yet to be released.");
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
        redirect("insertcardset.php?cardsetcode={$set->code}");
      }
      $fmt->insertNewLegalSet($setName);
      info("{$set->code} is Standard Legal.");
    }
  }
}

function updateModern(){
  info("Processing Modern...");
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
      $notFound = true;
      foreach ($legal as $legalsetName) {
          if (strcmp($setName, $legalsetName) == 0) {  
            $notFound = false;
          }
      }
      if ($notFound) {
        $fmt->insertNewLegalSet($setName);
        info("{$setName} is Modern Legal.");
      }
    }
  }
}

function updatePennyDreadful()
{
  info("Processing PD...");
  $fmt = LoadFormat("Penny Dreadful");

  $legal_cards = parseCards(file_get_contents("http://pdmtgo.com/legal_cards.txt"));
  if ($legal_cards){
    foreach($legal_cards as $card) {
      $success = $fmt->insertCardIntoLegallist($card);
      if(!$success) {
        info("Can't add {$card} to Legal list, it is either not in the database, or already on the ban list.");
        return; 
      }
    }
  }
  else{
    info("Unable to fetch legal_cards.txt");
  }
}