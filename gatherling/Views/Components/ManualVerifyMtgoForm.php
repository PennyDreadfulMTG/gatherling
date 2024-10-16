<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class ManualVerifyMtgoForm extends Component
{
    public bool $isVerified;
    public function __construct(Player $player)
    {
        parent::__construct('partials/manualVerifyMtgoForm');
        $this->isVerified = $player->verified == 1;
    }
}
