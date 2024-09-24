<?php

namespace Gatherling\Views\Components;

class FormatDeleteForm extends Component
{
    public FormatsDropMenu $formatsDropMenu;

    public function __construct(string $seriesName)
    {
        parent::__construct('partials/formatDeleteForm');
        $this->formatsDropMenu = $seriesName == 'System' ? new FormatsDropMenu('All', $seriesName) : new FormatsDropMenu('Private', $seriesName);
    }
}
