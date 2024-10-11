<?php

declare(strict_types=1);

namespace Gatherling\Models;

class Decksearch
{
    /**
     * @var list<string>
     */
    public array $errors = [];

    /**
     * @var array<string, list<int>>
     */
    private $results = [];

    /**
     * @var list<int>
     */
    private array $finalResults = [];

    private ?string $eventName;
    private ?string $playerName;

    // holds the current deck id that info is being collected for

    /**
     * Deck Search, Gatherling Deck Search Class.
     *
     * This class allows you to create a deck search query using a varing
     * amout of inputs
     *
     * example usage:
     *
     * <code>
     * $decksearch = new Decksearch();
     * $decksearch->searchByColor($color_array);
     * $decksearch->searchByFormat($formatname);
     * $results = $decksearch->getFinalResults();
     * </code>
     *
     * will return an array of deck id's matching the set search terms
     *
     *
     *
     * @version 1.0
     *
     * @category Deck
     */
    public function __construct()
    {
    }

    /**
     * call getFinalResults to complete a search request with any set inputs
     * and returns a list of deck id's that match.
     *
     * @return bool|list<int> List of id's that match the search request
     */
    public function getFinalResults(): bool|array
    {
        if (count($this->results) > 0 && count($this->errors) == 0) {
            $array_keys = array_keys($this->results);
            $first_key = array_shift($array_keys);
            $tmp_results = $this->results[$first_key];
            foreach ($this->results as $key => $value) {
                $tmp_results = array_intersect($tmp_results, $this->results[$key]);
            }
            // check if there was matches, if not set error and return
            if (count($tmp_results) != 0) {
                // filter decks in events that are current active
                // Only decks that has a field in entries will be filtered
                // will allow for creation and searching of decks without entries
                foreach ($tmp_results as $value) {
                    // check if there is a record in entries
                    $sql = 'select Count(*) FROM entries where deck = ?';
                    $result = Database::singleResultSingleParam($sql, 'd', $value);
                    if ($result) {
                        $sql = 'SELECT d.id FROM decks d, entries n, events e WHERE d.id = ? AND d.id = n.deck AND n.event_id = e.id AND e.finalized = 1';
                        $arr_tmp = Database::singleResultSingleParam($sql, 'd', $value);
                        if (!empty($arr_tmp)) {
                            array_push($this->finalResults, $arr_tmp);
                        }
                    } else {
                        array_push($this->finalResults, $value);
                    }
                }

                return $this->finalResults;
            } else {
                $this->errors[] = '<center><br>Your search query did not have any matches';

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Add search by format to the search query and sets $_results array with matching deck ids.
     *
     * @param string $format Format to search decks by
     */
    public function searchByFormat(string $format): void
    {
        $sql = 'SELECT id FROM decks WHERE format = ?';
        $results = Database::listResultSingleParam($sql, 's', $format);
        if (count($results) > 0) {
            $this->results['format'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks match the format: $format";
        }
    }

    /**
     * Add search by players to the search query sets $_results array.
     *
     * @param string $player Player name to search decks by
     */
    public function searchByPlayer(string $player): void
    {
        $sql = 'SELECT id FROM decks WHERE playername LIKE ?';
        $results = Database::listResultSingleParam($sql, 's', '%'.$player.'%');
        if (count($results) > 0) {
            $this->results['player'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks by the player like: <font color=red>$player</font></center>";
        }
    }

    /**
     * Add search by medals to the search query and sets $_results array with matching deck ids.
     *
     * Input options: 1st 2nd t4 t8
     *
     * @param string $medal Medal to search decks by
     */
    public function searchByMedals(string $medal): void
    {
        $sql = 'SELECT decks.id
        FROM decks INNER JOIN entries
        ON decks.id = entries.deck
        WHERE entries.medal = ?
        ORDER BY DATE(`created_date`) DESC';

        $results = Database::listResultSingleParam($sql, 's', $medal);
        if (count($results) > 0) {
            $this->results['medal'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks found with the medal: <font color=red>$medal</font></center>";
        }
    }

    /**
     *  Add search by colors to the search query sets $_results array with matching deck ids.
     *
     *  bcgruw u=Blue w=White b=Black r=Red g=Green c=Colorless
     *  e.g. array(u => 'u') order does not matter.
     *
     * @param array<string, string> $color_str_input
     */
    public function searchByColor(array $color_str_input): void
    {
        // alphebetizes then sets the search string
        $final_color_str = null;
        ksort($color_str_input);
        foreach ($color_str_input as $value) {
            $final_color_str .= $value;
        }

        $sql = 'SELECT id FROM decks WHERE deck_colors = ?';
        $results = Database::listResultSingleParam($sql, 's', $final_color_str);
        if (count($results) > 0) {
            $this->results['color'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks found matching the colors: <font color=red>$final_color_str</font></center>";
        }
    }

    /**
     *  Add search by archetype to the search query and sets $_results array with matching deck ids.
     *
     * @param string $archetype Name of archetype to search for
     */
    public function searchByArchetype(string $archetype): void
    {
        $sql = 'SELECT id FROM decks WHERE archetype = ?';
        $results = Database::listResultSingleParam($sql, 's', $archetype);
        if (count($results) > 0) {
            $this->results['archetype'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks found matching archetype: <font color=red>$archetype</font></center>";
        }
    }

    /**
     * Add search by series to the search query sets $_results array with matching deck ids.
     *
     * @param string $series Series name to search by
     */
    public function searchBySeries(string $series): void
    {
        $sql = 'SELECT entries.deck
        FROM entries INNER JOIN events
        ON entries.event_id = events.id
        WHERE events.series = ?
        AND entries.deck ORDER BY DATE(`registered_at`) DESC';

        $results = Database::listResultSingleParam($sql, 's', $series);
        if (count($results) > 0) {
            $this->results['series'] = $results;
        } else {
            $this->errors[] = "<center><br>No decks found matching series: <font color=red>$series</font></center>";
        }
    }

    /**
     *  Add search by card name to the search query and sets $_results array with matching deck ids.
     *
     * @param string $cardname Name of card to search for
     */
    public function searchByCardName(string $cardname): void
    {
        if (strlen($cardname) >= 3) {
            $sql = 'SELECT deckcontents.deck
            FROM deckcontents INNER JOIN cards
            on deckcontents.card = cards.id
            WHERE cards.name LIKE ?';
            $results = Database::listResultSingleParam($sql, 's', "%$cardname%");
            if (count($results) > 0) {
                //Remove Duplicate decks
                $results = array_unique($results);
                $this->results['cardname'] = $results;
            } else {
                $this->errors[] = "<center><br>No decks found with the card name like: <font color=red>$cardname</font></center>";
            }
        } else {
            $this->errors[] = '<center><br>String length is too short must be <font color=red>3</font> characters or greater</center>';
        }
    }

    /**
     * @param list<int> $id_arr
     * @return list<array{id: int, archetype: string, name: string, playername: string, format: string, created_date: string, record: string}>
     */
    public function idsToSortedInfo(array $id_arr): array
    {
        $db = Database::getConnection();

        //sanitize the id_arr to protect against sql injection.
        $id_arr = array_filter(array_map('intval', $id_arr));

        $query = 'SELECT id, archetype, name, playername, format, created_date from decks WHERE id IN ('.implode(',', $id_arr).') ORDER BY DATE(`created_date`) DESC';
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stmt->bind_result($id, $archetype, $name, $playername, $format, $created_date);
        $info = [];
        while ($stmt->fetch()) {
            $info[] = [
                'id'           => $id,
                'archetype'    => $archetype,
                'name'         => $name,
                'playername'   => $playername,
                'format'       => $format,
                'created_date' => $created_date,
            ];
        }
        $stmt->close();

        $list = [];
        foreach ($info as $row) {
            $row['record'] = $this->getDeckRecord($row['id']);
            $list[] = $row;
        }

        return $list;
    }

    private function getDeckRecord(int $deckid): string
    {
        // check if there is a record in entries
        $sql = 'select Count(*) FROM entries where deck = ?';
        $result = Database::singleResultSingleParam($sql, 'd', $deckid);

        if ($result) {
            $database = Database::getConnection();
            $stmt = $database->prepare('SELECT e.name, d.playername FROM decks d, entries n, events e WHERE d.id = ? AND d.id = n.deck AND n.event_id = e.id');
            $stmt || exit($database->error);
            $stmt->bind_param('d', $deckid);
            $stmt->execute();
            $stmt->bind_result($this->eventName, $this->playerName);
            $stmt->fetch();
            $stmt->close();

            if (!empty($this->eventName) && !empty($this->playerName)) {
                return $this->recordString();
            } else {
                return '?-?';
            }
        } else {
            return '?-?';
        }
    }

    private function recordString(): string
    {
        $wins = 0;
        $losses = 0;
        $draws = 0;

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id FROM matches m, subevents s WHERE m.subevent = s.id AND s.parent = ?
        AND (m.playera = ? OR m.playerb = ?) ORDER BY s.timing, m.round');
        $stmt->bind_param('sss', $this->eventName, $this->playerName, $this->playerName);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $matchids = [];
        while ($stmt->fetch()) {
            $matchids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($matchids as $matchid) {
            $matches[] = new Matchup($matchid);
        }

        foreach ($matches as $match) {
            if ($match->playerWon($this->playerName)) {
                $wins = $wins + 1;
            } elseif ($match->playerLost($this->playerName)) {
                $losses = $losses + 1;
            } else {
                $draws = $draws + 1;
            }
        }

        if ($draws == 0) {
            return $wins.'-'.$losses;
        } else {
            return $wins.'-'.$losses.'-'.$draws;
        }
    }
}
