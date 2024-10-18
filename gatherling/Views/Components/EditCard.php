<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\Format;
use Gatherling\Models\CardDto;
use Gatherling\Views\Components\CheckboxInput;

class EditCard extends Component
{
    public string $cardSet;
    public TextInput $nameInput;
    public TextInput $typelineInput;
    public TextInput $rarityInput;
    public TextInput $scryfallIdInput;
    public CheckboxInput $isChangelingInput;
    public string $creatureType = '';

    public function __construct(public int $id)
    {
        $sql = '
            SELECT
                `id`, `name`, `type`, `rarity`, `scryfallId`, `is_changeling`, `cardset`
            FROM
                `cards`
            WHERE
                `id` = :id';
        $card = DB::selectOnly($sql, CardDto::class, ['id' => $this->id]);

        $this->cardSet = $card->cardset;

        $this->nameInput = new TextInput('Card Name', 'name', $card->name, 100);
        $this->typelineInput = new TextInput('Typeline', 'type', $card->type, 100);
        $this->rarityInput = new TextInput('Rarity', 'rarity', $card->rarity);
        $this->scryfallIdInput = new TextInput('Scryfall ID', 'sfId', $card->scryfallId, 36);
        $this->isChangelingInput = new CheckboxInput('Changeling', 'is_changeling', (bool) $card->is_changeling);

        if (str_contains($card->type, 'Creature')) {
            $this->creatureType = Format::removeTypeCrap($card->type);
        }
    }
}
