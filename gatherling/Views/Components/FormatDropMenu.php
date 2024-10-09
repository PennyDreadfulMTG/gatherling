<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\FormatDTO;

class FormatDropMenu extends DropMenu
{
    public function __construct(?string $format, bool $useAll = false, string $formName = 'format')
    {
        $sql = 'SELECT name FROM formats ORDER BY priority desc, name';
        $formats = DB::select($sql, FormatDTO::class);

        $options = [];
        foreach ($formats as $f) {
            $options[] = [
                'text' => $f->name,
                'value' => $f->name,
                'isSelected' => $f->name === $format,
            ];
        }

        $default = $useAll == 0 ? '- Format -' : 'All';

        parent::__construct($formName, $options, $default);
    }
}
