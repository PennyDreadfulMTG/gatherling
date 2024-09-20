<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Database;

class SeasonDropMenu extends NumDropMenu
{
    public function __construct(int|string|null $season, bool $useAll = false)
    {
        $db = Database::getConnection();
        $query = 'SELECT MAX(season) AS m FROM events';
        $result = $db->query($query) or exit($db->error);
        $maxArr = $result->fetch_assoc();
        $max = $maxArr['m'];
        $title = $useAll ? '- Season - ' : 'All';
        $result->close();

        parent::__construct('season', $title, max(10, $max + 1), $season);
    }
}
