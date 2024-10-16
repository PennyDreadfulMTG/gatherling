<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class MaindeckTable extends Component
{
    public int $numMaindeckCards;
    public int $creaturesCount;
    public int $landsCount;
    public int $otherSpellsCount;
    /** @var array<int, array{amt: int, cardLink: CardLink}> */
    public array $creatures = [];
    /** @var array<int, array{amt: int, cardLink: CardLink}> */
    public array $lands = [];
    /** @var array<int, array{amt: int, cardLink: CardLink}> */
    public array $other = [];

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/maindeckTable');

        $this->numMaindeckCards = $deck->getCardCount($deck->maindeck_cards);

        $creatures = $deck->getCreatureCards();
        $this->creaturesCount = $deck->getCardCount($creatures);

        $other = $deck->getOtherCardS();
        $this->otherSpellsCount = $deck->getCardCount($other);

        $lands = $deck->getLandCards();
        $this->landsCount = $deck->getCardCount($lands);

        foreach ($creatures as $card => $amt) {
            $this->creatures[] = [
                'amt' => $amt,
                'cardLink' => new CardLink($card),
            ];
        }
        foreach ($other as $card => $amt) {
            $this->other[] = [
                'amt' => $amt,
                'cardLink' => new CardLink($card),
            ];
        }
        foreach ($lands as $card => $amt) {
            $this->lands[] = [
                'amt' => $amt,
                'cardLink' => new CardLink($card),
            ];
        }
    }
}
