<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Models\Series;

class PlayerBanForm extends Component
{
    public string $seriesName;
    /** @var array<array{playerName: string, addedDate: ?string, reasonBanned: ?string, isCurrentPlayer: bool}> */
    public array $bannedPlayers;

    public function __construct(public Series $series)
    {
        $this->seriesName = $series->name;
        $currentPlayerName = Player::loginName();
        foreach ($series->bannedplayers as $bannedPlayerName) {
            $this->bannedPlayers[] = [
                'playerName' => $bannedPlayerName,
                'addedDate' => $series->getBannedPlayerDate($bannedPlayerName),
                'reasonBanned' => $series->getBannedPlayerReason($bannedPlayerName),
                'isCurrentPlayer' => $bannedPlayerName == $currentPlayerName,
            ];
        }
    }
}
