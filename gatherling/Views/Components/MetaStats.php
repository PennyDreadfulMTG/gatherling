<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\Db;
use Gatherling\Models\Event;
use Gatherling\Models\ArchetypeDto;

class MetaStats extends Component
{
    /** @var list<array{name: string, pcg: int}> */
    public array $archetypes;
    /** @var list<array{pcg: int|'??'}> */
    public array $colors;

    public function __construct(Event $event)
    {
        $archcnt = $this->initArchetypeCount();
        $colorcnt = ['w' => 0, 'g' => 0, 'u' => 0, 'r' => 0, 'b' => 0];
        $decks = $event->getDecks();
        $ndecks = count($decks);
        foreach ($decks as $deck) {
            foreach ($deck->getColorCounts() as $color => $count) {
                $colorcnt[$color] += $count > 0 ? 1 : 0;
            }
            $archcnt[$deck->archetype]++;
        }

        $this->archetypes = [];
        foreach ($archcnt as $arch => $cnt) {
            if ($cnt > 0) {
                $pcg = (int) (round(($cnt / $ndecks) * 100));
                $this->archetypes[] = [
                    'name' => $arch,
                    'pcg' => $pcg,
                ];
            }
        }

        $this->colors = [];
        foreach ($colorcnt as $col => $cnt) {
            if ($col != '') {
                if ($ndecks > 0) {
                    $pcg = (int) (round(($cnt / $ndecks) * 100));
                } else {
                    $pcg = '??';
                }
                $this->colors[] = [
                    'pcg' => $pcg,
                ];
            }
        }
    }

    /** @return array<string, int> */
    private function initArchetypeCount(): array
    {
        $archetypes = Db::select('SELECT name FROM archetypes ORDER BY priority DESC', ArchetypeDto::class);
        return array_fill_keys(array_column($archetypes, 'name'), 0);
    }
}
