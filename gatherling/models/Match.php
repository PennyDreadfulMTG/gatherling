<?php

class Match
{
    public $id;
    public $subevent;
    public $round;
    public $playera;
    public $playerb;
    public $result;
    // We keep both players wins and losses, so that they can independently report their scores.
    public $playera_wins;
    public $playera_losses;
    public $playera_draws;
    public $playerb_wins;
    public $playerb_losses;
    public $playerb_draws;

    // Inherited from subevent

    public $timing;
    public $type;
    public $rounds;

    // Inherited from event

    public $format;
    public $series;
    public $season;
    public $eventname;
    public $event_id;

    // added for matching

    public $verification;

    public static function destroy($matchid)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM matches WHERE id = ?');
        $stmt->bind_param('d', $matchid);
        $stmt->execute();
        $rows = $stmt->affected_rows;
        $stmt->close();

        return $rows;
    }

    public function __construct($id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.subevent, m.round, m.playera, m.playerb, m.result, m.playera_wins, m.playera_losses, m.playera_draws, m.playerb_wins, m.playerb_losses, m.playerb_draws, s.timing, s.type, s.rounds, e.format, e.series, e.season, m.verification, e.name, e.id
      FROM matches m, subevents s, events e
      WHERE m.id = ? AND m.subevent = s.id AND e.name = s.parent');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result($this->subevent, $this->round, $this->playera, $this->playerb, $this->result, $this->playera_wins, $this->playera_losses, $this->playera_draws, $this->playerb_wins, $this->playerb_losses, $this->playerb_draws, $this->timing, $this->type, $this->rounds, $this->format, $this->series, $this->season, $this->verification, $this->eventname, $this->event_id);
        $stmt->fetch();
        $stmt->close();
        $this->id = $id;
    }

    // Retuns the event that this Match is a part of.
    public function getEvent()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT s.parent
                          FROM subevents s, matches m
                          WHERE m.id = ? AND m.subevent = s.id');
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->bind_result($eventname);
        $stmt->fetch();
        $stmt->close();

        return new Event($eventname);
    }

    private function playerA($name)
    {
        return strcasecmp($this->playera, $name) == 0;
    }

    private function playerB($name)
    {
        return strcasecmp($this->playerb, $name) == 0;
    }

    private function toName($player_or_name)
    {
        if (is_object($player_or_name)) {
            return $player_or_name->name;
        }

        return $player_or_name;
    }

    public function playerLetter($player)
    {
        if ($this->playerA($player)) {
            return 'a';
        } elseif ($this->playerB($player)) {
            return 'b';
        } else {
            return;
        }
    }

    // Returns true if $player has a bye in this match
    public function playerBye($player)
    {
        if ($this->result != 'BYE') {
            return false;
        }
        $playername = $this->toName($player);

        return $this->playerA($playername) || $this->playerB($playername);
    }

    // Returns true if $player is playing this match right now.
    public function playerMatchInProgress($player)
    {
        if ($this->result != 'P') {
            return false;
        }
        $playername = $this->toName($player);

        return $this->playerA($playername) || $this->playerB($playername);
    }

    public function playerWon($player)
    {
        $playername = $this->toName($player);

        return ($this->playerA($playername) && ($this->result == 'A'))
             || ($this->playerB($playername) && ($this->result == 'B'));
    }

    public function playerLost($player)
    {
        $playername = $this->toName($player);

        return ($this->playerA($playername) && ($this->result == 'B'))
         || ($this->playerB($playername) && ($this->result == 'A'));
    }

    // returns the number of wins for the current match for $player
    // returns false if the player is not in this match.
    public function getPlayerWins($player)
    {
        $playername = $this->toName($player);

        if ($this->playerA($playername)) {
            return $this->playera_wins;
        }
        if ($this->playerB($playername)) {
            return $this->playerb_wins;
        }

        return false;
    }

    public function getPlayerResult($player)
    {
        $playername = $this->toName($player);
        if ($this->playerA($playername)) {
            if ($this->isBYE()) {
                return 'BYE';
            }
            if ($this->result == 'A') {
                return 'Won';
            }
            if ($this->result == 'B') {
                return 'Loss';
            }

            return 'Draw';
        }
        if ($this->playerB($playername)) {
            if ($this->result == 'A') {
                return 'Loss';
            }
            if ($this->result == 'B') {
                return 'Won';
            }

            return 'Draw';
        }

        throw new Exception("Player $playername is not in match {$match->id}");
    }

    public function playerDropped($player)
    {
        $playername = $this->toName($player);
        $entry = new Entry($this->event_id, $player);

        return $entry->drop_round == $this->round;
    }

    // returns the number of wins for the current match for $player
    // Returns false if the player is not in this match.
    public function getPlayerLosses($player)
    {
        $playername = $this->toName($player);

        if ($this->playerA($playername)) {
            return $this->playera_losses;
        }
        if ($this->playerB($playername)) {
            return $this->playerb_losses;
        }

        return false;
    }

    public function getWinner()
    {
        if ($this->playerWon($this->playera)) {
            return $this->playera;
        }

        if ($this->playerWon($this->playerb)) {
            return $this->playerb;
        }

        if ($this->isBYE()) {
            return 'BYE';
        }

        if ($this->matchInProgress()) {
            return 'Match in Progress';
        }

        if ($this->isDraw()) {
            return 'Draw';
        }
    }

    public function isBYE()
    {
        if ($this->result == 'BYE') {
            return true;
        }

        return false;
    }

    public function matchInProgress()
    {
        if ($this->result == 'P') {
            return true;
        }

        return false;
    }

    public function getLoser()
    {
        if ($this->playerLost($this->playera)) {
            return $this->playera;
        }
        if ($this->playerLost($this->playerb)) {
            return $this->playerb;
        }
    }

    public function otherPlayer($oneplayer)
    {
        if (strcasecmp($oneplayer, $this->playera) == 0) {
            return $this->playerb;
        } elseif (strcasecmp($oneplayer, $this->playerb) == 0) {
            return $this->playera;
        }
    }

    // Returns a count of how many matches there are total.
    public static function count()
    {
        return Database::single_result('SELECT count(id) FROM matches');
    }

    // Saves a report from a player on their match results.
    public static function saveReport($result, $match_id, $player)
    {
        if ($match_id == 0) {
        }
        $savedMatch = new self($match_id);
        if ($savedMatch->result == 'P') {
            $db = Database::getConnection();
            // Which player is reporting?
            if ($player == 'a') {
                $stmt = $db->prepare('UPDATE matches SET playera_wins = ?, playera_losses = ? WHERE id = ?');
            } else {
                $stmt = $db->prepare('UPDATE matches SET playerb_wins = ?, playerb_losses = ? WHERE id = ?');
            }
            $stmt or exit($db->error);
            // this is dumb, fix later
            // I agree it's dumb, but you can't because PDO sucks.
            $two = 2;
            $one = 1;
            $zero = 0;

            switch ($result) {
            case 'W20':
                //echo "writing a 2-0 win";
                $stmt->bind_param('ddd', $two, $zero, $match_id);
                break;
            case 'W21':
                //echo "writing a 2-1 win";
                $stmt->bind_param('ddd', $two, $one, $match_id);
                break;
            case 'L20':
                //echo "writing a 2-0 loss";
                $stmt->bind_param('ddd', $zero, $two, $match_id);
                break;
            case 'L21':
                //echo "writing a 2-1 loss";
                $stmt->bind_param('ddd', $one, $two, $match_id);
                break;
            case 'D':
                //writing a draw
                $stmt->bind_param('ddd', $one, $one, $match_id);
                break;
        }

            $stmt->execute();
            self::validateReport($match_id);

            return;
        }
    }

    public function reportSubmitted($name)
    {
        if ($this->playerA($name) && (($this->playera_wins + $this->playera_losses) > 0)) {
            return true;
        }
        if ($this->playerB($name) && (($this->playerb_wins + $this->playerb_losses) > 0)) {
            return true;
        }

        return false;
    }

    // Checks both reports against each other to see if they match.
    // Marks ones where they don't match as 'failed'
    public static function validateReport($match_id)
    {
        // get and compare reports
        //echo "in validate report".$match_id;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT subevent, playera_wins, playerb_wins, playera_losses, playerb_losses
                            FROM matches
                            WHERE id = ? ');
        $stmt->bind_param('d', $match_id);
        $stmt->execute();
        $stmt->bind_result($subevent, $playera_wins, $playerb_wins, $playera_losses, $playerb_losses);
        $stmt->fetch();
        $stmt->close();

        if (($playera_wins + $playera_losses) == 0 or ($playerb_wins + $playerb_losses) == 0) {
            //No second report, quit
            return;
        } else {
            //Compare reports
            if (($playera_wins == $playerb_losses) and ($playerb_wins == $playera_losses)) {
                //matched, set verified
                self::flagVerified($match_id);
                $event = Event::getEventBySubevent($subevent);
                $event->resolveRound($subevent, $event->current_round);
            } else {
                //failed match, flag
                self::flagFailed($match_id);
            }
        }
    }

    public static function flagVerified($match_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE matches SET verification = 'verified' WHERE id = ?");
        $stmt->bind_param('d', $match_id);
        $stmt->execute();
        $stmt->close();
    }

    public static function flagFailed($match_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE matches SET verification = 'failed' WHERE id = ?");
        $stmt->bind_param('d', $match_id);
        $stmt->execute();
        $stmt->close();
    }

    public static function unresolvedMatchesCheck($subevent_name, $current_round)
    {
        $db = @Database::getConnection();
        $stmt = $db->prepare("SELECT count(id) FROM matches where subevent = ? AND verification != 'verified' AND round = ?");
        $stmt->bind_param('sd', $subevent_name, $current_round);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    // Goes through all matches in this round and updates the "Standing" objects with new scores.
    public function updateScores($structure)
    {
        // Goes through all matches in this round and updates scores
        // TODO remove scoring from here, as it's now calculated elsewhere so much of this is redundant

        $series = new Series($this->series);
        $seasonRules = $series->getSeasonRules($this->season);
        $thisevent = Event::getEventBySubevent($this->subevent);
        $playera_standing = new Standings($thisevent->name, $this->playera);
        $playerb_standing = new Standings($thisevent->name, $this->playerb);
        if ($this->result == 'BYE') {
            $playerb_standing->score += 3;
            $playerb_standing->byes++;
            $playerb_standing->save();
        } elseif ($this->isDraw()) {
            $playerb_standing->score += 1;
            $playerb_standing->save();
            $playera_standing->score += 1;
            $playera_standing->save();
            $this->result = 'D';
        } else {
            if ($this->playera_wins > $this->playerb_wins) {
                if ($structure == 'Single Elimination') {
                    $playerb_standing->active = 0;
                } elseif (strpos($structure, 'Swiss') === 0) {
                    $playera_standing->score += 3;
                } elseif ($structure == 'League') {
                    $playera_standing->score += 3;
                    $playerb_standing->score += $seasonRules['loss_pts'];
                }
                $this->result = 'A';
            } else {
                if ($structure == 'Single Elimination') {
                    $playera_standing->active = 0;
                } elseif (strpos($structure, 'Swiss') === 0) {
                    $playerb_standing->score += 3;
                } elseif ($structure == 'League') {
                    $playerb_standing->score += 3;
                    $playera_standing->score += $seasonRules['loss_pts'];
                }
                $this->result = 'B';
            }
        }
        if (strcmp($playera_standing->player, $playerb_standing->player) == 0) {
            // Moved to above
        } else {
            if ($structure !== 'Single Elimination') {
                $playera_standing->matches_played++;
                $playera_standing->games_played += ($this->playera_wins + $this->playera_losses);
                $playera_standing->games_won += $this->playera_wins;
                //echo "****playeragameswon".$playera_standing->games_won;
                $playerb_standing->matches_played++;
                $playerb_standing->games_played += $this->playera_wins + $this->playera_losses;
                $playerb_standing->games_won += $this->playerb_wins;
            }
            $playera_standing->save();
            $playerb_standing->save();
        }
        $this->finalize_match($this->result, $this->id);
    }

    // temp, will fix later
    // Don't know what this does, but it looks a lot like the above.
    public function fixScores($structure)
    {
        // Goes through all matches in this round and updates scores

        // I am thinking about making this use the points designated by the
        // Series Organizer for the seasons
        // will need to add:

        // $series = new Series($this->series); // this gets access to the season points
        // $rules = $series->getSeasonRules($this->season_number); // retrieves the points as specified by Organizer
        // see Series line# 695
        // can then add points like this:
        // $playera_standing->score += $rules['win_pts']; // for adding win points
        // $playerb_standing->score += $rules['loss_pts']; // adds points for loss
        // for a player to loose points for a loss, Organizer would have to specify
        // a negative value, at least I think that would work.
        // use $rules['bye_pts'] for byes, since byes count as a win

        $series = new Series($this->series);
        $seasonRules = $series->getSeasonRules($this->season);
        $thisevent = Event::getEventBySubevent($this->subevent);
        $playera_standing = new Standings($thisevent->name, $this->playera);
        $playerb_standing = new Standings($thisevent->name, $this->playerb);
        if ($this->result == 'BYE') {
            $playerb_standing->score += 3;
            $playerb_standing->matches_won += 1;
            $playerb_standing->byes++;
            $playerb_standing->save();
        } elseif ($this->isDraw()) {
            $playerb_standing->score += 1;
            $playerb_standing->draws += 1;
            $playerb_standing->save();
            $playera_standing->score += 1;
            $playera_standing->draws += 1;
            $playera_standing->save();
        } else {
            if ($this->playera_wins > $this->playerb_wins) {
                $playera_standing->score += 3;
                $playera_standing->matches_won += 1;
                if ($structure == 'League') {
                    $playerb_standing->score += $seasonRules['loss_pts'];
                }
                if ($structure == 'Single Elimination') {
                    $playerb_standing->active = 0;
                }
                $this->result = 'A';
            } else {
                $playerb_standing->score += 3;
                $playerb_standing->matches_won += 1;
                if ($structure == 'League') {
                    $playera_standing->score += $seasonRules['loss_pts'];
                }
                if ($structure == 'Single Elimination') {
                    $playera_standing->active = 0;
                }
                $this->result = 'B';
            }
        }
        if (strcmp($playera_standing->player, $playerb_standing->player) == 0) {
            //Might need this later if I want to rebuild bye score with standings $playera_standing->byes++;
        } else {
            $playera_standing->matches_played++;
            $playera_standing->games_played += ($this->playera_wins + $this->playera_losses);
            $playera_standing->games_won += $this->playera_wins;
            $playerb_standing->matches_played++;
            $playerb_standing->games_played += $this->playera_wins + $this->playera_losses;
            $playerb_standing->games_won += $this->playerb_wins;
        }
        $playera_standing->save();
        $playerb_standing->save();
    }

    public function finalize_match($winner, $match_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE matches SET result = ? WHERE id = ?');
        $stmt->bind_param('sd', $winner, $match_id);
        $stmt->execute();
        $stmt->close();
    }

    public function player_reportable_check()
    {
        $event = new Event($this->getEventNamebyMatchid());
        if ($event->player_reportable == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getEventNamebyMatchid()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name
                              FROM matches m, subevents s, events e
                              WHERE m.id = ?
                              AND m.subevent = s.id
                              AND e.name = s.parent');

        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();

        return $name;
    }

    public function isDraw()
    {
        return $this->playera_wins == $this->playerb_wins;
    }

    public function isReportable()
    {
        $event = $this->getEvent();

        return $event->player_reportable == 1;
    }

    public function allowsPlayerReportedDraws()
    {
        $event = new Event($this->eventname);
        if ($event->player_reported_draws == 1) {
            return 1;
        } else {
            return 2;
        }
    }
}
