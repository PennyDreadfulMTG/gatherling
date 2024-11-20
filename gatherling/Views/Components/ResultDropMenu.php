<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ResultDropMenu extends DropMenu
{
    /**
     * @param array<string, string> $extraOptions
     * @return array{name: string, default: string, options: array<int, array{value: string, text: string}>}
     */
    public function __construct(string $name, array $extraOptions = [])
    {
        $options = [
            ['value' => '2-0', 'text' => '2-0'],
            ['value' => '2-1', 'text' => '2-1'],
            ['value' => '1-2', 'text' => '1-2'],
            ['value' => '0-2', 'text' => '0-2'],
            ['value' => 'D', 'text' => 'Draw'],

        ];
        foreach ($extraOptions as $value => $text) {
            $options[] = ['value' => $value, 'text' => $text];
        }
        parent::__construct($name, $options, '- Result -');
    }
}
