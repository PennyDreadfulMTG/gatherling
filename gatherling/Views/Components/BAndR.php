<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class BAndR extends Component
{
    public string $formatName;
    public int $restrictedCardCount;
    /** @var array<int, array{cardName: string, cardLink: ?CardLink}> */
    public array $restrictedCards;
    public NotAllowed $noRestrictedCards;
    public int $bannedCardCount;
    /** @var array<int, array{cardName: string, cardLink: ?CardLink}> */
    public array $bannedCards;
    public NotAllowed $noBannedCards;
    public int $legalCardCount;
    /** @var array<int, array{cardName: string, cardLink: ?CardLink}> */
    public array $legalCards;
    public NotAllowed $noLegalCards;

    public function __construct(public string $seriesName, Format $format)
    {
        $this->formatName = $format->name;

        $restrictedCards = $format->getRestrictedList();
        $this->restrictedCardCount = count($restrictedCards);
        $this->restrictedCards = array_map(fn ($cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->restrictedCardCount <= 100 ? new CardLink($cardName) : null,
        ], $restrictedCards);
        if ($this->restrictedCardCount === 0) {
            $this->noRestrictedCards = new NotAllowed('No Restricted Cards To Delete');
        }

        $bannedCards = $format->getBanList();
        $this->bannedCardCount = count($bannedCards);
        $this->bannedCards = array_map(fn ($cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->bannedCardCount <= 100 ? new CardLink($cardName) : null,
        ], $bannedCards);
        if ($this->bannedCardCount === 0) {
            $this->noBannedCards = new NotAllowed('No Banned Cards To Delete');
        }

        $legalCards = $format->getLegalList();
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
