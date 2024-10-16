<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class VerifyMtgoForm extends Component
{
    public bool $isVerified;
    public string $playerName;

    public function __construct(Player $player, public string $infobotPrefix)
    {
        parent::__construct('partials/verifyMtgoForm');

        $this->isVerified = $player->verified == 1;
        $this->playerName = $player->name ?? '';
    }
}
