<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class ColorImages
{
    /** @var list<array{color: string, src: string}> */
    public array $colors = [];

    public function __construct(Deck $deck)
    {
        $count = $deck->getColorCounts();
        foreach ($count as $color => $n) {
            if ($n === 0) {
                continue;
            }
            $this->colors [] = [
                'color' => $color,
                'src' => 'styles/images/mana' . rawurlencode($color) . '.png',
            ];
        }
    }
}
