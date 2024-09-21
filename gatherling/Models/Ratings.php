<?php

namespace Gatherling\Models;

use Exception;
use Gatherling\Views\Components\DropMenu;
use Gatherling\Views\Components\FormatDropMenuR;

class Ratings
{
    public $player;
    public $rating;
    public $format;
    public $updated;
    public $wins;
    public $losses;

    public $ratingNames;

    public function __construct($format = '')
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

    public function formatDropMenuR($format = ''): string
    {
        return (new FormatDropMenuR($format))->render();
    }

    public function deleteAllRatings()
    {
        $db = Database::getConnection();
        $db->query('DELETE FROM ratings') or exit($db->error);
    }

    public function deleteRatingByFormat($format)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('Delete FROM ratings WHERE format = ?');
        if (!$stmt) {
            exit($db->error);
        }
        $stmt->bind_param('s', $format);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function calcAllRatings()
    {
        $this->calcCompositeRating();
        foreach ($this->ratingNames as $format) {
            $this->calcRatingByFormat($format);
        }
        $this->calcOtherRating();
    }

    public function calcCompositeRating()
    {
        $db = Database::getConnection();

        $result = $db->query("SELECT name, start FROM events WHERE finalized = '1' ORDER BY start") or exit($db->error);
        echo '<h3>Calculating Composite Ratings</h3>';

        while ($row = $result->fetch_assoc()) {
            $event = $row['name'];
            echo "Calculating for Event: $event <br /><br />";
            $players = $this->calcPostEventRatings($event, 'Composite');
            $this->insertRatings($event, $players, 'Composite', $row['start']);
        }
        $result->close();
    }

    public function calcRatingByFormat($format)
    {
        $db = Database::getConnection();
        $searchString = '%'.$format.'%';

        $stmt = $db->prepare("SELECT name, start FROM events WHERE finalized = '1' AND format LIKE ? ORDER BY start");
        $stmt->bind_param('s', $searchString);
        $stmt->execute();
        $eventname = $eventstart = null;
        $stmt->bind_result($eventname, $eventstart);

        $eventar = [];
        $index = 1;
        while ($stmt->fetch()) {
            $eventar[$index] = $eventname;
            $eventar[$index + 1] = $eventstart;
            $index += 2;
        }
        $stmt->close();

        echo "<h3>Calculating $format Ratings</h3>";

        for ($index = 1, $size = count($eventar); $index <= $size; $index += 2) {
            echo "Format: $format<br />";
            echo "Event: {$eventar[$index]}<br />";
            echo "Start: {$eventar[$index + 1]}<br /><br />";
            echo "Calculating for Event: {$eventar[$index]}<br />";
            $players = $this->calcPostEventRatings($eventar[$index], $format);
            $this->insertRatings($eventar[$index], $players, $format, $eventar[$index + 1]);
        }
    }

    public function calcOtherRating()
    {
        $db = Database::getConnection();

        $notlike = '';
        foreach ($this->ratingNames as $format) {
            $notlike = $notlike.' AND format NOT LIKE "%'.$format.'%" ';
        }

        $result = $db->query("SELECT name, start
                              FROM events
                              WHERE finalized = '1'
                              $notlike
                              ORDER BY start") or exit($db->error);
        echo '<h3>Calculating Other Formats Ratings</h3>';

        while ($row = $result->fetch_assoc()) {
            $event = $row['name'];
            echo "Calculating for Event: $event <br />";
            $players = $this->calcPostEventRatings($event, 'Other Formats');
            $this->insertRatings($event, $players, 'Other Formats', $row['start']);
        }
        $result->close();
    }

    public function calcFinalizedEventRatings($event, $format, $start)
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

    public function calcPostEventRatings($event, $format)
    {
        $players = $this->getEntryRatings($event, $format);
        $matches = $this->getMatches($event);
        for ($ndx = 0; $ndx < count($matches); $ndx++) {
            $aPts = 0.5;
            $bPts = 0.5;
            if (strcmp($matches[$ndx]['result'], 'A') == 0) {
                $aPts = 1.0;
                $bPts = 0.0;
                $players[$matches[$ndx]['playera']]['wins']++;
                $players[$matches[$ndx]['playerb']]['losses']++;
            } elseif (strcmp($matches[$ndx]['result'], 'B') == 0) {
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

    public function insertRatings($event, $players, $format, $date)
    {
        $db = Database::getConnection();

        foreach ($players as $player => $data) {
            $rating = $data['rating'];
            $wins = $data['wins'];
            $losses = $data['losses'];
            $stmt = $db->prepare('INSERT INTO ratings VALUES(?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssdssdd', $event, $player, $rating, $format, $date, $wins, $losses);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error, 1);
            }
            $stmt->close();
        }
    }

    public function newRating($old, $opp, $pts, $k)
    {
        $new = $old + ($k * ($pts - $this->winProb($old, $opp)));
        if ($old < $new) {
            $new = ceil($new);
        } elseif ($old > $new) {
            $new = floor($new);
        }

        return $new;
    }

    public function winProb($rating, $oppRating)
    {
        return 1 / (pow(10, ($oppRating - $rating) / 400) + 1);
    }

    public function getMatches($event)
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
        $playera = $playerb = $result = $kvalue = null;
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

    public function getEntryRatings($event, $format)
    {
        $event_id = Database::single_result_single_param('SELECT id
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
        $player = $rating = $qmax = $wins = $losses = null;
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
