<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ClientDropMenu extends DropMenu
{
    public function __construct(string $field, int $def)
    {
        $clients = [
            1 => 'MTGO',
            2 => 'Arena',
            3 => 'Other',
        ];
        $options = [];
        foreach ($clients as $value => $text) {
            $options[] = [
                'isSelected' => $def == $value,
                'value'      => (string) $value,
                'text'       => $text,
            ];
        }

        parent::__construct($field, $options, '- Client -', $field);
    }
}
