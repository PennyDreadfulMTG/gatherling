<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class LoadFormatForm extends Component
{
    public FormatsDropMenu $formatsDropMenu;

    public function __construct(string $seriesName)
    {
        $type = $seriesName == 'System' ? 'All' : 'Private';
        $this->formatsDropMenu = new FormatsDropMenu($type, $seriesName);
    }
}
