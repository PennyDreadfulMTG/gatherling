<?php

namespace Gatherling\Pages;

// Abstract class parent for all the pages that need to show the "control panel" set of links.
// Also handles setting event and title properties.
use Gatherling\Event;

abstract class EventFrame extends Page
{
    public string $title = 'Event Host Control Panel';
    public array $event;
    public array $controlPanelLinks;

    public function __construct(Event $event)
    {
        parent::__construct();
        $this->event = getObjectVarsCamelCase($event);
        $this->controlPanelLinks = $this->controlPanelLinks();
    }

    public function controlPanelLinks(): array
    {
        $views = [
            'settings'   => 'Event Settings',
            'reg'        => 'Registration',
            'match'      => 'Match Listing',
            'standings'  => 'Standings',
            'medal'      => 'Medals',
            'points_adj' => 'Season Points Adj.',
            'reports'    => 'Reports',
        ];
        $links = [];
        foreach ($views as $view => $text) {
            $links[] = [
                'href' => 'event.php?name='.rawurlencode($this->event['name']).'&view='.$view,
                'text' => $text,
            ];
        }

        return $links;
    }
}
