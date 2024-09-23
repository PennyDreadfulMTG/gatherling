<?php

namespace Gatherling\Views\Components;

class LoadFormatForm extends Component
{
    public FormatsDropMenu $formatsDropMenu;

    public function __construct(string $seriesName)
    {
        parent::__construct('partials/loadFormatForm');
        $type = $seriesName == 'System' ? 'All' : 'Private';
        $this->formatsDropMenu = new FormatsDropMenu($type, $seriesName);
    }
}
