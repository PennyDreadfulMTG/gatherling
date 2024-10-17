<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class AllMatchForm extends Component
{
    public DropMenu $formatDropMenuP;
    public DropMenu $seriesDropMenuP;
    public DropMenu $seasonDropMenuP;
    public DropMenu $oppDropMenu;

    public function __construct(Player $player, string $selectedFormat, string $selectedSeries, string $selectedSeason, string $selectedOpponent)
    {
        $this->formatDropMenuP = selector(opts($player->getFormatsPlayed()), 'format', '-- Format --', $selectedFormat ? $selectedFormat : '%');
        $this->seriesDropMenuP = selector(opts($player->getSeriesPlayed()), 'series', '-- Series --', $selectedSeries ? $selectedSeries : '%');
        $this->seasonDropMenuP = selector(opts($player->getSeasonsPlayed()), 'season', '-- Season --', $selectedSeason ? $selectedSeason : '%');
        $opts = array_map(fn (array $item) => ['value' => $item['opp'], 'text' => $item['opp'] . ' [' . $item['cnt'] . ']'], $player->getOpponents());
        $this->oppDropMenu = selector($opts, 'opp', '-- Opponent --', $selectedOpponent ? $selectedOpponent : '%');
    }
}

/** @param list<array{value: string, text: string}> $items */
function selector(array $items, string $name, string $default, string $selected): DropMenu
{
    $options = [];
    $options[] = ['value' => '%', 'text' => $default];
    foreach ($items as $item) {
        $isSelected = $item['value'] == $selected;
        $options[] = ['value' => $item['value'], 'text' => $item['text'], 'isSelected' => $isSelected];
    }
    return new DropMenu($name, $options);
}

/**
 * @param list<string|int> $items
 * @return list<array{value: string, text: string}>
 */
function opts(array $items): array
{
    return array_map(fn (string|int $item) => ['value' => (string) $item, 'text' => (string) $item], $items);
}
