<?php

declare(strict_types=1);


namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;

class MedalList extends EventFrame
{
    public array $finalists;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $finalists = $event->getFinalists();
        $pos = 0;
        foreach ($finalists as &$finalist) {
            $finalist['playerDropMenu'] = EventHelper::playerDropMenuArgs($event, "$pos", $finalist['player']);
            $finalist['src'] = "styles/images/{$finalist['medal']}.png";
            $pos++;
        }
        $this->finalists = $finalists;
    }
}
