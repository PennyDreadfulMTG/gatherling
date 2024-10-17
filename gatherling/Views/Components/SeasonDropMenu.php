<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Database;

class SeasonDropMenu extends NumDropMenu
{
    public function __construct(int|string|null $season, ?string $default = '- Season - ')
    {
        $db = Database::getConnection();
        $query = 'SELECT MAX(season) AS m FROM events';
        $result = $db->query($query) or exit($db->error);
        $maxArr = $result->fetch_assoc();
        $max = $maxArr['m'];
        $result->close();

        parent::__construct('season', $default, max(10, $max + 1), $season);
    }
}
