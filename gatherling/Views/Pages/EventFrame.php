<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

// Abstract class parent for all the pages that need to show the "control panel" set of links.
// Also handles setting event and title properties.
use Gatherling\Models\Event;

abstract class EventFrame extends Page
{
    public string $title = 'Event Host Control Panel';
    /** @var array<string, mixed> */
    public array $event;
    /** @var list<array{link: string, text: string}> */
    public array $controlPanelLinks;

    public function __construct(Event $event)
    {
        parent::__construct();
        $this->event = getObjectVarsCamelCase($event);
        $this->controlPanelLinks = $this->getControlPanelLinks($event);
    }

    /** @return list<array{link: string, text: string}> */
    public function getControlPanelLinks(Event $event): array
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
                'link' => 'event.php?name=' . rawurlencode($event->name ?? '') . '&view=' . $view,
                'text' => $text,
            ];
        }

        return $links;
    }
}
