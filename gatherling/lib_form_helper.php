<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\Components\FileInput;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\SelectInput;
use Gatherling\Views\Components\CheckboxInput;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    return (new TextInput($label, $name, $value, $size, $reminderText, $id))->render();
}

function checkboxInput(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): string
{
    return (new CheckboxInput($label, $name, $isChecked, $reminderText))->render();
}

/** @param array<string, string> $options */
function selectInput(string $label, string $name, ?array $options, mixed $selected = null, ?string $id = null): string
{
    return (new SelectInput($label, $name, $options, $selected, $id))->render();
}

/**
 * @param list<string|int> $items
 * @return list<array{value: string, text: string}>
 */
function opts(array $items): array
{
    return array_map(fn (string|int $item) => ['value' => (string) $item, 'text' => (string) $item], $items);
}
