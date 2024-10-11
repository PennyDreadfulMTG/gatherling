<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Deck;
use Gatherling\Views\TextFileDownload;

class DeckDownload extends TextFileDownload
{
    /** @var list<array{qty: int, card: string}> */
    public array $maindeckCards;
    /** @var list<array{qty: int, card: string}> */
    public array $sideboardCards;

    public function __construct(Deck $deck)
    {
        $filename = preg_replace('/ /', '_', $deck->name) . '.txt';
        parent::__construct($filename);
        $this->maindeckCards = $this->prepare($deck->maindeck_cards);
        $this->sideboardCards = $this->prepare($deck->sideboard_cards);
    }

    /**
     * @param array<string, int> $cards
     * @return list<array{qty: int, card: string}>
     */
    private function prepare(array $cards): array
    {
        $entries = [];
        foreach ($cards as $card => $qty) {
            $card = normaliseCardName($card);
            $entries[] = ['qty' => $qty, 'card' => $card];
        }
        return $entries;
    }
}
