<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class BAndR extends Component
{
    public string $activeFormatName;
    public int $restrictedCardCount;
    public array $restrictedCards;
    public NotAllowed $noRestrictedCards;
    public int $bannedCardCount;
    public array $bannedCards;
    public NotAllowed $noBannedCards;
    public int $legalCardCount;
    public array $legalCards;
    public NotAllowed $noLegalCards;

    public function __construct(public string $seriesName, Format $activeFormat)
    {
        parent::__construct('partials/bAndR');

        $this->activeFormatName = $activeFormat->name;

        $restrictedCards = $activeFormat->getRestrictedList();
        $this->restrictedCardCount = count($restrictedCards);
        $this->restrictedCards = array_map(fn ($cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->restrictedCardCount <= 100 ? new CardLink($cardName) : null,
        ], $restrictedCards);
        if ($this->restrictedCardCount === 0) {
            $this->noRestrictedCards = new NotAllowed('No Restricted Cards To Delete');
        }

        $bannedCards = $activeFormat->getBanList();
        $this->bannedCardCount = count($bannedCards);
        $this->bannedCards = array_map(fn ($cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->bannedCardCount <= 100 ? new CardLink($cardName) : null,
        ], $bannedCards);
        if ($this->bannedCardCount === 0) {
            $this->noBannedCards = new NotAllowed('No Banned Cards To Delete');
        }

        $legalCards = $activeFormat->getLegalList();
        $this->legalCardCount = count($legalCards);
        $this->legalCards = array_map(fn ($cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->legalCardCount <= 100 ? new CardLink($cardName) : null,
        ], $legalCards);
        if ($this->legalCardCount === 0) {
            $this->noLegalCards = new NotAllowed('No Legal Cards To Delete');
        }
    }
}
