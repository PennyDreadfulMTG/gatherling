<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class FormatDeleteForm extends Component
{
    public FormatsDropMenu $formatsDropMenu;

    public function __construct(string $seriesName)
    {
        $this->formatsDropMenu = $seriesName == 'System' ? new FormatsDropMenu('All', $seriesName) : new FormatsDropMenu('Private', $seriesName);
    }
}
