<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\DeckDto;

class MostPlayedDecks extends Component
{
    /** @var list<array{count: int, playerLink: string, playerName: string, deckLink: string, deckName: string, archetype: string, format: string, created: ?Time}> */
    public array $decks = [];

    public function __construct()
    {
        DB::execute("set session sql_mode='';"); // Disable ONLY_FULL_GROUP_BY
        $sql = '
            SELECT
                COUNT(d.deck_hash) as cnt, d.playername, d.name, d.archetype, d.format, d.created_date, d.id
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

        $decks = DB::select($sql, DeckDto::class);
        foreach ($decks as $deck) {
            $created = $deck->created_date ? strtotime($deck->created_date) : null;
            $createdTime = $created ? new Time($created, time()) : null;
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
