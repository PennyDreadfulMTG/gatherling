<?php

namespace Gatherling\Views\Components;

class FormatDropMenuR extends DropMenu
{
    public function __construct(public string $format)
    {
        $names = ['Composite', 'Standard', 'Extended', 'Modern',
            'Classic', 'Legacy', 'Pauper', 'SilverBlack', 'Heirloom',
            'Commander', 'Tribal Wars', 'Other Formats', ];
        $options = [];
        foreach ($names as $name) {
            $options[] = ['value' => $name, 'text' => $name, 'isSelected' => $name === $format];
        }
        parent::__construct('format', $options);
    }
}
