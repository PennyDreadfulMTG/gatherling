<?php

if (!isset($_REQUEST['action'])) {
    // The below is for backwards compat
    if (isset($_GET['deck'])) {
        $_REQUEST['action'] = 'deckinfo';
    } elseif (isset($_GET['addplayer']) && isset($_GET['event'])) {
        $_REQUEST['action'] = 'addplayer';
    } elseif (isset($_GET['delplayer']) && isset($_GET['event'])) {
        $_REQUEST['action'] = 'delplayer';
    } elseif (isset($_GET['dropplayer']) && isset($_GET['event'])) {
        $_REQUEST['action'] = 'dropplayer';
    }
}

require_once 'api.php';
