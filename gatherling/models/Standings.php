<?php

namespace Gatherling;

class Standings
{
    public $id;
    public $event;  // belongs_to event
    public $player; // belongs_to player
    public $active;
    public $score;
    public $matches_played;
    public $matches_won;
    public $draws;
    public $games_won;
    public $games_played;
    public $byes;
    public $OP_Match;
    public $PL_Game;
    public $OP_Game;
    public $seed;
    public $matched;
    public $new;

    public function __construct($eventname, $playername)
    {
        // Check to see if we are doing event standings of player standings
        if ($playername == '0') {
            $this->id = 0;
            $this->new = true;

            return;
        } else {
            //echo "past loop";
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT active, matches_played, games_won, games_played, byes, OP_Match, PL_Game, OP_Game, score, seed, matched, matches_won, draws FROM standings WHERE event = ? AND player = ? limit 1');
            $stmt or exit($db->error);
            $stmt->bind_param('ss', $eventname, $playername);
            $stmt->execute();
            $stmt->bind_result($this->active, $this->matches_played, $this->games_won, $this->games_played, $this->byes, $this->OP_Match, $this->PL_Game, $this->OP_Game, $this->score, $this->seed, $this->matched, $this->matches_won, $this->draws);
            $this->player = $playername;
            $this->event = $eventname;
            if ($stmt->fetch() == null) { // No entry in standings table,
                $this->new = true;
            }
            $stmt->close();
        }
    }

    public function save()
    {
        $db = Database::getConnection();
        if (!is_null($this->player) && trim($this->player) != ''
            && !is_null($this->event) && trim($this->event) != '') {
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
                $this->seed = 127;
                $this->matched = 0;
                $this->draws = 0;

                $stmt = $db->prepare('INSERT INTO standings(player, event, active,
            matches_played, draws, games_won, games_played, byes, OP_Match, PL_Game,
            OP_Game, score, seed, matched)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssdddddddddddd', $this->player, $this->event, $this->active, $this->matches_played, $this->draws, $this->games_won, $this->games_played, $this->byes, $this->OP_Match, $this->PL_Game, $this->OP_Game, $this->score, $this->seed, $this->matched);
                $stmt->execute() or exit($stmt->error);
                $stmt->close();
            } else {
                $stmt = $db->prepare('UPDATE standings SET
                                    player = ?, event = ?, active = ?, matches_played = ?, games_won = ?,
                                    games_played = ?, byes = ?, OP_Match = ?, PL_Game = ?, OP_Game = ?,
                                    score = ?, seed = ?, matched = ?, matches_won = ?, draws = ? WHERE player = ? AND event = ?');
                $stmt or exit($db->error);
                $stmt->bind_param('ssdddddddddddddss', $this->player, $this->event, $this->active, $this->matches_played, $this->games_won, $this->games_played, $this->byes, $this->OP_Match, $this->PL_Game, $this->OP_Game, $this->score, $this->seed, $this->matched, $this->matches_won, $this->draws, $this->player, $this->event);
                $stmt->execute() or exit($stmt->error);
                $stmt->close();
            }
        }
    }

    /**
     * @param string $eventname
     * @param int    $isactive
     *
     * @return Standings[]
     */
    public static function getEventStandings($eventname, $isactive)
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

    public static function printEventStandings($eventname, $playername = null)
    {
        $event = new Event($eventname);
        $standings = self::getEventStandings($eventname, 0);
        echo '<p />';
        echo '<table style="text-align:center;">';
        echo '<th colspan="8"><center><h3>Current Standings</h3></th>';
        echo "<tr><td colspan=\"8\"><h6> {$eventname}</h6></td></tr>";
        $rank = 1;
        echo '<tr>
              <td>Rank</td>
              <td>Player</td>
              <td>Match Points</td>
              <td>OMW %</td>
              <td>PGW %</td>
              <td>OGW %</td>
              <td>Matches Played</td>
              <td>Byes</td>
              </tr>';

        foreach ($standings as $player_standing) {
            $color_code = '';
            if ($player_standing->player == $playername) {
                $color_code = ' style="color:green" ';
            }
            $match_score = $player_standing->score;
            $sp = new Player($player_standing->player);
            $name = $sp->gameName($event->client);
            echo "<tr>
                  <td{$color_code}>{$rank}</td>
                  <td{$color_code}>{$name}</td>
                  <td{$color_code}>{$match_score}</td>
                  <td{$color_code}>{$player_standing->OP_Match}</td>
                  <td{$color_code}>{$player_standing->PL_Game}</td>
                  <td{$color_code}>{$player_standing->OP_Game}</td>
                  <td{$color_code}>{$player_standing->matches_played}</td>
                  <td{$color_code}>{$player_standing->byes}</td>
                  </tr>";
            $rank++;
        }
        echo '<tr><td colspan="8"><br/><b> Tiebreakers Explained </b><p/></td></tr>';
        echo '<tr><td colspan="8"> Players with the same number of match points are ranked based on three tiebreakers scores according to DCI rules. In order, they are: </td></tr>';
        echo '<tr><td colspan="8"> OMW % is the average percentage of matches your opponents have won. </td></tr>';
        echo '<tr><td colspan="8"> PGW % is the percentage of games you have won. </td></tr>';
        echo '<tr><td colspan="8"> OGW % is the average percentage of games your opponents have won. </td></tr>';
        echo '<tr><td colspan="8"> BYEs are not included when calculating standings. For example, a player with one BYE, one win, and one loss has a match win percentage of .50 rather than .66</td></tr>';
        echo '<tr><td colspan="8"> When calculating standings, any opponent with less than a .33 win percentage is calculated as .33</td></tr>';
        echo '</table></center>';
    }

    public static function updateStandings($eventname, $subevent, $round)
    {
        $players = self::getEventStandings($eventname, 0);
        foreach ($players as $player) {
            $player->calculateStandings($eventname, $subevent, $round);
        }
    }

    public function calculateStandings($eventname, $subevent, $round)
    {
        $players = self::getEventStandings($eventname, 0);
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

    public function getOpponents($eventname, $subevent, $round)
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

    /**
     * @param int $subevent
     * @param int $round
     *
     * @return void|array
     */
    public function League_getAvailable_Opponents($subevent, $round)
    {
        $opponentsAlreadyFaced = [];
        $allPlayers = [];
        $opponent_names = [];

        if ($round == '0') {
            return;
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
        $structure = Database::single_result_single_param('SELECT `type` FROM subevents WHERE id = ?', 'd', $subevent);
        if ($structure == 'League Match' && count($opponentsAlreadyFaced) >= 1) {
            return [];
        }
        if (count($opponentsAlreadyFaced) >= 5) {
            return [];
        }
        // Get all opponents who haven't dropped from event and exclude myself
        $allPlayers = Database::list_result_double_param('SELECT player
                                                          FROM standings
                                                          WHERE event = ?
                                                          AND active = 1
                                                          AND player <> ?', 'ss', $this->event, $this->player);

        // prune all opponents by opponents I have already played
        $opponent_names = array_diff($allPlayers, $opponentsAlreadyFaced);

        return $opponent_names;
    }

    public static function startEvent($entries, $event_name)
    {
        foreach ($entries as $entry) {
            $standing = new self($event_name, $entry->player->name);
            $standing->save();
        }
    }

    public static function addPlayerToEvent($event_name, $entry)
    {
        $standing = new self($event_name, $entry);
        $standing->save();
    }

    public static function dropPlayer($eventname, $playername)
    {
        $standing = new self($eventname, $playername);
        $standing->active = 0;
        $standing->save();
    }

    public static function playerActive($eventname, $playername)
    {
        $standing = new self($eventname, $playername);

        return $standing->active == 1;
    }

    public static function writeSeed($eventname, $playername, $seed)
    {
        $standing = new self($eventname, $playername);
        $standing->seed = $seed;
        $standing->save();
    }

    public static function resetMatched($eventname)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE standings SET matched = 0 WHERE event = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('s', $eventname);
        $stmt->execute() or exit($stmt->error);
        $stmt->close();
    }
}
