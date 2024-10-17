<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class PointsRule extends Component
{
    public string $name;
    public bool $isText;
    public ?string $value;
    public bool $isCheckbox;
    public bool $isChecked;
    public bool $isDropMenu;
    public ?FormatDropMenu $formatDropMenu;

    /** @param array<string, int|string> $rules */
    public function __construct(
        public string $rule,
        string $key,
        array $rules,
        string $formtype = 'text',
        public int $size = 4,
    ) {
        parent::__construct('partials/pointsRule');
        $this->name = "new_rules[{$key}]";
        $this->isText = $formtype == 'text';
        $this->value = $this->isText ? (string) $rules[$key] : null;
        $this->isCheckbox = $formtype == 'checkbox';
        $this->isChecked = $this->isCheckbox && $rules[$key] == 1;
        $this->isDropMenu = $formtype == 'format';
        $this->formatDropMenu = $this->isDropMenu ? new FormatDropMenu((string) $rules[$key], false, $this->name) : null;
    }
}
