<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;
use Gatherling\Views\Components\TribeBanDropMenu;

class TribalBAndR extends Component
{
    public string $activeFormatName;
    public int $cardCount;
    /** @var list<array{cardName: string, cardLink: CardLink|null}> */
    public array $restrictedToTribe;
    public ?NotAllowed $noRestrictedToTribeCreatures;
    /** @var list<string> */
    public array $tribesBanned;
    public TribeBanDropMenu $tribeBanDropMenu;
    public ?NotAllowed $noTribesBanned;
    /** @var list<string> */
    public array $subTypesBanned;
    public TribeBanDropMenu $subTypeBanDropMenu;
    public ?NotAllowed $noSubTypesBanned;

    public function __construct(public string $seriesName, Format $activeFormat)
    {
        $this->activeFormatName = $activeFormat->name ?? '';

        $restrictedToTribe = $activeFormat->getRestrictedToTribeList();
        $this->cardCount = count($restrictedToTribe);
        $this->restrictedToTribe = array_map(fn (string $cardName) => [
            'cardName' => $cardName,
            'cardLink' => $this->cardCount <= 100 ? new CardLink($cardName) : null,
        ], $restrictedToTribe);
        if ($this->cardCount === 0) {
            $this->noRestrictedToTribeCreatures = new NotAllowed('No Restricted To Tribe Creatures To Delete');
        }

        // tribe ban
        // tribe will be banned, subtype will still be allowed in other tribes decks
        $this->tribesBanned = $activeFormat->getTribesBanned();
        $this->tribeBanDropMenu = new TribeBanDropMenu($activeFormat, 'tribeban');
        if (count($this->tribesBanned) === 0) {
            $this->noTribesBanned = new NotAllowed('No Selected Tribe To Delete');
        }

        // subtype ban
        // subtype is banned and is not allowed to be used by any deck
        $this->subTypesBanned = $activeFormat->getSubTypesBanned();
        $this->subTypeBanDropMenu = new TribeBanDropMenu($activeFormat, 'subtypeban');
        if (count($this->subTypesBanned) === 0) {
            $this->noSubTypesBanned = new NotAllowed('No Selected SubType To Delete');
        }
    }
}
