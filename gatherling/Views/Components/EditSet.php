<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\CardDto;
use Gatherling\Models\CardSetDto;

use function Gatherling\Helpers\db;

class EditSet extends Component
{
    public TextInput $setCodeInput;
    public TextInput $releaseDateInput;
    public CheckboxInput $standardLegalInput;
    public CheckboxInput $modernLegalInput;
    /** @var array<array{id: int, name: string, type: string, rarity: string, count: int, checked: bool, editLink: string}> */
    public array $cards = [];

    public function __construct(public string $cardSetName)
    {
        $names = [];

        $sql = '
            SELECT
                `code`, `released`, `standard_legal`, `modern_legal`
            FROM
                `cardsets`
            WHERE
                `name` = :name';
        $set = db()->selectOnly($sql, CardSetDto::class, ['name' => $cardSetName]);

        $this->setCodeInput = new TextInput('Set Code', 'code', $set->code);
        $this->releaseDateInput = new TextInput('Release Date', 'released', $set->released);
        $this->standardLegalInput = new CheckboxInput('Standard Legal', 'standard_legal', (bool) $set->standard_legal);
        $this->modernLegalInput = new CheckboxInput('Modern Legal', 'modern_legal', (bool) $set->modern_legal);

        $sql = '
            SELECT
                `id`, `name`, `type`, `rarity`, `scryfallId`, COUNT(*) AS `count`
            FROM
                `cards`
            LEFT JOIN
                `deckcontents` ON `cards`.`id` = `deckcontents`.`card`
            WHERE
                `cardset` = :cardset';
        $cards = db()->select($sql, CardDto::class, ['cardset' => $cardSetName]);

        $names = [];
        foreach ($cards as $card) {
            $checked = in_array($card->name, $names) && $card->count == 0;
            $names[] = $card->name;
            $this->cards[] = [
                'id' => $card->id,
                'name' => $card->name,
                'type' => $card->type,
                'rarity' => $card->rarity,
                'count' => $card->count,
                'checked' => $checked,
                'editLink' => 'cardscp.php?view=edit_card&id=' . rawurlencode((string) $card->id),
            ];
        }
    }
}
