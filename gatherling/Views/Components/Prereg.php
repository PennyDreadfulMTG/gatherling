<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class Prereg extends Component
{
    public ?string $eventName;
    public bool $showSignIn = false;
    public bool $showRegistered = false;
    public ?string $unregisterLink;
    public bool $showFull = false;
    public bool $showRegister = false;
    public ?string $registerLink;
    public ?Time $start;

    public function __construct(public Event $event)
    {
        parent::__construct('partials/prereg');
        $player = Player::getSessionPlayer();

        $this->eventName = $event->name;
        if (is_null($player)) {
            $this->showSignIn = true;
        } elseif ($event->hasRegistrant($player->name)) {
            $this->showRegistered = true;
            $this->unregisterLink = 'prereg.php?action=unreg&event=' . rawurlencode($event->name);
        } elseif ($event->is_full()) {
            $this->showFull = true;
        } else {
            $this->showRegister = true;
            $this->registerLink = 'prereg.php?action=reg&event=' . rawurlencode($event->name);
        }
        if ($event->start) {
            $this->start = new Time(strtotime($event->start), time(), true);
        }
    }
}
