<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Matchup;
use InvalidArgumentException;

class SubmitResultForm extends Component
{
    public string $letter;
    public int $currentRound;
    public GameName $opponentGameName;
    public bool $allowsPlayerReportedDraws;
    public bool $isSingleElimination;
    public CheckboxInput $dropCheckboxInput;
    public function __construct(public int $matchId, public bool $drop = false)
    {
        $match = new Matchup($matchId);
        $event = new Event($match->getEventNamebyMatchid());
        $player = Player::getSessionPlayer();
        if (!$player) {
            throw new InvalidArgumentException('Player is not logged in');
        }
        $letter = $match->playerLetter($player->name ?? '');
        if (!$letter) {
            throw new InvalidArgumentException("Player ({$player->name}) is not in this match ({$matchId})");
        }
        $this->letter = $letter;
        $opp = $this->letter == 'a' ? $match->playerb : $match->playera;
        if (!$opp) {
            throw new InvalidArgumentException("Opponent for player ({$player->name}) in match ({$matchId}) is not found");
        }
        $oppPlayer = new Player($opp);
        $this->currentRound = $event->current_round ?? 0;
        $this->opponentGameName = new GameName($oppPlayer, $event->client);
        $this->allowsPlayerReportedDraws = $match->allowsPlayerReportedDraws() === 1;
        $this->isSingleElimination = $match->type === 'Single Elimination';
        $this->dropCheckboxInput = new CheckboxInput('I want to drop from this event', 'drop', $drop);
    }
}
