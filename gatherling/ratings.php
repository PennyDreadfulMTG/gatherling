<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Player;
use Gatherling\Models\Pagination;
use Gatherling\Views\Pages\Ratings;

require_once 'lib.php';

function main(): void
{
    $format = $_POST['format'] ?? 'Composite';
    ['date' => $lastTournamentDate, 'name' => $lastTournamentName] = currentThrough($format);
    ['rating' => $highestRating, 'player' => $highestRatedPlayer, 't' => $highestRatingTimestamp] = bestEver($format);
    $minMatches = 20;
    ['ratings_data' => $ratingsData, 'pagination' => $pagination] = ratingsData($format, $minMatches);
    $page = new Ratings($format, $lastTournamentDate, $lastTournamentName, $highestRating, $highestRatedPlayer, $highestRatingTimestamp, $minMatches, $ratingsData, $pagination);
    $page->send();
}

/** @return array{ratings_data: array<int, array{qplayer: string, qmax: int, player: string, rank: int, playerName: string, player: Player}>, pagination: Pagination} */
function ratingsData(string $format, int $minMatches): array
{
    $subquery = '
        SELECT
            qr.player AS qplayer, MAX(qr.updated) AS qmax
        FROM
            ratings AS qr
        WHERE
            qr.format = :format
        GROUP BY qr.player';
    $sql = "
        SELECT
            p.name AS player, r.rating, r.wins, r.losses
        FROM
            ratings r,
            players p,
            ({$subquery}) AS q
        WHERE
            r.format = :format AND p.name = r.player AND q.qplayer = r.player AND q.qmax = r.updated AND r.wins + r.losses >= :min_matches
        ORDER BY
            r.rating DESC";
    $ratings_data = DB::select($sql, ['format' => $format, 'min_matches' => $minMatches]);
    $rank = 0;
    foreach ($ratings_data as &$data) {
        $rank++;
        $data['rank'] = $rank;
        $data['playerName'] = $data['player'];
        $data['player'] = new Player($data['player']);
    }

    $records_per_page = 25;
    $pagination = new Pagination();
    $pagination->records(count($ratings_data));
    $pagination->records_per_page($records_per_page);
    $pagination->avoid_duplicate_content(false);

    // get the ratings for the current page
    $ratings_data = array_slice($ratings_data, (($pagination->get_page() - 1)
                                    * $records_per_page), $records_per_page);

    return ['ratings_data' => $ratings_data, 'pagination' => $pagination];
}

/** @return array{player: string, rating: int, t: int} */
function bestEver(string $format): array
{
    $sql = '
        SELECT
            p.name AS player, r.rating, UNIX_TIMESTAMP(r.updated) AS t
        FROM
            ratings AS r,
            players AS p,
            (
                SELECT
                    MAX(qr.rating) AS qmax
                FROM
                    ratings AS qr
                WHERE
                    qr.format = :format
            ) AS q
        WHERE
            format = :format AND p.name = r.player AND q.qmax = r.rating';
    return DB::select($sql, ['format' => $format])[0];
}

/** @return array{date: DateTime, name: string} */
function currentThrough(string $format): array
{
    $start = DB::value('SELECT MAX(updated) FROM ratings WHERE format = :format', ['format' => $format]);
    $name = DB::value('SELECT name FROM events WHERE start = :start', ['start' => $start]);
    return ['date' => new DateTime($start), 'name' => $name];
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
