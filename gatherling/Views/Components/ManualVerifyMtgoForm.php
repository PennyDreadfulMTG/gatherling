<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class ManualVerifyMtgoForm extends Component
{
    public bool $isVerified;
    public function __construct(Player $player)
    {
        $this->isVerified = $player->verified == 1;
    }
}
