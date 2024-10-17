<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use InvalidArgumentException;

class Placing extends Component
{
    public Medal $medal;
    public string $placing;
    public bool $showPlayer;
    public string $day;
    public string $eventName;
    public ?PlayerLink $playerLink = null;
    public string $eventLink = '';
    public string $recordString;

    public function __construct(Event $event, Deck $deck)
    {
        parent::__construct('partials/placing');
        if (!$deck->playername) {
            throw new InvalidArgumentException('Deck player name is required');
        }
        $this->medal = new Medal($deck->medal ?? 'dot');
        if ($deck->medal == '1st') {
            $this->placing = '1st';
        } elseif ($deck->medal == '2nd') {
            $this->placing = '2nd';
        } elseif ($deck->medal == 't4') {
            $this->placing = 'Top 4';
        } elseif ($deck->medal == 't8') {
            $this->placing = 'Top 8';
        } else {
            $this->placing = 'Played';
        }
        $this->showPlayer = $deck->playername != null;
        if ($this->showPlayer) {
            $deckplayer = new Player($deck->playername);
            $this->playerLink = new PlayerLink($deckplayer);
            $targetUrl = 'eventreport';
            $player = Player::loginName();
            if ($player && $event->authCheck($player)) {
                $targetUrl = 'event';
            }
            $this->eventLink = $targetUrl . '.php?event=' . rawurlencode($deck->eventname ?? '');
            $startTime = $event->start ? strtotime($event->start) : null;
            $this->day = $startTime ? date('F j, Y', $startTime) : '';
            $this->eventName = $deck->eventname ?? '';
        }
        $this->recordString = $deck->recordString();
    }
}
