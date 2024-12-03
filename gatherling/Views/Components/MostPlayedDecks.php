<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\MostPlayedDeckDto;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\datetime;
use function Gatherling\Helpers\db;

class MostPlayedDecks extends Component
{
    /** @var list<array{count: int, playerLink: string, playerName: string, deckLink: string, deckName: string, archetype: string, format: string, created: ?Time}> */
    public array $decks = [];

    public function __construct()
    {
        db()->execute("set session sql_mode='';"); // Disable ONLY_FULL_GROUP_BY
        $sql = '
            SELECT
                COUNT(d.deck_hash) as cnt,
                d.playername,
                d.name,
                d.archetype,
                d.format,
                d.created_date,
                d.id
            FROM
                decks d, entries n
            WHERE
                n.deck = d.id
            AND 5 < (
                SELECT
                    COUNT(*)
                FROM
                    deckcontents
                WHERE
                    deck = d.id
                GROUP BY
                    deck
            )
            GROUP BY
                d.deck_hash
            ORDER BY
                cnt DESC
            LIMIT 20';

        $decks = db()->select($sql, MostPlayedDeckDto::class);
        $now = new DateTimeImmutable();
        foreach ($decks as $deck) {
            $createdTime = new Time(datetime($deck->created_date), $now);
            $this->decks[] = [
                'count' => $deck->cnt,
                'playerLink' => 'profile.php?player=' . rawurlencode($deck->playername) . '&mode=Lookup+Profile',
                'playerName' => $deck->playername,
                'deckLink' => 'deck.php?mode=view&id=' . rawurlencode((string) $deck->id),
                'deckName' => $deck->name,
                'archetype' => $deck->archetype ?? '',
                'format' => $deck->format ?? '',
                'created' => $createdTime,
            ];
        }
    }
}
