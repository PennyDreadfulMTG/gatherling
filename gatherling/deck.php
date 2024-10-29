<?php

declare(strict_types=1);

use Gatherling\Models\Deck;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\AuthFailed;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\DeckForm;
use Gatherling\Views\Components\DeckNotAllowed;
use Gatherling\Views\Components\DeckNotFound;
use Gatherling\Views\Components\DeckProfile;
use Gatherling\Views\Components\DeckRegisterForm;
use Gatherling\Views\Components\LoginRequired;
use Gatherling\Views\Components\NoDeckSpecified;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Pages\Deck as DeckPage;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\request;

require_once 'lib.php';

function main(): void
{
    $event = null;

    $title = 'Deck Database';
    if (isset($_GET['event'])) {
        if (!Event::exists(get()->string('event'))) {
            unset($_GET['event']);
        } else {
            $event = new Event(get()->string('event'));
            $title = $event->name ?? 'Deck Database';
        }
    }

    $requestMode = request()->string('mode', '');
    $postMode = post()->string('mode', '');

    $viewComponent = new NullComponent();
    if (strcmp($requestMode, 'view') == 0) {
        $deck = null;
        if (isset($_GET['event'])) {
            $deck = $event->getPlaceDeck('1st');
        } elseif (isset($_GET['id'])) {
            $deck = new Deck($_GET['id']);
        }
        $viewComponent = deckProfile($deck);
    } else {
        // Need to auth for everything else.
        if (!isset($_POST['player']) and isset($_GET['player'])) {
            $_POST['player'] = $_GET['player'];
        }
        $deck = isset($_POST['id']) ? new Deck($_POST['id']) : null;
        if (!isset($_POST['event'])) {
            if (!isset($_GET['event'])) {
                $_GET['event'] = '';
            }
            $_REQUEST['event'] = $_GET['event'];
        }

        if (isset($_REQUEST['event']) && is_null($event)) {
            $event = new Event(request()->string('event'));
        }

        $deck_player = isset($_POST['player']) ? post()->string('player') : Player::loginName();
        $playerName = isset($_POST['player']) ? post()->string('player') : get()->optionalString('player');
        $eventName = isset($_POST['player']) ? request()->optionalString('event') : get()->optionalString('event');
        // part of the reg-decklist feature. both "register" and "addregdeck" switches
        if (strcmp($requestMode, 'register') == 0) {
            $playerName = isset($_POST['player']) ? post()->string('player') : get()->string('player');
            $eventName = isset($_POST['player']) ? post()->string('event') : get()->string('event');
            $viewComponent = new DeckRegisterForm($playerName, $eventName);
        } elseif (strcmp($requestMode, 'addregdeck') == 0) {
            $deck = insertDeck($event, post()->string('name'), post()->string('archetype'), post()->string('notes'), post()->string('player'), post()->string('contents', ''), post()->string('sideboard', ''));
            $viewComponent = new DeckProfile($deck);
        } elseif (is_null($deck) && $event->name == '') {
            $viewComponent = new NoDeckSpecified();
        } elseif ($deck_player === false || !Player::isLoggedIn()) {
            $viewComponent = new LoginRequired();
        } elseif (checkDeckAuth($event, $deck_player, $deck)) {
            if (strcmp($postMode, 'Create Deck') == 0) {
                $deck = insertDeck($event, post()->string('name'), post()->string('archetype'), post()->string('notes'), post()->string('player'), post()->string('contents', ''), post()->string('sideboard', ''));
                if ($deck->isValid()) {
                    $viewComponent = deckProfile($deck);
                } else {
                    $viewComponent = deckForm($deck, $playerName, $eventName);
                }
            } elseif (strcmp($postMode, 'Update Deck') == 0) {
                $deck = updateDeck($deck, post()->string('archetype'), post()->string('name'), post()->string('notes'), post()->string('contents', ''), post()->string('sideboard', ''));
                $deck = new Deck($deck->id); // had to do this to get the constructor to run, otherwise errors weren't loading
                if ($deck->isValid()) {
                    $viewComponent = deckProfile($deck);
                } else {
                    $viewComponent = deckForm($deck, $playerName, $eventName);
                }
            } elseif (strcmp($postMode, 'Edit Deck') == 0) {
                $viewComponent = deckForm($deck, $playerName, $eventName);
            } elseif (strcmp($requestMode, 'create') == 0) {
                $viewComponent = deckForm(null, $playerName, $eventName);
            }
        } else {
            $viewComponent = new AuthFailed();
        }
    }
    $page = new DeckPage($title, $viewComponent);
    $page->send();
}

function insertDeck(Event $event, string $name, string $archetype, string $notes, string $playerName, string $contents, string $sideboard): Deck
{
    $deck = new Deck(0);

    $deck->name = $name;
    $deck->archetype = $archetype;
    $deck->notes = $notes;

    $deck->playername = $playerName;
    $deck->eventname = $event->name;
    $deck->event_id = $event->id;

    $deck->maindeck_cards = parseCardsWithQuantity($contents);
    $deck->sideboard_cards = parseCardsWithQuantity($sideboard);

    $deck->save();

    return $deck;
}

function updateDeck(Deck $deck, string $archetype, string $name, string $notes, string $contents, string $sideboard): Deck
{
    $deck->archetype = $archetype;
    $deck->name = $name;
    $deck->notes = $notes;

    $deck->maindeck_cards = parseCardsWithQuantity($contents);
    $deck->sideboard_cards = parseCardsWithQuantity($sideboard);

    $deck->save();

    return $deck;
}

function checkDeckAuth(Event $event, string|false $player, ?Deck $deck = null): bool
{
    if ($player === false || !Player::isLoggedIn()) {
        return false;
    }
    if (is_null($deck) && $event->id > 0) {
        // Creating a deck.
        $entry = new Entry($event->id, $player);
        $playerName = Player::loginName();
        return $playerName !== false && $entry->canCreateDeck($playerName);
    }
    // Updating a deck.
    return $deck->canEdit(Player::loginName());
}

function deckProfile(?Deck $deck): Component
{
    if ($deck == null || $deck->id == 0) {
        return new DeckNotFound();
    } elseif ($deck->canView(Player::loginName())) {
        return new DeckProfile($deck);
    } else {
        return new DeckNotAllowed();
    }
}

function deckForm(?Deck $deck, ?string $playerName = null, ?string $eventName = null): Component
{
    $create = is_null($deck) || $deck->id == 0;
    if (!$create) {
        $playerName = $deck->playername ?? false;
        $event = new Event($deck->eventname);
    } else {
        $event = new Event($eventName);
    }

    if (!checkDeckAuth($event, $playerName ?: false, $deck)) {
        return new AuthFailed();
    }
    assert($playerName !== false && $playerName !== null); // checkDeckAuth guarantees this

    return new DeckForm($deck, $playerName, $event);
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
