<?php

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;

class FormatDropMenu extends DropMenu
{
    public function __construct(?string $format, bool $useAll = false, string $formName = 'format', bool $showMeta = true)
    {
        $sql = 'SELECT name FROM formats';
        if (!$showMeta) {
            $sql .= ' WHERE NOT is_meta_format ';
        }
        $sql .= ' ORDER BY priority desc, name';
        $formats = DB::select($sql);

        $options = [];
        foreach ($formats as $f) {
            $options[] = [
                'text' => $f['name'],
                'value' => $f['name'],
                'isSelected' => $f['name'] === $format,
            ];
        }

        $default = $useAll == 0 ? '- Format -' : 'All';

        parent::__construct($formName, $options, $default);
    }
}
