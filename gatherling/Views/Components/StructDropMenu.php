<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class StructDropMenu extends DropMenu
{
    public function __construct(string $field, string $def)
    {
        $names = ['Swiss', 'Single Elimination', 'League', 'League Match'];
        if ($def == 'Swiss (Blossom)') {
            $def = 'Swiss';
        }
        $options = [];
        foreach ($names as $name) {
            $options[] = [
                'value'      => $name,
                'text'       => $name,
                'isSelected' => $def === $name,
            ];
        }

        parent::__construct($field, $options, '- Structure -');
    }
}
