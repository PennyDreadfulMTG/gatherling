<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class EditAccountsForm extends Component
{
    public TextInput $mtgoUsernameTextInput;
    public TextInput $mtgaUsernameTextInput;

    public function __construct(Player $player)
    {
        $this->mtgoUsernameTextInput = new TextInput('Magic Online', 'mtgo_username', $player->mtgo_username);
        $this->mtgaUsernameTextInput = new TextInput('Magic Arena', 'mtga_username', $player->mtga_username, 0, "Don't forget the 5-digit number!");
    }
}
