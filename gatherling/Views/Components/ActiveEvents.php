<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ActiveEvents extends Component
{
    /** @param array<array{eventName: string, eventLink: string, standingsLink: string, showDiscordRoom: bool, discordChannelName: string, discordGuildName: string, showMtgoRoom: bool, mtgoRoom: string, dropLink: string, joinLink: string, joinLinkText: string, showCreateDeckLink: bool, createDeckLink: ?CreateDeckLink}> $events */
    public function __construct(public array $events)
    {
        parent::__construct('partials/activeEvents');
    }
}
