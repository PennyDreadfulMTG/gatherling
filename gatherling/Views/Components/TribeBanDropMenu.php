<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class TribeBanDropMenu extends DropMenu
{
    public function __construct(Format $format, string $name)
    {
        $allTribes = Format::getTribesList();
        if ($name === 'tribeban') {
            $bannedTribes = $format->getTribesBanned();
            $desc = 'Tribe';
        } elseif ($name === 'subtypeban') {
            $bannedTribes = $format->getSubTypesBanned();
            $desc = 'Subtype';
        } else {
            throw new \InvalidArgumentException("Invalid name for tribe ban drop menu: $name");
        }

        $tribes = array_diff($allTribes, $bannedTribes); // remove tribes banned from drop menu
        $options = [];
        foreach ($tribes as $tribe) {
            $options[] = [
                'value' => $tribe,
                'text' => $tribe,
            ];
        }
        parent::__construct($name, $options, "- $desc to Ban -");
    }
}
