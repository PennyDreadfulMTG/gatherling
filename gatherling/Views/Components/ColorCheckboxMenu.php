<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ColorCheckboxMenu extends Component
{
    public bool $wChecked;
    public bool $bChecked;
    public bool $uChecked;
    public bool $gChecked;
    public bool $rChecked;

    /** @param ?array<string, string> $colors */
    public function __construct(array $colors = null)
    {
        $this->wChecked = isset($colors['w']);
        $this->bChecked = isset($colors['b']);
        $this->uChecked = isset($colors['u']);
        $this->gChecked = isset($colors['g']);
        $this->rChecked = isset($colors['r']);
    }
}
