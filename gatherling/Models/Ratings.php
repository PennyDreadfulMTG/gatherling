<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Gatherling\Models\RatingsEventDto;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\datetime;
use function Gatherling\Helpers\db;

class Ratings
{
    public string $player;
    public int $rating;
    public string $format;
    public ?string $updated;
    public int $wins;
    public int $losses;
    /** @var list<string> */
    public array $ratingNames;

    public function __construct(string $format = '')
    {
        if ($format == '') {
            $this->player = '';
            $this->rating = 0;
            $this->format = '';
            $this->updated = null;
            $this->wins = 0;
            $this->losses = 0;
            $this->ratingNames = ['Standard', 'Extended', 'Modern', 'Classic', 'Legacy',
                'Pauper', 'SilverBlack', 'Heirloom', 'Commander', 'Tribal Wars',
                'Penny Dreadful', ];

            return;
        }
    }

    public function deleteAllRatings(): void
    {
        $db = Database::getConnection();
        $db->query('DELETE FROM ratings') or exit($db->error);
    }

    public function deleteRatingByFormat(string $format): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('Delete FROM ratings WHERE format = ?');
        if (!$stmt) {
            exit($db->error);
        }
        $stmt->bind_param('s', $format);
        $stmt->execute();
        $stmt->close();
    }

    public function calcAllRatings(): void
    {
        $this->calcCompositeRating();
        foreach ($this->ratingNames as $format) {
            $this->calcRatingByFormat($format);
        }
        $this->calcOtherRating();
    }

    public function calcCompositeRating(): void
    {
        $sql = "SELECT name, start FROM events WHERE finalized = '1' ORDER BY start";
        $results = db()->select($sql, FinalizedEventDto::class);
        foreach ($results as $event) {
            $players = $this->calcPostEventRatings($event->name, 'Composite');
            $this->insertRatings($event->name, $players, 'Composite', datetime($event->start));
        }
    }

    public function calcRatingByFormat(string $format): void
    {
        $searchString = '%' . $format . '%';

        $sql = 'SELECT name, start, format FROM events WHERE finalized = 1 AND format LIKE :search_string ORDER BY start';
        $params = ['search_string' => $searchString];

        $results = db()->select($sql, RatingsEventDto::class, $params);

        foreach ($results as $result) {
            $players = $this->calcPostEventRatings($result->name, $format);
            $this->insertRatings($result->name, $players, $format, new DateTimeImmutable($result->start));
        }
    }

    public function calcOtherRating(): void
    {
        $sql = '
            SELECT name, start
              FROM events
             WHERE finalized = 1
               AND format NOT IN (:formats)
          ORDER BY start';
        $params = ['formats' => $this->ratingNames];
        $results = db()->select($sql, FinalizedEventDto::class, $params);
        foreach ($results as $event) {
            $players = $this->calcPostEventRatings($event->name, 'Other Formats');
            $this->insertRatings($event->name, $players, 'Other Formats', datetime($event->start));
        }
    }

    public function calcFinalizedEventRatings(string $event, string $format, DateTimeImmutable $start): void
    {
        $players = $this->calcPostEventRatings($event, 'Composite');
        $this->insertRatings($event, $players, 'Composite', $start);
        $noEventRatingAvail = true;
        foreach ($this->ratingNames as $rating) {
            if (strpos($format, $rating) === false) {
                continue;
            } else {
                $players = $this->calcPostEventRatings($event, $rating);
                $this->insertRatings($event, $players, $rating, $start);
                $noEventRatingAvail = false;
            }
        }
        if ($noEventRatingAvail) {
            $players = $this->calcPostEventRatings($event, 'Other Formats');
            $this->insertRatings($event, $players, 'Other Formats', $start);
        }
    }

    /** @return array<string, array<string, int>> */
    public function calcPostEventRatings(string $event, string $format): array
    {
        $players = $this->getEntryRatings($event, $format);
        $matches = $this->getMatches($event);
        for ($ndx = 0; $ndx < count($matches); $ndx++) {
            $aPts = 0.5;
            $bPts = 0.5;
            if ($matches[$ndx]['result'] === 'A') {
                $aPts = 1.0;
                $bPts = 0.0;
                $players[$matches[$ndx]['playera']]['wins']++;
                $players[$matches[$ndx]['playerb']]['losses']++;
            } elseif ($matches[$ndx]['result'] === 'B') {
                $aPts = 0.0;
                $bPts = 1.0;
                $players[$matches[$ndx]['playerb']]['wins']++;
                $players[$matches[$ndx]['playera']]['losses']++;
            }
            $newA = $this->newRating(
                $players[$matches[$ndx]['playera']]['rating'],
                $players[$matches[$ndx]['playerb']]['rating'],
                $aPts,
                $matches[$ndx]['kvalue']
            );
            $newB = $this->newRating(
                $players[$matches[$ndx]['playerb']]['rating'],
                $players[$matches[$ndx]['playera']]['rating'],
                $bPts,
                $matches[$ndx]['kvalue']
            );

            $players[$matches[$ndx]['playera']]['rating'] = $newA;
            $players[$matches[$ndx]['playerb']]['rating'] = $newB;
        }

        return $players;
    }

    /** @param array<string, array<string, int>> $players */
    public function insertRatings(string $event, array $players, string $format, DatetimeImmutable $date): void
    {
        $sql = 'INSERT INTO ratings (event, player, rating, format, updated, wins, losses)
                     VALUES (:event, :player, :rating, :format, :updated, :wins, :losses)';

        foreach ($players as $player => $data) {
            $params = [
                'event' => $event,
                'player' => $player,
                'rating' => $data['rating'],
                'format' => $format,
                'updated' => $date,
                'wins' => $data['wins'],
                'losses' => $data['losses']
            ];
            db()->execute($sql, $params);
        }
    }

    public function newRating(int $old, int $opp, float $pts, int $k): int
    {
        $new = $old + ($k * ($pts - $this->winProb($old, $opp)));
        if ($old < $new) {
            $new = ceil($new);
        } elseif ($old > $new) {
            $new = floor($new);
        }

        return intval($new);
    }

    public function winProb(int $rating, int $oppRating): float
    {
        return 1 / (pow(10, ($oppRating - $rating) / 400) + 1);
    }

    /** @return list<array{playera: string, playerb: string, result: string, kvalue: int}> */
    public function getMatches(string $event): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT LCASE(m.playera) AS playera, LCASE(m.playerb) AS playerb, m.result, e.kvalue
                              FROM matches AS m, subevents AS s, events AS e
                              WHERE m.subevent=s.id
                              AND s.parent=e.name
                              AND e.name = ?
                              ORDER BY s.timing, m.round');
        $stmt->bind_param('s', $event);
        $stmt->execute();
        $stmt->bind_result($playera, $playerb, $result, $kvalue);

        $data = [];
        while ($stmt->fetch()) {
            $data[] = ['playera'      => $playera,
                'playerb'             => $playerb,
                'result'              => $result,
                'kvalue'              => $kvalue, ];
        }
        $stmt->close();

        return $data;
    }

    /** @return array<string, array<string, int>> */
    public function getEntryRatings(string $event, string $format): array
    {
        $event_id = Database::singleResultSingleParam('SELECT id
                                                          FROM events
                                                          WHERE name = ?', 's', $event);

        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT LCASE(n.player) AS player, r.rating, q.qmax, r.wins, r.losses
                              FROM entries AS n
                              LEFT OUTER JOIN ratings AS r ON r.player = n.player
                              LEFT OUTER JOIN
                              (SELECT qr.player AS qplayer, MAX(qr.updated) AS qmax
                              FROM ratings AS qr, events AS qe
                              WHERE qr.updated<qe.start AND qe.name = ? AND qr.format = ?
                              GROUP BY qr.player) AS q
                              ON q.qplayer=r.player
                              WHERE n.event_id = ? AND ((q.qmax=r.updated AND q.qplayer=r.player AND r.format = ?)
                              OR q.qmax IS NULL)
                              GROUP BY n.player ORDER BY n.player');
        $stmt or exit($db->error);
        $stmt->bind_param('ssds', $event, $format, $event_id, $format);
        $stmt->execute();
        $stmt->bind_result($player, $rating, $qmax, $wins, $losses);

        $data = [];
        while ($stmt->fetch()) {
            $datum = [];
            if (!is_null($qmax)) {
                $datum['rating'] = $rating;
                $datum['wins'] = $wins;
                $datum['losses'] = $losses;
            } else {
                $datum['rating'] = 1600;
                $datum['wins'] = 0;
                $datum['losses'] = 0;
            }
            $data[$player] = $datum;
        }
        $stmt->close();

        return $data;
    }
}
