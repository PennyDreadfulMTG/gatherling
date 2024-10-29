<?php

declare(strict_types=1);

use Gatherling\Models\Database;
use Gatherling\Models\Decksearch;
use Gatherling\Views\Pages\DeckSearch as DeckSearchPage;
use Zebra_Pagination as Pagination;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\session;

require_once 'lib.php';

function main(): void
{
    if (count($_POST) > 0) {
        unset($_SESSION['search_results']);
    }

    $decksearch = new Decksearch();
    if (!isset($_GET['mode'])) {
        $_GET['mode'] = '';
    }

    $results = $errors = [];
    if (isset($_GET['mode']) && strcmp(get()->string('mode'), 'search') == 0 && !isset($_GET['page'])) {
        if (!empty($_POST['format'])) {
            $decksearch->searchByFormat(post()->string('format'));
            $_SESSION['format'] = $_POST['format'];
        } else {
            unset($_SESSION['format']);
        }
        if (!empty($_POST['cardname'])) {
            $decksearch->searchByCardName(post()->string('cardname'));
            $_SESSION['cardname'] = $_POST['cardname'];
        } else {
            unset($_SESSION['cardname']);
        }
        if (!empty($_POST['player'])) {
            $decksearch->searchByPlayer(post()->string('player'));
            $_SESSION['player'] = $_POST['player'];
        } else {
            unset($_SESSION['player']);
        }
        if (!empty($_POST['archetype'])) {
            $decksearch->searchByArchetype(post()->string('archetype'));
            $_SESSION['archetype'] = $_POST['archetype'];
        } else {
            unset($_SESSION['archetype']);
        }
        if (!empty($_POST['medals'])) {
            $decksearch->searchByMedals(post()->string('medals'));
            $_SESSION['medals'] = $_POST['medals'];
        } else {
            unset($_SESSION['medals']);
        }
        if (!empty($_POST['series'])) {
            $decksearch->searchBySeries(post()->string('series'));
            $_SESSION['series'] = $_POST['series'];
        } else {
            unset($_SESSION['series']);
        }
        if (isset($_POST['color'])) {
            $decksearch->searchByColor(post()->dictString('color'));
            $_SESSION['color'] = $_POST['color'];
        } else {
            unset($_SESSION['color']);
        }
        $results = $decksearch->getFinalResults();
        if ($results) {
            $_SESSION['search_results'] = $results;
        } else {
            $errors = $decksearch->errors;
        }
    } else {
        if (isset($_GET['page']) && isset($_SESSION['search_results'])) {
            $results = session()->listInt('search_results');
        } else {
            unset($_SESSION['search_results']);
            unset($_SESSION['archetype']);
            unset($_SESSION['format']);
            unset($_SESSION['series']);
            unset($_SESSION['name']);
            unset($_SESSION['player']);
            unset($_SESSION['cardname']);
            unset($_SESSION['color']);
            unset($_SESSION['medals']);
        }
    }

    $playerName = session()->string('player', '');
    $cardName = session()->string('cardname', '');
    $formatName = session()->string('format', '');
    $archetype = session()->string('archetype', '');
    $seriesName = session()->string('series', '');
    $medals = session()->string('medals', '');
    $colors = session()->dictString('color');

    if ($results === false) {
        $results = [];
    }

    $page = new DeckSearchPage($results, server()->string('PHP_SELF'), $errors, $playerName, $cardName, $formatName, $archetype, $seriesName, $medals, $colors);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
