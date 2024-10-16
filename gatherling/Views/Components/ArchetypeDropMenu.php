<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;

class ArchetypeDropMenu extends DropMenu
{
    public function __construct(public string $archetypeName, int $useAll = 0, string $formName = 'archetype')
    {
        $sql = 'SELECT name FROM archetypes WHERE priority > 0 ORDER BY name';
        $archetypes = DB::values($sql, 'string');
        $title = ($useAll == 0) ? '- Archetype -' : 'All';
        $options = [];
        foreach ($archetypes as $archetype) {
            $options[] = [
                'value' => $archetype,
                'text' => $archetype,
                'isSelected' => strcmp($archetype, $archetypeName) == 0,
            ];
        }
        parent::__construct($formName, $options, $title, 'ds_select');
    }
}
