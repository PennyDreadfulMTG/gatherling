<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Data\DB;
use InvalidArgumentException;
use Gatherling\Views\Components\DeckLink;

class Deck
{
    public ?int $id;
    public ?string $name = null;
    public ?string $archetype = null;
    public ?string $notes = null;
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
    public ?string $tribe = null; // used only for tribal events
    public ?string $deck_color_str = null;  // Holds the final string color string
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
        $sql = '
            SELECT
                id, name, playername, archetype, format, tribe, notes, deck_hash,
                sideboard_hash, whole_hash, created_date, deck_colors AS deck_color_str
            FROM
                decks d
            WHERE
                id = :id';
        $deck = DB::selectOnlyOrNull($sql, DeckDto::class, ['id' => $id]);
        if (!$deck) {
            $this->id = 0;
            $this->new = true;
            return;
        }
        foreach (get_object_vars($deck) as $key => $value) {
            $this->{$key} = $value;
        }
        $this->new = false;

        if (empty($this->playername)) {
            $sql = '
                SELECT
                    p.name
                FROM
                    players p, entries e, decks d
                WHERE
                    p.name = e.player AND d.id = e.deck AND d.id = :id';
            $this->playername = DB::optionalString($sql, ['id' => $id]);
        }

        // Check for created date in entries if it isn't in the decks table
        if (is_null($this->created_date)) {
            $sql = 'SELECT registered_at FROM entries where deck = :id';
            $this->created_date = DB::optionalString($sql, ['id' => $id]);
        }

        // Retrieve cards.
        $sql = '
            SELECT
                c.name, dc.qty, dc.issideboard
            FROM
                cards c, deckcontents dc, decks d
            WHERE
                d.id = dc.deck
                AND c.id = dc.card
                AND d.id = :id
            ORDER BY
                c.name';
        $cards = DB::select($sql, DeckCardDto::class, ['id' => $id]);

        $this->maindeck_cardcount = 0;
        $this->sideboard_cardcount = 0;
        foreach ($cards as $card) {
            if ($card->issideboard == 0) {
                $this->maindeck_cards[$card->name] = $card->qty;
                $this->maindeck_cardcount += $card->qty;
            } else {
                $this->sideboard_cards[$card->name] = $card->qty;
                $this->sideboard_cardcount += $card->qty;
            }
        }

        // Retrieve event
        $sql = '
            SELECT
                e.name, e.id
            FROM
                events e, entries n, decks d
            WHERE
                d.id = :id
                AND d.id = n.deck
                AND n.event_id = e.id';
        $event = DB::selectOnlyOrNull($sql, DeckEventDto::class, ['id' => $id]);
        if ($event) {
            $this->eventname = $event->name;
            $this->event_id = $event->id;
        }

        // Retrieve format - LI: added subeventid holder
        // The entire constructor does not run when a new deck is created, so this has to be duplicated
        // later in the save() function
        //     l
        // Find subevent id     - ignores sub-subevents like finals, which have the same name but different subevent id
        if (!is_null($this->eventname)) {
            $sql = '
                SELECT
                    events.format
                FROM
                    entries
                INNER JOIN
                    events ON entries.event_id = events.id
                WHERE
                    entries.deck = :id';
            $this->format = DB::optionalString($sql, ['id' => $id]);
            $sql = 'SELECT MIN(id) FROM subevents WHERE parent = :eventname';
            $this->subeventid = DB::optionalInt($sql, ['eventname' => $this->eventname]);
        } else {
            $this->format = '';
            $this->subeventid = null;
        }

        // Retrieve medal
        $sql = 'SELECT n.medal FROM entries n WHERE n.deck = :id';
        $this->medal = DB::optionalString($sql, ['id' => $id]) ?? 'dot';

        // Retrieve errors
        $sql = 'SELECT error FROM deckerrors WHERE deck = :id';
        $this->errors = DB::strings($sql, ['id' => $id]);

        // if there isnt a color string get one, must execute
        // after card retrival
        if (empty($this->deck_color_str)) {
            $this->getDeckColors();
        }
    }

    /** @return list<string> */
    public static function getArchetypes(): array
    {
        return DB::strings('SELECT name FROM archetypes WHERE priority > 0 ORDER BY priority DESC, name');
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
        $sql = '
            SELECT
                COALESCE(MAX(isw), 0) AS w,
                COALESCE(MAX(isu), 0) AS u,
                COALESCE(MAX(isb), 0) AS b,
                COALESCE(MAX(isr), 0) AS r,
                COALESCE(MAX(isg), 0) AS g
            FROM
                cards c, deckcontents d
            WHERE
                d.deck = :id AND c.id = d.card AND d.issideboard != 1';
        $colors = DB::selectOnly($sql, DeckColorsDto::class, ['id' => $this->id]);
        $this->deck_color_str = implode('', array_filter(['w', 'u', 'b', 'r', 'g'], fn($c) => $colors->{$c}));
    }

    /**
     * @return array<string, int>
     */
    public function getColorCounts(): array
    {
        $sql = '
            SELECT
                COALESCE(SUM(isw * d.qty), 0) AS w,
                COALESCE(SUM(isu * d.qty), 0) AS u,
                COALESCE(SUM(isb * d.qty), 0) AS b,
                COALESCE(SUM(isr * d.qty), 0) AS r,
                COALESCE(SUM(isg * d.qty), 0) AS g
            FROM
                cards c, deckcontents d
            WHERE
                d.deck = :id
                AND c.id = d.card
                AND d.issideboard != 1';
        $count = DB::selectOnly($sql, DeckColorsDto::class, ['id' => $this->id]);
        return (array) $count;
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
        $sql = 'SELECT error FROM deckerrors WHERE deck = :deck_id';
        return DB::strings($sql, ['deck_id' => $this->id]);
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
        $this->errors = [];
        // Checks to see if any matches have been played by the deck, if not deletes the deck
        if (count($this->getMatches()) == 0) {
            DB::begin('delete_deck');
            DB::execute('DELETE FROM entries WHERE deck = :deck', ['deck' => $this->id]);
            DB::execute('DELETE FROM deckerrors WHERE deck = :deck', ['deck' => $this->id]);
            DB::execute('DELETE FROM deckcontents WHERE deck = :deck', ['deck' => $this->id]);
            DB::execute('DELETE FROM decks WHERE id = :deck', ['deck' => $this->id]);
            DB::commit('delete_deck');
        }
    }

    public function save(): void
    {
        DB::begin('save_deck');
        $this->errors = [];

        $this->name = $this->name ?: 'Temp';
        if ($this->archetype != 'Unclassified' && !in_array($this->archetype, self::getArchetypes())) {
            $this->archetype = 'Unclassified';
        }

        // had to put this here since the constructor doesn't run entirely when a new deck is created
        if (!is_null($this->event_id) && is_null($this->format)) {
            $this->format = DB::string('SELECT format FROM events WHERE id = :event_id', ['event_id' => $this->event_id]);
        }
        if (!$this->format) {
            throw new InvalidArgumentException('Format is required');
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
            $sql = '
                INSERT INTO
                    decks
                    (archetype, name, playername, format, tribe, notes, created_date)
                VALUES
                    (:archetype, :name, :playername, :format, :tribe, :notes, NOW())';
            $params = [
                'archetype' => $this->archetype,
                'name' => $this->name,
                'playername' => $this->playername,
                'format' => $this->format,
                'tribe' => $this->tribe,
                'notes' => $this->notes,
            ];
            $this->id = DB::insert($sql, $params);

            $sql = 'UPDATE entries SET deck = :deck WHERE player = :player AND event_id = :event_id';
            $params = ['deck' => $this->id, 'player' => $this->playername, 'event_id' => $this->event_id];
            $affectedRows = DB::update($sql, $params);
            if ($affectedRows != 1) {
                DB::rollback('save_deck');
                throw new Exception('Entry for ' . $this->playername . ' in ' . $this->eventname . ' not found');
            }
        } else {
            $sql = 'UPDATE decks SET archetype = :archetype, name = :name, format = :format, tribe = :tribe, deck_colors = :deck_colors, notes = :notes WHERE id = :id';
            $params = [
                'archetype' => $this->archetype,
                'name' => $this->name,
                'format' => $this->format,
                'tribe' => $this->tribe,
                'deck_colors' => $this->deck_color_str,
                'notes' => $this->notes,
                'id' => $this->id,
            ];
            DB::update($sql, $params);
            $format = new Format($this->format);
        }

        $sql = 'DELETE FROM deckcontents WHERE deck = :id';
        $params = ['id' => $this->id];
        DB::execute($sql, $params);

        $sql = 'DELETE FROM deckerrors WHERE deck = :id';
        $params = ['id' => $this->id];
        DB::execute($sql, $params);

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
                $this->unparsed_cards[$card] = ($this->unparsed_cards[$card] ?? 0) + $amt;
                continue;
            }
            $card = $testcard;

            // Restricted Card list. Only one of these cards is alowed in a deck
            if ($format->isCardOnRestrictedList($card) && $amt > 1) {
                $this->errors[] = "Maindeck card: {$amt} {$card} is on the restricted list.
                             Only one of this card may be in a deck list.";
                $this->unparsed_cards[$card] = ($this->unparsed_cards[$card] ?? 0) + $amt;
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
                $this->unparsed_cards[$card] = ($this->unparsed_cards[$card] ?? 0) + $amt;
                continue;
            }
            if (count($legalCards)) {
                if (!$format->isCardOnLegalList($card)) {
                    $this->errors[] = "Maindeck card: {$amt} {$card} is not on the legal card list";
                    $this->unparsed_cards[$card] = ($this->unparsed_cards[$card] ?? 0) + $amt;
                    continue;
                }
            }
            if (!$format->isCardLegalByRarity($card)) {
                $this->errors[] = "Maindeck card : {$amt} {$card} is illegal by rarity.";
                $this->unparsed_cards[$card] = ($this->unparsed_cards[$card] ?? 0) + $amt;
                continue;
            }
            $this->maindeck_cardcount += $amt;
            $sql = 'INSERT INTO deckcontents (deck, card, issideboard, qty) values(:deck, :card, 0, :qty)';
            $params = ['deck' => $this->id, 'card' => $cardar['id'], 'qty' => $amt];
            DB::execute($sql, $params);
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
                $this->unparsed_side[$card] = ($this->unparsed_side[$card] ?? 0) + $amt;
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
                    $this->unparsed_side[$card] = ($this->unparsed_side[$card] ?? 0) + $amt;
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
                $this->unparsed_side[$card] = ($this->unparsed_side[$card] ?? 0) + $amt;
                continue;
            }
            if (count($legalCards)) {
                if (!$format->isCardOnLegalList($card)) {
                    $this->errors[] = "Sideboard card: {$amt} {$card} is not on the legal card list";
                    $this->unparsed_side[$card] = ($this->unparsed_side[$card] ?? 0) + $amt;
                    continue;
                }
            }
            if (!$format->isCardLegalByRarity($card)) {
                $this->errors[] = "Sideboard card : {$amt} {$card} is illegal by rarity.";
                $this->unparsed_side[$card] = ($this->unparsed_side[$card] ?? 0) + $amt;
                continue;
            }

            $this->sideboard_cardcount += $amt;
            $sql = 'INSERT INTO deckcontents (deck, card, issideboard, qty) values(:deck, :card, 1, :qty)';
            $params = ['deck' => $this->id, 'card' => $cardar['id'], 'qty' => $amt];
            DB::execute($sql, $params);
            $newsideboard[$cardar['name']] = $amt;
        }

        $this->sideboard_cards = $newsideboard;

        // needs to be after card parsing so it can work with new decks.

        $this->getDeckColors(); // gets the deck colors

        $sql = 'UPDATE decks SET notes = :notes, deck_colors = :deck_colors WHERE id = :id';
        $params = ['notes' => $this->notes, 'deck_colors' => $this->deck_color_str, 'id' => $this->id];
        DB::update($sql, $params);

        $this->deck_contents_cache = implode('|', array_merge(
            array_keys($this->maindeck_cards),
            array_keys($this->sideboard_cards)
        ));

        $sql = 'UPDATE decks set deck_contents_cache = :deck_contents_cache WHERE id = :id';
        $params = ['deck_contents_cache' => $this->deck_contents_cache, 'id' => $this->id];
        DB::execute($sql, $params);

        DB::commit('save_deck');
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
            $sql = 'INSERT INTO deckerrors (deck, error) VALUES (:deck, :error)';
            $params = ['deck' => $this->id, 'error' => $error];
            DB::execute($sql, $params);
        }

        // Autonamer Function
        if ($this->name == 'Temp') {
            if ($format->tribal) {
                $this->name = strtoupper($this->deck_color_str) . ' ' . $this->tribe;
            } else {
                $this->name = strtoupper($this->deck_color_str) . ' ' . $this->archetype;
            }
            $sql = 'UPDATE decks set name = :name WHERE id = :id';
            $params = ['name' => $this->name, 'id' => $this->id];
            DB::execute($sql, $params);
        }
    }

    public function getTribe(): ?string
    {
        return DB::string('SELECT tribe FROM decks WHERE id = :id', ['id' => $this->id]);
    }

    /**
     * @return list<self>
     */
    public function findIdenticalDecks(): array
    {
        if (!isset($this->identical_decks)) {
            $this->identical_decks = $this->findIdenticalDecksInternal();
        }
        return $this->identical_decks;
    }

    /** @return list<self> */
    private function findIdenticalDecksInternal(): array
    {
        $sql = '
            SELECT
                d.id
            FROM
                decks d, entries n, events e
            WHERE
                deck_hash = :deck_hash AND d.id != :id AND n.deck = d.id AND e.id = n.event_id AND e.finalized = 1
            ORDER BY
                e.start DESC';
        $params = ['deck_hash' => $this->deck_hash, 'id' => $this->id];
        $deckIds = DB::ints($sql, $params);
        $decks = [];
        foreach ($deckIds as $deckId) {
            $deck = new self($deckId);
            if (isset($deck->playername)) {
                $decks[] = $deck;
            }
        }
        return $decks;
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
        $sql = '
            UPDATE
                decks
            SET
                sideboard_hash = :sideboard_hash,
                deck_hash = :deck_hash,
                whole_hash = :whole_hash
            WHERE id = :id';
        DB::execute($sql, [
            'sideboard_hash' => $this->sideboard_hash,
            'deck_hash' => $this->deck_hash,
            'whole_hash' => $this->whole_hash,
            'id' => $this->id,
        ]);
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
