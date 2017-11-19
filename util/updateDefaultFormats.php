<?php
session_start();
require_once('../lib.php');

if (PHP_SAPI != "cli"){
  if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
    redirect("index.php");
  }
}

updateStandard();
updateModern();
updatePennyDreadful();

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
  echo "Processing Standard...<br/>\n";
  $fmt = LoadFormat("Standard");
  $legal = json_decode(file_get_contents("http://whatsinstandard.com/api/v5/sets.json"));
  if (!$legal)
  {
    echo "Unable to load WhatsInStandard API.  Aborting.<br/>\n";
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
      echo "{$set->code} has rotated out.<br/>\n";
    }
    else if ($enter > $now)
    {
      echo "{$set->code} is yet to be released.<br/>\n";
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
      echo "{$set->code} is Standard Legal.<br/>\n";      
    }
  }
}

function updateModern(){
  echo "Processing Modern...<br/>\n";
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
        echo "{$setName} is Modern Legal.<br/>\n";  
      }
    }
  }
}

function updatePennyDreadful()
{
  echo "Processing PD...<br/>\n";
  $fmt = LoadFormat("Penny Dreadful");

  $legal_cards = parseCards(file_get_contents("http://pdmtgo.com/legal_cards.txt"));
  if ($legal_cards){
    foreach($legal_cards as $card) {
      // echo "  {$card}<br/>\n";  
      $success = $fmt->insertCardIntoLegallist($card);
      if(!$success) {
        echo "Can't add {$card} to Legal list, it is either not in the database, already on the ban list, or already on the legal list<br/>\n";
        return; 
      }
    }
  }
  else{
    echo "Unable to fetch legal_cards.txt<br/>\n";  
  }
}