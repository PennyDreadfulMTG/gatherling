<?php

require 'lib.php'; 

session_start(); 

$some_admin = Player::getSessionPlayer();
if (!$some_admin->isSuper()) { 
  header("Location: gatherling.php"); 
  exit(0);
} 

$ndx = 7000;
$totalHangingDecks = 0;
$decksChecked = 0;

while($ndx > 0) {
    if (Database::single_result("SELECT Count(*) FROM decks d WHERE id = {$ndx}")) {
        $decksChecked++;
        $deck = new Deck($ndx);
        if ($deck != NULL || $deck->id != 0) {
            if ($deck->playername == NULL) {
                $totalHangingDecks++;
                echo "<a href=\"deck.php?mode=view&id=". $deck->id . "\">" . $deck->name . "</a> Deck ID: " . $deck->id . " is a hanging deck and will be deleted.<br />\n";
                $deck->delete();
            }
        }
    }
    $ndx--;
} 

echo "Total hanging decks found and removed: $totalHangingDecks<br />\n";
echo "Total decks checked: $decksChecked<br />\n";

?>