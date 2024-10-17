<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class SymbolTable extends Component
{
    public int $sum;
    /** @var array<array{color: string, num: int}> */
    public array $symbols;

    public function __construct(Deck $deck)
    {
        $cnts = $deck->getColorCounts();
        asort($cnts);
        $cnts = array_reverse($cnts, true);
        $sum = 0;
        foreach ($cnts as $color => $num) {
            if ($num == 0) {
                continue;
            }
            $this->symbols[] = [
                'color' => $color,
                'num' => $num,
            ];
            $sum += $num;
        }
        $this->sum = $sum;
    }
}
