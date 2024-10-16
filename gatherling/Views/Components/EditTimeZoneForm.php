<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class EditTimeZoneForm extends Component
{
    public string $playerName;
    public string $timeZone;
    public TimeZoneDropMenu $timeZoneDropMenu;

    public function __construct(Player $player)
    {
        parent::__construct('partials/editTimeZoneForm');

        $this->playerName = $player->name ?? '';
        $this->timeZone = $player->timeZone() ?? '';
        $this->timeZoneDropMenu = new TimeZoneDropMenu($player->timezone);
    }
}
