<?php

namespace Gatherling\Views\Pages;

use Gatherling\Models\Deck;
use Gatherling\Views\TextFileDownload;

class DeckDownload extends TextFileDownload
{
    public array $maindeckCards;
    public array $sideboardCards;

    public function __construct(Deck $deck)
    {
        $filename = preg_replace('/ /', '_', $deck->name).'.txt';
        parent::__construct($filename);
        $this->maindeckCards = $this->prepare($deck->maindeck_cards);
        $this->sideboardCards = $this->prepare($deck->sideboard_cards);
    }

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
