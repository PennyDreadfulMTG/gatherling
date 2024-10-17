<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use InvalidArgumentException;

class MatchupTable extends Component
{
    public bool $canView;
    public bool $hasMatches = false;
    /** @var array<array{isBye: bool, rnd: string, color: string, res: string, playerWins: int|false|null, playerLosses: int|false|null, oppDeckLink: ?DeckLink, opponentLink: ?PlayerLink, showOppDeck: bool}> */
    public array $matches = [];

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/matchupTable');
        if (!$deck->eventname) {
            throw new InvalidArgumentException('Deck event name is required');
        }
        if (!$deck->playername) {
            throw new InvalidArgumentException('Deck player name is required');
        }
        if (!$deck->event_id) {
            throw new InvalidArgumentException('Deck event id is required');
        }
        $event = new Event($deck->eventname);
        $this->canView = $deck->canView(Player::loginName());

        if (!$this->canView) {
            return;
        }

        $matches = $deck->getMatches();

        $this->hasMatches = count($matches) > 0;

        foreach ($matches as $match) {
            $rnd = 'R' . $match->round;
            if ($match->timing > 1 && $match->type == 'Single Elimination') {
                $rnd = 'T' . pow(2, $match->rounds - $match->round + 1);
            }
            $color = '#FF9900';
            $res = 'Draw';
            if ($match->playerMatchInProgress($deck->playername)) {
                $res = 'In Progress';
            }
            if ($match->playerWon($deck->playername)) {
                $color = '#009900';
                $res = 'Win';
            }
            if ($match->playerLost($deck->playername)) {
                $color = '#FF0000';
                $res = 'Loss';
            }
            if ($match->playerBye($deck->playername)) {
                $res = 'Bye';
            }
            $isBye = $res == 'Bye';
            $opp = $oppDeckLink = null;
            $showOppDeck = false;
            if (!$isBye) {
                $oppName = $match->otherPlayer($deck->playername);
                if (!$oppName) {
                    throw new InvalidArgumentException('Opponent name is required');
                }
                $opp = new Player($oppName);
                if (!$event->active && $event->finalized) {
                    $showOppDeck = true;
                    $oppdeck = $opp->getDeckEvent($deck->event_id);
                    if ($oppdeck != null) {
                        $oppDeckLink = new DeckLink($oppdeck);
                    }
                }
            }
            $this->matches[] = [
                'isBye' => $isBye,
                'rnd' => $rnd,
                'color' => $color,
                'res' => $res,
                'playerWins' => $match->getPlayerWins($deck->playername),
                'playerLosses' => $match->getPlayerLosses($deck->playername),
                'oppDeckLink' => $oppDeckLink,
                'opponentLink' => $opp ? new PlayerLink($opp) : null,
                'showOppDeck' => $showOppDeck,
            ];
        }
    }
}
