<?php
session_start();
require_once('../lib.php');

$cardsets = Database::list_result("SELECT name FROM cardsets");

foreach ($cardsets as $set) {
    $cards = Database::list_result_single_param("SELECT name FROM cards WHERE `cardset` = ?", "s", $set);
    if (!$cards || count($cards) == 0){
        echo "{$set} needs reimporting.<br/>";
    }
}
?>