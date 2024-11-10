<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class EditEmailForm extends Component
{
    public string $emailAddress;
    public EmailStatusDropMenu $emailStatusDropMenu;

    public function __construct(Player $player)
    {
        $this->emailAddress = $player->emailAddress ?? '';
        $this->emailStatusDropMenu = new EmailStatusDropMenu($player->emailPrivacy);
    }
}
