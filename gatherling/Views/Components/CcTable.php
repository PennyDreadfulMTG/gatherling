<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class CcTable extends Component
{
    /** @var list<array{cost: int, amt: int}> */
    public array $castingCosts;
    public string $avgCmc;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/ccTable');

        $convertedCosts = $deck->getCastingCosts();

        $total = $cards = 0;
        foreach ($convertedCosts as $cost => $amt) {
            $this->castingCosts[] = [
                'cost' => $cost,
                'amt' => $amt,
            ];
            $total += $cost * $amt;
            $cards += $amt;
        }
        $avg = $total / max($cards, 1);
        $this->avgCmc = sprintf('%1.2f', $avg);
    }
}