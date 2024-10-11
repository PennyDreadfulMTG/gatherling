<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class FormatRenameForm extends Component
{
    public FormatsDropMenu $formatsDropMenu;

    public function __construct(string $seriesName)
    {
        parent::__construct('partials/formatRenameForm');
        $this->formatsDropMenu = $seriesName == 'System' ? new FormatsDropMenu('All', $seriesName) : new FormatsDropMenu('Private', $seriesName);
    }
}
