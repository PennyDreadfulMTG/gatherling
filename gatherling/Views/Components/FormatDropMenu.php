<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\FormatDto;

use function Gatherling\Helpers\db;

class FormatDropMenu extends DropMenu
{
    public function __construct(?string $format, bool $useAll = false, string $formName = 'format')
    {
        $sql = 'SELECT name FROM formats ORDER BY priority desc, name';
        $formats = db()->strings($sql);

        $options = [];
        foreach ($formats as $formatName) {
            $options[] = [
                'text' => $formatName,
                'value' => $formatName,
                'isSelected' => $formatName === $format,
            ];
        }

        $default = $useAll ? 'All' : '- Format -';

        parent::__construct($formName, $options, $default);
    }
}
