<?php

use Gatherling\Deck;
use Gatherling\Player;

require_once 'lib.php';

$id = 0;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

if ($id == 0) {
    header('location: player.php');
    exit;
}

$deck = new Deck($id);

if (!$deck->canView(Player::loginName())) {
    header('location: player.php');
    exit;
}

$content = '';

foreach ($deck->maindeck_cards as $card => $qty) {
    // Æ to AE litigation
    $card = normaliseCardName($card);
    $content .= $qty . ' ' . $card . "\r\n";
}

$content .= "\r\nSideboard\r\n";

foreach ($deck->sideboard_cards as $card => $qty) {
    $card = normaliseCardName($card);
    $content .= $qty . ' ' . $card . "\r\n";
}

$filename = preg_replace('/ /', '_', $deck->name) . '.txt';
header('Content-type: text/plain');
header("Content-Disposition: attachment; filename=$filename");
echo $content;
exit;
