<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\CardSetDto;

use function Gatherling\Helpers\db;

class SetList extends Component
{
    /** @var array<array{name: string, editLink: string, code: string|null, released: string, type: string, lastUpdated: string|null, count: int}> */
    public array $sets = [];

    public function __construct()
    {
        $sql = '
            SELECT
                cs.name, cs.code, cs.released, cs.type, cs.last_updated, COUNT(*) AS `count`
            FROM
                cardsets AS cs
            LEFT JOIN
                cards AS c ON cs.name = c.cardset
            GROUP BY
                cs.name
            ORDER BY
                cs.name';
        $sets = db()->select($sql, CardSetDto::class);
        foreach ($sets as $set) {
            $this->sets[] = [
                'name' => $set->name,
                'editLink' => 'cardscp.php?view=edit_set&set=' . rawurlencode($set->name),
                'code' => $set->code,
                'released' => $set->released,
                'type' => $set->type,
                'lastUpdated' => $set->last_updated,
                'count' => $set->count,
            ];
        }
    }
}
