<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Data\DB;
use Gatherling\Exceptions\DatabaseException;

class Matchup
{
    public int $id;
    public ?int $subevent = null;
    public ?int $round;
    public ?string $playera;
    public ?string $playerb;
    public ?string $result;
    // We keep both players wins and losses, so that they can independently report their scores.
    public ?int $playera_wins;
    public ?int $playera_losses;
    public ?int $playera_draws;
    public ?int $playerb_wins;
    public ?int $playerb_losses;
    public ?int $playerb_draws;

    // Inherited from subevent

    public ?int $timing;
    public ?string $type;
    public ?int $rounds;

    // Inherited from event

    public ?string $format;
    public ?string $series;
    public ?int $season;
    public ?string $eventname;
    public ?int $event_id;

    // added for matching

    public ?string $verification;

    public static function destroy(int $matchid): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM matches WHERE id = ?');
        $stmt->bind_param('d', $matchid);
        $stmt->execute();
        $rows = (int) $stmt->affected_rows;
        $stmt->close();

        return $rows;
    }

    public function __construct(int $id)
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
    public function getEvent(): Event
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

    private function playerA(string $name): bool
    {
        if (is_null($this->playera)) {
            return false;
        }
        return strcasecmp($this->playera, $name) == 0;
    }

    private function playerB(string $name): bool
    {
        if (is_null($this->playerb)) {
            return false;
        }
        return strcasecmp($this->playerb, $name) == 0;
    }

    private function toName(string|Player $player_or_name): ?string
    {
        if (is_object($player_or_name)) {
            return $player_or_name->name;
        }

        return $player_or_name;
    }

    public function playerLetter(string|Player $player): ?string
    {
        if ($this->playerA($player)) {
            return 'a';
        } elseif ($this->playerB($player)) {
            return 'b';
        } else {
            return null;
        }
    }

    // Returns true if $player has a bye in this match
    public function playerBye(string|Player $player): bool
    {
        if ($this->result != 'BYE') {
            return false;
        }
        $playername = $this->toName($player);

        return $this->playerA($playername) || $this->playerB($playername);
    }

    // Returns true if $player is playing this match right now.
    public function playerMatchInProgress(string|Player $player): bool
    {
        if ($this->result != 'P') {
            return false;
        }
        $playername = $this->toName($player);

        return $this->playerA($playername) || $this->playerB($playername);
    }

    public function playerWon(string|Player $player): bool
    {
        $playername = $this->toName($player);

        return ($this->playerA($playername) && ($this->result == 'A'))
             || ($this->playerB($playername) && ($this->result == 'B'));
    }

    public function playerLost(string|Player $player): bool
    {
        $playername = $this->toName($player);

        return ($this->playerA($playername) && ($this->result == 'B'))
         || ($this->playerB($playername) && ($this->result == 'A'));
    }

    // returns the number of wins for the current match for $player
    // returns false if the player is not in this match.
    public function getPlayerWins(string|Player $player): int|null|false
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

    public function getPlayerResult(string|Player $player): string
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

        throw new Exception("Player $playername is not in match {$this->id}");
    }

    public function playerDropped(string $player): bool
    {
        $entry = new Entry($this->event_id, $player);

        return $entry->drop_round == $this->round;
    }

    // returns the number of wins for the current match for $player
    // Returns false if the player is not in this match.
    public function getPlayerLosses(string|Player $player): int|null|false
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

    public function getWinner(): ?string
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

        return null;
    }

    public function isBYE(): bool
    {
        return $this->result == 'BYE';
    }

    public function matchInProgress(): bool
    {
        return $this->result == 'P';
    }

    public function getLoser(): ?string
    {
        if ($this->playerLost($this->playera)) {
            return $this->playera;
        }
        if ($this->playerLost($this->playerb)) {
            return $this->playerb;
        }
        return null;
    }

    public function otherPlayer(string $oneplayer): ?string
    {
        if (strcasecmp($oneplayer, $this->playera) == 0) {
            return $this->playerb;
        } elseif (strcasecmp($oneplayer, $this->playerb) == 0) {
            return $this->playera;
        }
        return null;
    }

    // Returns a count of how many matches there are total.
    public static function count(): int
    {
        return Database::singleResult('SELECT count(id) FROM matches');
    }

    // Saves a report from a player on their match results.
    public static function saveReport(string $result, int $match_id, string $player): void
    {
        $savedMatch = new self($match_id);
        if ($savedMatch->result != 'P') {
            return;
        }
        // Which player is reporting?
        if ($player == 'a') {
            $sql = 'UPDATE matches SET playera_wins = :wins, playera_losses = :losses WHERE id = :id';
        } else {
            $sql = 'UPDATE matches SET playerb_wins = :wins, playerb_losses = :losses WHERE id = :id';
        }

        switch ($result) {
            case 'W20':
                $wins = 2;
                $losses = 0;
                break;
            case 'W21':
                $wins = 2;
                $losses = 1;
                break;
            case 'L20':
                $wins = 0;
                $losses = 2;
                break;
            case 'L21':
                $wins = 1;
                $losses = 2;
                break;
            case 'D':
                $wins = 1;
                $losses = 1;
                break;
            default:
                throw new Exception("Invalid result: $result"); // BAKERT better exception
        }
        DB::execute($sql, ['wins' => $wins, 'losses' => $losses, 'id' => $match_id]);
        self::validateReport($match_id);
    }

    public function reportSubmitted(string $name): bool
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
    public static function validateReport(int $match_id): void
    {
        // get and compare reports
        $sql = 'SELECT subevent, playera_wins, playerb_wins, playera_losses, playerb_losses FROM matches WHERE id = :id';
        $report = DB::selectOnly($sql, ReportDto::class, ['id' => $match_id]);

        if (($report->playera_wins + $report->playera_losses) == 0 or ($report->playerb_wins + $report->playerb_losses) == 0) {
            //No second report, quit
            return;
        } else {
            //Compare reports
            if (($report->playera_wins == $report->playerb_losses) and ($report->playerb_wins == $report->playera_losses)) {
                //matched, set verified
                self::flagVerified($match_id);
                $event = Event::getEventBySubevent($report->subevent);
                $event->resolveRound($report->subevent, $event->current_round);
            } else {
                //failed match, flag
                self::flagFailed($match_id);
            }
        }
    }

    public static function flagVerified(int $match_id): void
    {
        $sql = "UPDATE matches SET verification = 'verified' WHERE id = :id";
        DB::execute($sql, ['id' => $match_id]);
    }

    public static function flagFailed(int $match_id): void
    {
        $sql = "UPDATE matches SET verification = 'failed' WHERE id = :id";
        DB::execute($sql, ['id' => $match_id]);
    }

    public static function unresolvedMatchesCheck(int $subevent, int $current_round): int
    {
        $db = @Database::getConnection();
        $stmt = $db->prepare("SELECT count(id) FROM matches where subevent = ? AND verification != 'verified' AND round = ?");
        $stmt->bind_param('sd', $subevent, $current_round);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    // Goes through all matches in this round and updates the "Standing" objects with new scores.
    public function updateScores(string $structure): void
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
                } elseif ($structure == 'League' || $structure == 'League Match') {
                    $playera_standing->score += 3;
                    $playerb_standing->score += $seasonRules['loss_pts'];
                }
                $this->result = 'A';
            } else {
                if ($structure == 'Single Elimination') {
                    $playera_standing->active = 0;
                } elseif (strpos($structure, 'Swiss') === 0) {
                    $playerb_standing->score += 3;
                } elseif ($structure == 'League' || $structure == 'League Match') {
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
                $playerb_standing->matches_played++;
                $playerb_standing->games_played += $this->playera_wins + $this->playera_losses;
                $playerb_standing->games_won += $this->playerb_wins;
            }
            $playera_standing->save();
            $playerb_standing->save();
        }
        $this->finalizeMatch($this->result, $this->id);
    }

    // temp, will fix later
    // Don't know what this does, but it looks a lot like the above.
    public function fixScores(string $structure): void
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
                if ($structure == 'League' || $structure == 'League Match') {
                    $playerb_standing->score += $seasonRules['loss_pts'];
                }
                if ($structure == 'Single Elimination') {
                    $playerb_standing->active = 0;
                }
                $this->result = 'A';
            } else {
                $playerb_standing->score += 3;
                $playerb_standing->matches_won += 1;
                if ($structure == 'League' || $structure == 'League Match') {
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

    public function finalizeMatch(string $winner, int $match_id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE matches SET result = ? WHERE id = ?');
        $stmt->bind_param('sd', $winner, $match_id);
        $stmt->execute();
        $stmt->close();
    }

    public function playerReportableCheck(): bool
    {
        $event = new Event($this->getEventNamebyMatchid());
        return $event->player_reportable == 1;
    }

    public function getEventNamebyMatchid(): string
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

    public function isDraw(): bool
    {
        return $this->playera_wins == $this->playerb_wins;
    }

    public function isReportable(): bool
    {
        $event = $this->getEvent();

        return $event->player_reportable == 1;
    }

    public function allowsPlayerReportedDraws(): int
    {
        $event = new Event($this->eventname);
        if ($event->player_reported_draws == 1) {
            return 1;
        } else {
            return 2;
        }
    }
}
