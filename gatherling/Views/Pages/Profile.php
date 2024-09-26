<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Player;
use Gatherling\Views\Components\ProfileEditForm;
use Gatherling\Views\Components\ProfileTable;
use Gatherling\Views\Components\PlayerSearchForm;

class Profile extends Page
{
    public PlayerSearchForm $playerSearchForm;
    public bool $isLoggedOut = false;
    public bool $notFound = false;
    public ?ProfileEditForm $profileEditForm;
    public ?ProfileTable $profileTable;

    public function __construct(public string $playerName, ?Player $player, public int $profileEdit)
    {
        parent::__construct();
        $this->title = 'Player Profile';

        $this->playerSearchForm = new PlayerSearchForm($playerName);

        if (rtrim($playerName) === '') {
            $this->isLoggedOut = true;
            return;
        }
        if (is_null($player)) {
            $this->notFound = true;
            return;
        }
        $this->profileEditForm = $profileEdit == 1 ? new ProfileEditForm($player->timezone, $player->emailAddress, $player->emailPrivacy) : null;
        $this->profileTable = $profileEdit != 1 ? new ProfileTable($player) : null;
    }
}
