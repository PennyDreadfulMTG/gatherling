<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Gatherling\Data\DB;
use InvalidArgumentException;

class Standings
{
    public int $id;
    public ?string $event = null;  // belongs_to event
    public ?string $player = null; // belongs_to player
    public ?int $active;
    public ?int $score;
    public ?int $matches_played;
    public ?int $matches_won;
    public ?int $draws;
    public ?int $games_won;
    public ?int $games_played;
    public ?int $byes;
    public ?float $OP_Match;
    public ?float $PL_Game;
    public ?float $OP_Game;
    public ?int $seed;
    public ?int $matched;
    public ?bool $new = null;

    public function __construct(string $eventname, string $playername, int $initial_seed = 127)
    {
        // Check to see if we are doing event standings of player standings
        if ($playername == '0') {
            $this->id = 0;
            $this->new = true;

            return;
        } else {
            $sql = '
                SELECT
                    active, matches_played, games_won, games_played, byes, OP_Match, PL_Game, OP_Game,
                    score, seed, matched, matches_won, draws
                FROM
                    standings
                WHERE
                    event = :event AND player = :player
                LIMIT 1';
            $params = ['event' => $eventname, 'player' => $playername];
            $standings = DB::selectOnlyOrNull($sql, StandingsDto::class, $params);
            $this->player = $playername;
            $this->event = $eventname;
            if (!$standings) {
                $this->new = true;
                $this->seed = $initial_seed;
                return;
            }
            foreach (get_object_vars($standings) as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function save(): void
    {
        if (
            !is_null($this->player) && trim($this->player) != ''
            && !is_null($this->event) && trim($this->event) != ''
        ) {
            if ($this->new == true) {
                $this->active = 1;
                $this->score = 0;
                $this->matches_played = 0;
                $this->matches_won = 0;
                $this->games_won = 0;
                $this->games_played = 0;
                $this->byes = 0;
                $this->OP_Match = 0;
                $this->PL_Game = 0;
                $this->OP_Game = 0;
                $this->matched = 0;
                $this->draws = 0;

                $sql = '
                    INSERT INTO
                        standings
                        (player, event, active, matches_played, draws, games_won, games_played, matches_won, byes, OP_Match, PL_Game,
                        OP_Game, score, seed, matched)
                    VALUES
                        (:player, :event, :active, :matches_played, :draws, :games_won, :games_played, :matches_won, :byes, :OP_Match, :PL_Game,
                        :OP_Game, :score, :seed, :matched)';
                $params = [
                    'player' => $this->player,
                    'event' => $this->event,
                    'active' => $this->active,
                    'matches_played' => $this->matches_played,
                    'draws' => $this->draws,
                    'games_won' => $this->games_won,
                    'games_played' => $this->games_played,
                    'matches_won' => $this->matches_won,
                    'byes' => $this->byes,
                    'OP_Match' => $this->OP_Match,
                    'PL_Game' => $this->PL_Game,
                    'OP_Game' => $this->OP_Game,
                    'score' => $this->score,
                    'seed' => $this->seed,
                    'matched' => $this->matched,
                ];
                DB::execute($sql, $params);
            } else {
                $sql = '
                    UPDATE
                        standings
                    SET
                        player = :player, event = :event, active = :active, matches_played = :matches_played,
                        games_won = :games_won, games_played = :games_played, byes = :byes, OP_Match = :OP_Match,
                        PL_Game = :PL_Game, OP_Game = :OP_Game, score = :score, seed = :seed, matched = :matched,
                        matches_won = :matches_won, draws = :draws
                    WHERE
                        player = :player AND event = :event';
                $params = [
                    'player' => $this->player,
                    'event' => $this->event,
                    'active' => $this->active,
                    'matches_played' => $this->matches_played,
                    'games_won' => $this->games_won,
                    'games_played' => $this->games_played,
                    'byes' => $this->byes,
                    'OP_Match' => $this->OP_Match,
                    'PL_Game' => $this->PL_Game,
                    'OP_Game' => $this->OP_Game,
                    'score' => $this->score,
                    'seed' => $this->seed,
                    'matched' => $this->matched,
                    'matches_won' => $this->matches_won,
                    'draws' => $this->draws,
                ];
                DB::execute($sql, $params);
            }
        }
    }

    /** @return list<Standings> */
    public static function getEventStandings(string $eventname, int $isactive): array
    {
        $db = Database::getConnection();

        // Ordered by rand() is bad for speed and scalability reasons, but since this function
        // only runs between rounds, it probably doesn't matter.
        if ($isactive == 0) {
            $stmt = $db->prepare('SELECT player FROM standings WHERE event = ? ORDER BY score desc, OP_Match desc, PL_Game desc, OP_Game desc');
        } elseif ($isactive == 1) {
            $stmt = $db->prepare('SELECT player FROM standings WHERE event = ? AND active = 1 and matched = 0 ORDER BY score desc, byes desc, RAND() LIMIT 1');
        } elseif ($isactive == 2) {
            $stmt = $db->prepare('SELECT player FROM standings WHERE event = ? AND active = 1 ORDER BY seed');
        } elseif ($isactive == 3) {
            $stmt = $db->prepare('SELECT player FROM standings WHERE event = ? AND active = 1 ORDER BY score desc, OP_Match desc, PL_Game desc, OP_Game desc');
        } else {
            throw new InvalidArgumentException("Invalid argument for isactive {$isactive}");
        }
        $stmt or exit($db->error);
        $stmt->bind_param('s', $eventname);
        $stmt->execute();
        $stmt->bind_result($name);
        $playernames = [];
        while ($stmt->fetch()) {
            $playernames[] = $name;
        }
        $stmt->close();
        $event_standings = [];
        foreach ($playernames as $playername) {
            $event_standings[] = new self($eventname, $playername);
        }

        return $event_standings;
    }

    public static function updateStandings(string $eventname, int $subevent, int $round): void
    {
        $players = self::getEventStandings($eventname, 0);
        foreach ($players as $player) {
            $player->calculateStandings($eventname, $subevent, $round);
        }
    }

    public function calculateStandings(string $eventname, int $subevent, int $round): void
    {
        $opponents = $this->getOpponents($eventname, $subevent, $round);
        $OMW = 0;
        $OGW = 0;
        $number_of_opponents = 0;
        foreach ($opponents as $opponent) {
            // do calc
            if ($opponent->byes > 0) {
                if ($opponent->matches_played != 0) {
                    $opp_score = ($opponent->matches_won - $opponent->byes) + ($opponent->draws * .333);
                    $opp_win_percentage = ($opp_score / $opponent->matches_played);
                    if ($opp_win_percentage < .33) {
                        $opp_win_percentage = .33;
                    }
                    $OMW += $opp_win_percentage;
                } else {
                    $OMW += .33;
                }
                if ($opponent->games_played != 0) {
                    $OGW += ($opponent->games_won / $opponent->games_played);
                }
                $number_of_opponents++;
            } else {
                if ($opponent->matches_played != 0) {
                    $opp_win_percentage = (($opponent->matches_won + ($opponent->draws * .333)) / $opponent->matches_played);
                    if ($opp_win_percentage < .33) {
                        $opp_win_percentage = .33;
                    }
                    $OMW += $opp_win_percentage;
                } else {
                    $OMW += .33;
                }
                if ($opponent->games_played != 0) {
                    $OGW += ($opponent->games_won / $opponent->games_played);
                }
                $number_of_opponents++;
            }
        }
        if ($this->games_played == 0) {
            $default = 0;
            if ($this->score > 0) { //The player has a bye on their first round
                $default = 0.33;
            }
            $this->OP_Match = $default;
            $this->OP_Game = $default;
            $this->PL_Game = $default;
        } else {
            $this->OP_Match = ($OMW / $number_of_opponents);
            $this->OP_Game = ($OGW / $number_of_opponents);
            $this->PL_Game = ($this->games_won / $this->games_played);
        }

        $this->save();
    }

    /** @return list<Standings> */
    public function getOpponents(string $eventname, int $subevent, int $round): array
    {
        if ($round == '0') {
            return [];
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT playera, playerb FROM matches where subevent = ? AND result <> 'P' AND (playera = ? OR playerb = ?)");
        $stmt->bind_param('dss', $subevent, $this->player, $this->player);

        $stmt->execute();
        $stmt->bind_result($playera, $playerb);
        $playernames = [];
        while ($stmt->fetch()) {
            if ($playera == $this->player) {
                $opponent_name = $playerb;
            } else {
                $opponent_name = $playera;
            }
            if ($opponent_name != $this->player) {
                $playernames[] = $opponent_name;
            }
        }

        $stmt->close();
        $opponents = [];
        foreach ($playernames as $playername) {
            $opponents[] = new self($eventname, $playername);
        }

        return $opponents;
    }

    /** @return list<string> */
    public function getAvailableLeagueOpponents(int $subevent, int $round, int $league_length): array
    {
        $opponentsAlreadyFaced = [];
        $allPlayers = [];
        $opponent_names = [];

        if ($round == '0') {
            return [];
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT playera, playerb FROM matches where subevent = ? AND (playera = ? OR playerb = ?) AND round = ?');
            $stmt->bind_param('dssd', $subevent, $this->player, $this->player, $round);

            //Find existing opponents
            $stmt->execute();
            $stmt->bind_result($playera, $playerb);

            while ($stmt->fetch()) {
                if ($playera == $this->player) {
                    $opponent_name = $playerb;
                } else {
                    $opponent_name = $playera;
                }
                if ($opponent_name != $this->player) {
                    $opponentsAlreadyFaced[] = $opponent_name;
                }
            }
            $stmt->close();
        }
        $structure = Database::singleResultSingleParam('SELECT `type` FROM subevents WHERE id = ?', 'd', $subevent);
        if ($structure == 'League Match' && count($opponentsAlreadyFaced) >= 1) {
            return [];
        }
        if (count($opponentsAlreadyFaced) >= $league_length) {
            return [];
        }
        // Get all opponents who haven't dropped from event and exclude myself
        $allPlayers = Database::listResultDoubleParam('SELECT player
                                                          FROM standings
                                                          WHERE event = ?
                                                          AND active = 1
                                                          AND player <> ?
                                                          ORDER BY player', 'ss', $this->event, $this->player);

        // prune all opponents by opponents I have already played
        $opponent_names = array_diff($allPlayers, $opponentsAlreadyFaced);

        return $opponent_names;
    }

    /** @param list<Entry> $entries */
    public static function startEvent(array $entries, string $event_name): void
    {
        foreach ($entries as $entry) {
            $standing = new self($event_name, $entry->player->name, $entry->initial_seed);
            $standing->save();
        }
    }

    public static function addPlayerToEvent(string $event_name, string $entry): void
    {
        $standing = new self($event_name, $entry);
        $standing->save();
    }

    public static function dropPlayer(string $eventname, string $playername): void
    {
        $standing = new self($eventname, $playername);
        $standing->active = 0;
        $standing->save();
    }

    public static function playerActive(string $eventname, string $playername): bool
    {
        $standing = new self($eventname, $playername);

        return $standing->active == 1;
    }

    public static function writeSeed(string $eventname, string $playername, int $seed): void
    {
        $standing = new self($eventname, $playername);
        $standing->seed = $seed;
        $standing->save();
    }

    public static function resetMatched(string $eventname): void
    {
        $sql = 'UPDATE standings SET matched = 0 WHERE event = :event';
        DB::execute($sql, ['event' => $eventname]);
    }
}
