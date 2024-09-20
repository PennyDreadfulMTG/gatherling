<?php

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\Event;
use Gatherling\Models\Database;

class MetaStats extends Component
{
    public array $archetypes;
    public array $colors;

    public function __construct(Event $event)
    {
        parent::__construct('partials/metaStats');

        $archcnt = $this->initArchetypeCount();
        $colorcnt = ['w' => 0, 'g' => 0, 'u' => 0, 'r' => 0, 'b' => 0];
        $decks = $event->getDecks();
        $ndecks = count($decks);
        foreach ($decks as $deck) {
            if (is_null($deck)) {
                $ndecks--;
                continue;
            }
            foreach ($deck->getColorCounts() as $color => $count) {
                $colorcnt[$color] += $count > 0 ? 1 : 0;
            }
            $archcnt[$deck->archetype]++;
        }

        $this->archetypes = [];
        foreach ($archcnt as $arch => $cnt) {
            if ($cnt > 0) {
                $pcg = round(($cnt / $ndecks) * 100);
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
                    $pcg = round(($cnt / $ndecks) * 100);
                } else {
                    $pcg = '??';
                }
                $this->colors[] = [
                    'pcg' => $pcg,
                ];
            }
        }
    }

    private function initArchetypeCount()
    {
        $archetypes = DB::select('SELECT name FROM archetypes ORDER BY priority DESC');
        return array_fill_keys(array_column($archetypes, 'name'), 0);
    }
}
