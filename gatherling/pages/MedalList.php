<?php

namespace Gatherling\Pages;

use Gatherling\Event;

class MedalList extends EventFrame
{
    public array $finalists;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $finalists = $event->getFinalists();
        $pos = 0;
        foreach ($finalists as &$finalist) {
            $finalist['playerDropMenu'] = playerDropMenuArgs($event, "$pos", $finalist['player']);
            $finalist['img'] = theme_file("images/{$finalist['medal']}.png");
            $pos++;
        }
        $this->finalists = $finalists;
    }
}
