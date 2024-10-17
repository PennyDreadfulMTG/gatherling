<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class MedalTable extends Component
{
    /** @var array<array{medal: string, medalSrc: string, numMedals: int}> */
    public array $medalStats;
    public string $playerName;
    public function __construct(Player $player)
    {
        $this->playerName = $player->name ?? '';
        $medalStats = $player->getMedalStats();
        $this->medalStats = array_map(fn($medal) => [
            'medal' => $medal,
            'medalSrc' => 'styles/images/' . rawurlencode($medal) . '.png',
            'numMedals' => $medalStats[$medal] ?? 0
        ], ['1st', '2nd', 't4', 't8']);
    }
}
