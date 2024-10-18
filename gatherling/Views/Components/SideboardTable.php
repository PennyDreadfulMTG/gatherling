<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class SideboardTable extends Component
{
    public int $numSideboardCards;
    /** @var array<array{amt: int, cardLink: CardLink}> */
    public array $sideboardCards;

    public function __construct(Deck $deck)
    {
        $sideboardCards = $deck->sideboard_cards;

        $this->numSideboardCards = $deck->getCardCount($sideboardCards);

        ksort($sideboardCards);
        arsort($sideboardCards, SORT_NUMERIC);

        foreach ($sideboardCards as $card => $amt) {
            $this->sideboardCards[] = [
                'amt' => $amt,
                'cardLink' => new CardLink($card),
            ];
        }
    }
}
