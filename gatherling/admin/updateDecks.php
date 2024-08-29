<?php

require_once '../lib.php';

$some_admin = Player::getSessionPlayer();
if (!is_null($some_admin)) {
    if (!$some_admin->isSuper()) {
        header('Location: http://www.gatherling.com/gatherling.php');
        exit(0);
    }
} else {
    header('Location: gatherling.php');
    exit(0);
}

$ndx = 8000;
$successUpdatedDecks = 0;
$failedUpdatedDecks = 0;
$decksChecked = 0;
$err = 0;

while ($ndx > 7000) {
    $sql = 'SELECT Count(*) FROM decks WHERE id = ?';
    $result = Database::single_result_single_param($sql, 'd', $ndx);
    if ($result) {
        $decksChecked++;

        $deck = new Deck($ndx);
        if ($deck != null || $deck->id != 0) {
            $sql2 = 'select Count(*) FROM entries where deck = ?';
            $result2 = Database::single_result_single_param($sql2, 'd', $ndx);
            if ($result2) {
                $db = Database::getConnection();
                $stmt = $db->prepare('UPDATE decks SET playername = ?, format = ?, deck_colors = ?, created_date = ? WHERE id = ?');
                echo 'Playername: '.$deck->playername.'<br />Format: '.$deck->format.'<br />Color String: '.$deck->deck_color_str."<br />\n";
                $stmt->bind_param('ssssd', $deck->playername, $deck->format, $deck->deck_color_str, $deck->created_date, $deck->id);
                $stmt->execute() or exit($stmt->error);
                echo '<a href="deck.php?mode=view&id='.$deck->id.'">'.$deck->name.'</a> Deck ID: '.$deck->id.' Sucessfully updated<br />';
                $successUpdatedDecks++;
            //if ($decksChecked > 10) { die; }
            } else {
                echo  $deck->id.' Has no data in entries, missing playername, no player association. Deck will be deleted<br />';
                $failedUpdatedDecks++;
                $deck->delete();
            }
        }
    }
    $ndx--;
}
$stmt->close();
echo "Total decks updated: $successUpdatedDecks<br />\n";
echo "Total decks failed: $failedUpdatedDecks<br />\n";
echo "Total decks checked: $decksChecked<br /><br /><br /><br />\n";
