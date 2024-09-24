<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class FormatsDropMenu extends Component
{
    public array $formatNames;

    public function __construct(public string $formatType, string $seriesName)
    {
        parent::__construct('partials/formatsDropMenu');

        $formatNames = [];
        if ($formatType == 'System') {
            $formatNames = Format::getSystemFormats();
        }
        if ($formatType == 'Public') {
            $formatNames = Format::getPublicFormats();
        }
        if ($formatType == 'Private') {
            $formatNames = Format::getPrivateFormats($seriesName);
        }
        if ($formatType == 'Private+') {
            $formatNames = array_merge(
                Format::getSystemFormats(),
                Format::getPublicFormats(),
                Format::getPrivateFormats($seriesName)
            );
        }
        if ($formatType == 'All') {
            $formatNames = Format::getAllFormats();
        }

        $this->formatNames = $formatNames;
    }
}
