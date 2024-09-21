<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class InitialSeedDropMenu extends DropMenu
{
    public function __construct(string $name, string $playerName, int $currentSeed, int $numEntries)
    {
        $options = [
            ['value' => "$playerName 127", 'text' => 'None', 'isSelected' => $currentSeed == 127],
        ];
        for ($i = 1; $i <= $numEntries; $i++) {
            $options[] = [
                'value'      => "$playerName $i",
                'text'       => "$i",
                'isSelected' => $currentSeed == $i,
            ];
        }
        $this->name = $name;
        $this->options = $options;
    }
}
