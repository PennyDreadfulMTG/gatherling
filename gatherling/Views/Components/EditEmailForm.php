<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class EditEmailForm extends Component
{
    public string $emailAddress;
    public int $emailPrivacy;

    public function __construct(Player $player)
    {
        parent::__construct('partials/editEmailForm');
        $this->emailAddress = $player->emailAddress ?? '';
        $this->emailPrivacy = $player->emailPrivacy ?? 1;
    }
}
