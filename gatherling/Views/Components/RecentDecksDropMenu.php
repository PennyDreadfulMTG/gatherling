<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class RecentDecksDropMenu extends Component
{
    /** @var array<array{value: string, text: string}> */
    public array $options;

    public function __construct(string $playerName)
    {
        parent::__construct('partials/recentDecksDropMenu');

        $deckplayer = new Player($playerName);
        $recentDecks = $deckplayer->getRecentDecks();

        $this->options = [
            ['value' => '0', 'text' => 'Select a recent deck to start from there'],
        ];
        foreach ($recentDecks as $deck) {
            if ($deck->id && $deck->name) {
                $this->options[] = [
                    'value' => (string) $deck->id,
                    'text' => $deck->name,
                ];
            }
        }
    }
}
