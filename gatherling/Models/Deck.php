<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Views\Components\DeckLink;

class Deck
{
    public ?int $id;
    public ?string $name = null;
    public ?string $archetype = null;
    public ?string $notes;
    /** @var array<string, int> */
    public array $maindeck_cards = []; // Has many maindeck_cards through deckcontents (issideboard = 0)
    /** @var array<string, int> */
    public array $sideboard_cards = []; // Has many sideboard_cards through deckcontents (issideboard = 1)
    public int $maindeck_cardcount = 0;
    public int $sideboard_cardcount = 0;
    /** @var array<string> */
    public array $errors = [];
    public ?string $playername = null; // Belongs to player through entries, now held in decks table
    public ?string $eventname; // Belongs to event through entries
    public ?int $event_id; // Belongs to event through entries
    public ?int $subeventid; // Belongs to event
    public ?string $format = null; // Belongs to event..  now held in decks table
    public ?string $tribe; // used only for tribal events
    public ?string $deck_color_str;  // Holds the final string color string
    public ?string $created_date; // Date deck was created
    public ?string $deck_hash;
    public ?string $sideboard_hash;
    public ?string $whole_hash;
    /** @var array<string, int> */
    public array $unparsed_cards;
    /** @var array<string, int> */
    public array $unparsed_side;
    public ?string $deck_contents_cache;
    /** @var ?list<self> */
    public ?array $identical_decks;
    public ?string $medal = null; // has a medal
    public bool $new; // is new

    public function __construct(mixed $id)
    {
        if ($id == 0) {
            $this->id = 0;
            $this->new = true;
            return;
        }
        $database = Database::getConnection();
        $stmt = $database->prepare('SELECT id, name, playername, archetype, format, tribe, notes, deck_hash,
                                       sideboard_hash, whole_hash, created_date, deck_colors
                                FROM decks d
                                WHERE id = ?');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result(
            $this->id,
            $this->name,
            $this->playername,
            $this->archetype,
            $this->format,
            $this->tribe,
            $this->notes,
            $this->deck_hash,
            $this->sideboard_hash,
            $this->whole_hash,
            $this->created_date,
            $this->deck_color_str
        );

        if ($stmt->fetch() == null) {
            $this->id = 0;
            $this->new = true;

            return;
        }

        $this->new = false;

        $stmt->close();

        // trys to grab the playername if it was not in the decks table
        if (empty($this->playername)) {
            $stmt = $database->prepare('SELECT p.name
                            FROM players p, entries e, decks d
                            WHERE p.name = e.player
                            AND d.id = e.deck
                            AND d.id = ?');
            $stmt->bind_param('d', $id);
            $stmt->execute();
            $stmt->bind_result($this->playername);
            $stmt->fetch();
            $stmt->close();
        }

        // Check for created date in entries if it isn't in the decks table
        if (is_null($this->created_date)) {
            $stmt = $database->prepare('SELECT registered_at FROM entries where deck = ?');
            $stmt->bind_param('d', $id);
            $stmt->execute();
            $stmt->bind_result($this->created_date);
            $stmt->fetch();
            $stmt->close();
        }

        // Retrieve cards.
        $stmt = $database->prepare('SELECT c.name, dc.qty, dc.issideboard
                                FROM cards c, deckcontents dc, decks d
                                WHERE d.id = dc.deck
                                AND c.id = dc.card
                                AND d.id = ?
                                ORDER BY c.name');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result($cardname, $cardqty, $isside);

        $this->maindeck_cardcount = 0;
        $this->sideboard_cardcount = 0;
        while ($stmt->fetch()) {
            if ($isside == 0) {
                $this->maindeck_cards[$cardname] = $cardqty;
                $this->maindeck_cardcount += $cardqty;
            } else {
                $this->sideboard_cards[$cardname] = $cardqty;
                $this->sideboard_cardcount += $cardqty;
            }
        }

        $stmt->close();

        // Retrieve event
        $stmt = $database->prepare('SELECT e.name, e.id
                                FROM events e, entries n, decks d
                                WHERE d.id = ? and d.id = n.deck
                                AND n.event_id = e.id');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result($this->eventname, $this->event_id);
        $stmt->fetch();
        $stmt->close();

        // Retrieve format - LI: added subeventid holder
        // The entire constructor does not run when a new deck is created, so this has to be duplicated
        // later in the save() function
        //     l
        // Find subevent id     - ignores sub-subevents like finals, which have the same name but different subevent id
        if (!is_null($this->eventname)) {
            $this->format = Database::singleResultSingleParam('SELECT  events.format
                                                               FROM entries INNER JOIN events
                                                               ON entries.event_id = events.id
                                                               WHERE entries.deck = ?', 'd', $this->id);
            $this->subeventid = (int) Database::singleResultSingleParam('SELECT id
                                                                   FROM subevents
                                                                   WHERE parent = ?', 's', $this->eventname);
        } else {
            $this->format = '';
            $this->subeventid = null;
        }

        // Retrieve medal
        $stmt = $database->prepare('SELECT n.medal
                                FROM entries n
                                WHERE n.deck = ?');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result($this->medal);
        $stmt->fetch();
        $stmt->close();
        if ($this->medal == null) {
            $this->medal = 'dot';
        }

        // Retrieve errors
        $stmt = $database->prepare('Select error
                                FROM deckerrors
                                WHERE deck = ?');
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->bind_result($error);

        while ($stmt->fetch()) {
            $this->errors[] = $error;
        }
        $stmt->close();

        // if there isnt a color string get one, must execute
        // after card retrival
        if (empty($this->deck_color_str)) {
            $this->getDeckColors();
        }
    }

    /**
     * @return array<string>
     */
    public static function getArchetypes(): array
    {
        return Database::listResult('SELECT name FROM archetypes WHERE priority > 0 ORDER BY priority DESC, name');
    }

    public function getEntry(): Entry
    {
        return new Entry($this->event_id, $this->playername);
    }

    public function recordString(): string
    {
        if ($this->playername == null) {
            return '?-?';
        }

        return $this->getEntry()->recordString();
    }

    public function getColorImages(): string
    {
        $count = $this->getColorCounts();
        $str = '';
        foreach ($count as $color => $n) {
            if ($n > 0) {
                $str = $str . image_tag("mana{$color}.png");
            }
        }

        return $str;
    }

    public function getDeckColors(): void
    {
        // First, get a list of casting costs, CC is used so we can determine
        // if its an artifact
        $color = [];
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT isw
                          AS w, isg
                          AS g, isu
                          AS u, isr
                          AS r, isb
                          AS b, cost
                          FROM cards c, deckcontents d
                          WHERE d.deck = ?
                          AND c.id = d.card
                          AND d.issideboard != 1');
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->bind_result($color['w'], $color['g'], $color['u'], $color['r'], $color['b'], $color['cost']);

        $str = [];

        // loop through results
        while ($stmt->fetch()) {
            foreach (['u', 'g', 'b', 'r', 'w'] as $c) {
                if ($color[$c]) {
                    $str[$c] = $c;
                }
            }
        }

        // alpabetize and sets the $deck_color_str
        // this should be changed to MTG Color wheel sort order of WUBRG
        ksort($str);
        $this->deck_color_str = '';
        foreach ($str as $value) {
            $this->deck_color_str .= $value;
        }
        $stmt->close();
    }

    /**
     * @return array<string, int>
     */
    public function getColorCounts(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT sum(isw*d.qty)
                          AS w, sum(isg*d.qty)
                          AS g, sum(isu*d.qty)
                          AS u, sum(isr*d.qty)
                          AS r, sum(isb*d.qty)
                          AS b
                          FROM cards c, deckcontents d
                          WHERE d.deck = ?
                          AND c.id = d.card
                          AND d.issideboard != 1');
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $count = [];
        $stmt->bind_result($count['w'], $count['g'], $count['u'], $count['r'], $count['b']);
        $stmt->fetch();

        $stmt->close();

        return $count;
    }

    /** @return array<int, int> */
    public function getCastingCosts(): array
    {
        $db = Database::getConnection();
        $result = $db->query("SELECT convertedcost
                          AS cc, sum(qty)
                          AS s
                          FROM cards c, deckcontents d
                          WHERE d.deck = {$this->id}
                          AND c.id = d.card
                          AND d.issideboard = 0
                          GROUP BY c.convertedcost
                          HAVING cc > 0");

        $convertedcosts = [];
        while ($res = $result->fetch_assoc()) {
            $convertedcosts[(int) $res['cc']] = (int) $res['s'];
        }

        return $convertedcosts;
    }

    public function getEvent(): Event
    {
        return new Event($this->event_id);
    }

    /**
     * @param array<string, int> $cards
     */
    public function getCardCount(array $cards): int
    {
        return array_sum($cards);
    }

    /**
     * @return array<string, int>
     */
    public function getCreatureCards(): array
    {
        $db = Database::getConnection();
        $result = $db->query("SELECT dc.qty, c.name
                          FROM deckcontents dc, cards c
                          WHERE c.id = dc.card
                          AND dc.deck = {$this->id}
                          AND c.type
                          LIKE '%Creature%'
                          AND dc.issideboard = 0
                          ORDER BY dc.qty
                          DESC, c.name");
        if (!$result) {
            throw new Exception($db->error, 1);
        }
        $cards = [];
        while ($res = $result->fetch_assoc()) {
            $cards[(string) $res['name']] = (int) $res['qty'];
        }

        return $cards;
    }

    // find a way to list the id as a param
    /**
     * @return array<string, int>
     */
    public function getLandCards(): array
    {
        $db = Database::getConnection();
        $result = $db->query("SELECT dc.qty, c.name
                          FROM deckcontents dc, cards c
                          WHERE c.id = dc.card
                          AND dc.deck = {$this->id}
                          AND c.type
                          LIKE '%Land%'
                          AND dc.issideboard = 0
                          ORDER BY dc.qty
                          DESC, c.name");

        $cards = [];
        while ($res = $result->fetch_assoc()) {
            $cards[(string) $res['name']] = (int) $res['qty'];
        }

        return $cards;
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('Select error FROM deckerrors WHERE deck = ?');
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->bind_result($error);

        $errors = [];
        while ($stmt->fetch()) {
            $errors[] = $error;
        }
        $stmt->close();

        return $errors;
    }

    // find a way to list the id as a param
    /**
     * @return array<string, int>
     */
    public function getOtherCards(): array
    {
        $db = Database::getConnection();
        $result = $db->query("SELECT dc.qty, c.name
                         FROM deckcontents dc, cards c
                         WHERE c.id = dc.card
                         AND dc.deck = {$this->id}
                         AND c.type
                         NOT LIKE '%Creature%'
                         AND c.type
                         NOT LIKE '%Land%'
                         AND dc.issideboard = 0
                         ORDER BY dc.qty
                         DESC, c.name");

        $cards = [];
        while ($res = $result->fetch_assoc()) {
            $cards[(string) $res['name']] = (int)$res['qty'];
        }

        return $cards;
    }

    /**
     * @return list<Matchup>
     */
    public function getMatches(): array
    {
        if ($this->playername == null) {
            return [];
        }

        return $this->getEntry()->getMatches();
    }

    public function getPlayer(): Player
    {
        return new Player($this->playername);
    }

    public function canEdit(string|false $username): bool
    {
        if ($username === false) {
            return false;
        }
        $event = $this->getEvent();
        $player = new Player($username);

        if (
            $player->isSuper() ||
            $event->isHost($username) ||
            $event->isOrganizer($username) ||
            (!$event->finalized && !$this->isValid() && strcasecmp($username, $this->playername) == 0) ||
            (!$event->finalized && !$event->active && strcasecmp($username, $this->playername) == 0)
        ) {
            return true;
        }

        return false;
    }

    public function canView(string|false $username): bool
    {
        if ($username === false) {
            return false;
        }
        $event = $this->getEvent();
        $player = new Player($username);

        if (($event->finalized && !$event->active) || $event->private_decks == 0) {
            return true;
        } elseif ($event->current_round > $event->mainrounds && !$event->private_finals) {
            return true;
        } else {
            if (
                $player->isSuper() ||
                $event->isHost($username) ||
                $event->isOrganizer($username) ||
                strcasecmp($username, $this->playername) == 0
            ) {
                return true;
            }
        }

        return false;
    }

    public function isValid(): bool
    {
        return count($this->errors) == 0;
    }

    public function delete(): void
    {
        $db = Database::getConnection();
        $db->autocommit(false);
        $this->errors = [];

        // Checks to see if any matches have been played by the deck, if not deletes the deck
        if (count($this->getMatches()) == 0) {
            $succ = $db->query("DELETE FROM entries WHERE deck = {$this->id}");
            if (!$succ) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception("Can't delete deck contents {$this->id} expection 1");
            }

            $succ = $db->query("DELETE FROM deckerrors WHERE deck = {$this->id}");
            if (!$succ) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception("Can't delete deck contents {$this->id} expection 2");
            }

            $succ = $db->query("DELETE FROM deckcontents WHERE deck = {$this->id}");
            if (!$succ) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception("Can't delete deck contents {$this->id} expection 3");
            }

            $succ = $db->query("DELETE FROM decks WHERE id = {$this->id}");
            if (!$succ) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception("Can't delete deck contents {$this->id} expection 4");
            }

            $db->commit();
            $db->autocommit(true);
        }
    }

    public function save(): bool
    {
        $db = Database::getConnection();
        $db->autocommit(false);
        $this->errors = [];

        $this->name = $this->name ?: 'Temp';
        if ($this->archetype != 'Unclassified' && !in_array($this->archetype, self::getArchetypes())) {
            $this->archetype = 'Unclassified';
        }

        // had to put this here since the constructor doesn't run entirely when a new deck is created
        if (!is_null($this->event_id) && is_null($this->format)) {
            $this->format = Database::singleResultSingleParam('SELECT format
                                                               FROM events
                                                               WHERE id = ?', 'd', $this->event_id);
        }
        $format = new Format($this->format);

        //Extra check to make sure a duplicate deck won't be created
        if ($this->id == 0) {
            $check_entry = Entry::findByEventAndPlayer($this->event_id, $this->playername);
            if ($check_entry && $check_entry->deck != null) { //The player already registered a deck
                $this->id = $check_entry->deck->id;
            }
        }

        if ($this->id == 0) {
            // New record.  Set up the decks entry and the Entry.
            $stmt = $db->prepare('INSERT INTO decks (archetype, name, playername, format, tribe, notes, created_date) values(?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('ssssss', $this->archetype, $this->name, $this->playername, $this->format, $this->tribe, $this->notes);
            $stmt->execute();
            $this->id = (int) $stmt->insert_id;

            $stmt = $db->prepare('UPDATE entries SET deck = ? WHERE player = ? AND event_id = ?');
            $stmt->bind_param('dsd', $this->id, $this->playername, $this->event_id);
            $stmt->execute();
            if ($stmt->affected_rows != 1) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception('Entry for ' . $this->playername . ' in ' . $this->eventname . ' not found');
            }
        } else {
            $stmt = $db->prepare('UPDATE decks SET archetype = ?, name = ?, format = ?, tribe = ?, deck_colors = ?, notes = ? WHERE id = ?');
            if (!$stmt) {
                throw new \Exception($db->error);
            }
            $stmt->bind_param('ssssssd', $this->archetype, $this->name, $this->format, $this->tribe, $this->deck_color_str, $this->notes, $this->id);
            if (!$stmt->execute()) {
                $db->rollback();
                $db->autocommit(true);

                throw new Exception('Can\'t update deck ' . $this->id);
            }
            $format = new Format($this->format);
        }

        // TODO: find a way to list the id as a param
        $succ = $db->query("DELETE FROM deckcontents WHERE deck = {$this->id}");

        if (!$succ) {
            $db->rollback();
            $db->autocommit(true);

            throw new Exception("Can't update deck contents {$this->id}");
        }

        // find a way to list the id as a param
        $succ = $db->query("DELETE FROM deckerrors WHERE deck = {$this->id}");

        if (!$succ) {
            $db->rollback();
            $db->autocommit(true);

            throw new Exception("Can't update deck contents {$this->id}");
        }

        //////
        /// parsing start

        // begin parsing deck list
        $newmaindeck = [];
        $legalCards = $format->getLegalList();
        $this->maindeck_cardcount = 0;

        foreach ($this->maindeck_cards as $card => $amt) {
            $amt = (int) $amt;
            $card = stripslashes($card);
            $testcard = Format::getCardName($card);
            if (is_null($testcard)) {
                $testcard = Format::getCardNameFromPartialDFC($card);
            }
            if (is_null($testcard)) {
                $this->errors[] = "Could not find card in database, did you make a typo?: {$card}";
                continue;
            }
            $cardar = $format->getLegalCard($testcard);
            if (is_null($cardar)) {
                $this->errors[] = "Could not find maindeck card: {$amt} {$card} in legal sets";

                if (!isset($this->unparsed_cards[$card])) {
                    $this->unparsed_cards[$card] = 0;
                }
                $this->unparsed_cards[$card] += $amt;
                continue;
            } else {
                $card = $testcard;
            }

            // Restricted Card list. Only one of these cards is alowed in a deck
            if ($format->isCardOnRestrictedList($card) && $amt > 1) {
                $this->errors[] = "Maindeck card: {$amt} {$card} is on the restricted list.
                             Only one of this card may be in a deck list.";
                if (!isset($this->unparsed_cards[$card])) {
                    $this->unparsed_cards[$card] = 0;
                }
                $this->unparsed_cards[$card] += $amt;
                continue;
            }

            if ($format->limitless) {
                // Ignore this check
            } elseif ($format->singleton) {
                if (!$format->isCardSingletonLegal($card, $amt)) {
                    $this->errors[] = "Singleton formats allow only one of any card, except basic lands.
                                 You entered {$amt} {$card} in your mainboard.";
                }
            } elseif (!$format->isQuantityLegal($card, $amt)) {
                $this->errors[] = "No more than four of any card is allowed in this format, except basic lands.
                                 You entered {$amt} {$card} in your mainboard.";
            }

            if ($format->isCardOnBanList($card)) {
                $this->errors[] = "Maindeck card: {$amt} {$card} is banned in {$format->name}";
                if (!isset($this->unparsed_cards[$card])) {
                    $this->unparsed_cards[$card] = 0;
                }
                $this->unparsed_cards[$card] += $amt;
                continue;
            }
            if (count($legalCards)) {
                if (!$format->isCardOnLegalList($card)) {
                    $this->errors[] = "Maindeck card: {$amt} {$card} is not on the legal card list";
                    if (!isset($this->unparsed_cards[$card])) {
                        $this->unparsed_cards[$card] = 0;
                    }
                    $this->unparsed_cards[$card] += $amt;
                    continue;
                }
            }
            if (!$format->isCardLegalByRarity($card)) {
                $this->errors[] = "Maindeck card : {$amt} {$card} is illegal by rarity.";
                if (!isset($this->unparsed_cards[$card])) {
                    $this->unparsed_cards[$card] = 0;
                }
                $this->unparsed_cards[$card] += $amt;
                continue;
            }
            $this->maindeck_cardcount += $amt;
            $stmt = $db->prepare('INSERT INTO deckcontents (deck, card, issideboard, qty) values(?, ?, 0, ?)');
            $stmt->bind_param('ddd', $this->id, $cardar['id'], $amt);
            $stmt->execute();
            $newmaindeck[$cardar['name']] = $amt;
        }

        $this->maindeck_cards = $newmaindeck;

        // begin parsing sideboard
        $newsideboard = [];
        $this->sideboard_cardcount = 0;

        foreach ($this->sideboard_cards as $card => $amt) {
            $amt = (int) $amt;
            $card = stripslashes($card);
            $testcard = Format::getCardName($card);
            if (is_null($testcard)) {
                $testcard = Format::getCardNameFromPartialDFC($card);
            }
            if (is_null($testcard)) {
                $this->errors[] = "Could not find card in database, did you make a typo?: {$card}";
                continue;
            }
            $cardar = $format->getLegalCard($testcard);
            if (is_null($cardar)) {
                $this->errors[] = "Could not find sideboard card: {$amt} {$card} in legal sets";

                if (!isset($this->unparsed_side[$card])) {
                    $this->unparsed_side[$card] = 0;
                }
                $this->unparsed_side[$card] += $amt;
                continue;
            } else {
                $card = $testcard;
            }

            // Restricted Card list. Only one of these cards is alowed in a deck
            if ($format->isCardOnRestrictedList($card)) {
                $restrictedError = false;
                if ($amt > 1) {
                    $restrictedError = true;
                }
                foreach ($this->maindeck_cards as $restrictedCard => $mainamt) {
                    if ($restrictedCard == $card) {
                        $restrictedError = true;
                        break;
                    }
                }
                if ($restrictedError) {
                    $this->errors[] = "Sideboard card: {$amt} {$card} is on the restricted list.
                                 Only one of this card may be in a deck list.";
                    if (!isset($this->unparsed_side[$card])) {
                        $this->unparsed_side[$card] = 0;
                    }
                    $this->unparsed_side[$card] += $amt;
                    continue;
                }
            }

            if ($format->singleton) {
                if (!$format->isCardSingletonLegal($card, $amt)) {
                    $this->errors[] = "Singleton formats allow only one of any card, except basic lands.
                                 You entered {$amt} {$card} on your sideboard.";
                }
                foreach ($this->maindeck_cards as $singletonCard => $mainamt) {
                    if ($singletonCard == $card) {
                        $this->errors[] = "Singleton formats allow only one of any card, except basic lands.
                                     You entered {$amt} {$card} on your sideboard
                                     and {$mainamt} {$card} in your mainboard.";
                        break;
                    }
                }
            } else {
                if (!$format->isQuantityLegal($card, $amt)) {
                    $this->errors[] = "No more than four of any card is allowed in this format, except basic lands.
                                 You entered {$amt} {$card} on your sideboard.";
                } else {
                    foreach ($this->maindeck_cards as $quantityCard => $mainamt) {
                        if (!$format->isQuantityLegalAgainstMain($card, $amt, $quantityCard, $mainamt)) {
                            $this->errors[] = "No more than four of any card is allowed in this format, except basic lands.
                                         You entered {$amt} {$card} on your sideboard
                                         and {$mainamt} {$card} in your mainboard.";
                            break;
                        }
                    }
                }
            }

            if ($format->isCardOnBanList($card)) {
                $this->errors[] = "Sideboard card: {$amt} {$card} is banned in {$format->name}";
                if (!isset($this->unparsed_side[$card])) {
                    $this->unparsed_side[$card] = 0;
                }
                $this->unparsed_side[$card] += $amt;
                continue;
            }
            if (count($legalCards)) {
                if (!$format->isCardOnLegalList($card)) {
                    $this->errors[] = "Sideboard card: {$amt} {$card} is not on the legal card list";
                    if (!isset($this->unparsed_side[$card])) {
                        $this->unparsed_side[$card] = 0;
                    }
                    $this->unparsed_side[$card] += $amt;
                    continue;
                }
            }
            if (!$format->isCardLegalByRarity($card)) {
                $this->errors[] = "Sideboard card : {$amt} {$card} is illegal by rarity.";
                if (!isset($this->unparsed_side[$card])) {
                    $this->unparsed_side[$card] = 0;
                }
                $this->unparsed_side[$card] += $amt;
                continue;
            }

            $this->sideboard_cardcount += $amt;
            $stmt = $db->prepare('INSERT INTO deckcontents (deck, card, issideboard, qty) values(?, ?, 1, ?)');
            $stmt->bind_param('ddd', $this->id, $cardar['id'], $amt);
            $stmt->execute();
            $newsideboard[$cardar['name']] = $amt;
        }

        $this->sideboard_cards = $newsideboard;

        // needs to be after card parsing so it can work with new decks.

        $this->getDeckColors(); // gets the deck colors

        $stmt = $db->prepare('UPDATE decks SET notes = ?, deck_colors = ? WHERE id = ?');
        if (!$stmt) {
            throw new Exception("Failed to prepare db statement");
        }
        $stmt->bind_param('ssd', $this->notes, $this->deck_color_str, $this->id);
        if (!$stmt->execute()) {
            $db->rollback();
            $db->autocommit(true);

            throw new Exception('Can\'t update deck ' . $this->id);
        }

        $this->deck_contents_cache = implode('|', array_merge(
            array_keys($this->maindeck_cards),
            array_keys($this->sideboard_cards)
        ));

        $stmt = $db->prepare('UPDATE decks set deck_contents_cache = ? WHERE id = ?');

        $stmt->bind_param('sd', $this->deck_contents_cache, $this->id);
        $stmt->execute();

        $db->commit();
        $db->autocommit(true);
        $this->calculateHashes();

        if ($this->maindeck_cardcount < $format->min_main_cards_allowed) {
            $this->errors[] = "This format requires a minimum of {$format->min_main_cards_allowed} Maindeck Cards";
        } elseif ($this->maindeck_cardcount > $format->max_main_cards_allowed) {
            $this->errors[] = "This format allows a maximum of {$format->max_main_cards_allowed} Maindeck Cards";
        }

        if (
            $this->sideboard_cardcount < $format->min_side_cards_allowed ||
            $this->sideboard_cardcount > $format->max_side_cards_allowed
        ) {
            $this->errors[] = "A legal sideboard for this format has between $format->min_side_cards_allowed cards, and
                              $format->max_side_cards_allowed cards.";
        }

        if ($format->commander) {
            if (!$format->isDeckCommanderLegal($this->id)) {
                $beg = $this->errors;
                $end = $format->getErrors();
                $this->errors = [];
                $this->errors = array_merge($beg, $end);
            }
        }

        if ($format->tribal) {
            if (!$format->isDeckTribalLegal($this->id)) {
                $beg = $this->errors;
                $end = $format->getErrors();
                $this->errors = [];
                $this->errors = array_merge($beg, $end);
            }
            $this->tribe = self::getTribe();
        }

        foreach ($this->errors as $error) {
            $stmt = $db->prepare('INSERT INTO deckerrors (deck, error) values(?, ?)');
            $stmt->bind_param('ds', $this->id, $error);
            $stmt->execute();
        }

        // Autonamer Function
        if ($this->name == 'Temp') {
            if ($format->tribal) {
                $this->name = strtoupper($this->deck_color_str) . ' ' . $this->tribe;
            } else {
                $this->name = strtoupper($this->deck_color_str) . ' ' . $this->archetype;
            }
            $stmt = $db->prepare('UPDATE decks set name = ? WHERE id = ?');
            $stmt->bind_param('ss', $this->name, $this->id);
            $stmt->execute();
        }

        return true;
    }

    public function getTribe(): ?string
    {
        return Database::singleResultSingleParam('SELECT tribe FROM decks WHERE id = ?', 'd', $this->id);
    }

    /**
     * @return list<self>
     */
    public function findIdenticalDecks(): array
    {
        if (!isset($this->identical_decks)) {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT d.id
                            FROM decks d, entries n, events e
                            WHERE deck_hash = ?
                            AND d.id != ?
                            AND n.deck = d.id
                            AND e.id = n.event_id
                            AND e.finalized = 1
                            ORDER BY e.start
                            DESC');
            $stmt->bind_param('sd', $this->deck_hash, $this->id);
            $same_ids = [];
            $this_id = 0;
            $stmt->execute();
            $stmt->bind_result($this_id);
            while ($stmt->fetch()) {
                $same_ids[] = $this_id;
            }
            $stmt->close();

            $decks = [];

            foreach ($same_ids as $other_deck_id) {
                $possibledeck = new self($other_deck_id);
                if (isset($possibledeck->playername)) {
                    $decks[] = $possibledeck;
                }
            }
            $this->identical_decks = $decks;
        }

        return $this->identical_decks;
    }

    public function calculateHashes(): void
    {
        // Deck HASHES are an easy way to compare two decks for EQUALITY.
        // They are computed as follows:
        //  A string is built with the following format:
        //   "(amt)(Cardname)(amt)(Cardname)..."
        //  The cardnames are unique per Magic: The Gathering
        //  The cardnames are lexographically sorted!
        //  The amounts are NOT PADDED: 1 => 1, 10 => 10, 100 => 100
        //  There is NO SPACE BETWEEN THE amount and the cardname, or between cards
        //  Make this string for the main deck and the sideboard.
        //  Concatenate these strings: maindeckStr + "<sb>" + sideboardStr
        //  Make a SHA-1 hash of this string for the whole_hash
        //  Make a SHA-1 hash of the maindeckStr for the maindeck_hash
        //  Make a SHA-1 hash of the sideboardStr for the sideboard_hash
        $cards = array_keys($this->maindeck_cards);
        sort($cards, SORT_STRING);
        $maindeckStr = '';
        foreach ($cards as $cardname) {
            $maindeckStr .= $this->maindeck_cards[$cardname] . $cardname;
        }
        $this->deck_hash = sha1($maindeckStr);
        $sideboardStr = '';
        $cards = array_keys($this->sideboard_cards);
        sort($cards, SORT_STRING);
        foreach ($cards as $cardname) {
            $sideboardStr .= $this->sideboard_cards[$cardname] . $cardname;
        }
        $this->sideboard_hash = sha1($sideboardStr);
        $this->whole_hash = sha1($maindeckStr . '<sb>' . $sideboardStr);
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE decks SET sideboard_hash = ?, deck_hash = ?, whole_hash = ? where id = ?');
        $stmt->bind_param('sssd', $this->sideboard_hash, $this->deck_hash, $this->whole_hash, $this->id);
        $stmt->execute();
        $stmt->close();
    }

    public static function uniqueCount(): int
    {
        $db = @Database::getConnection();
        $stmt = $db->prepare('SELECT COUNT(deck_hash) FROM decks GROUP BY deck_hash');
        $stmt->execute();
        $stmt->store_result();
        $uniquecount = (int) $stmt->num_rows;
        $stmt->close();

        return $uniquecount;
    }

    public function linkTo(): string
    {
        return (new DeckLink($this))->render();
    }

    public function colorStr(): string
    {
        $row = $this->getColorCounts();
        $colors = ['w', 'g', 'u', 'r', 'b'];
        $s = implode('', array_filter($colors, fn($color) => $row[$color] > 0));

        $s = $s ?: 'blackout';
        return $s;
    }

    public function manaSrc(): string
    {
        if ($this->new) {
            return 'styles/images/manablackout.png';
        }

        return 'styles/images/mana' . rawurlencode($this->colorStr()) . '.png';
    }
}
