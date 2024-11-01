<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class EventReportLink extends Component
{
    public string $eventReportLink;
    public string $text;

    public function __construct(string $name)
    {
        $this->eventReportLink = 'eventreport.php?event=' . rawurlencode($name);
        $this->text = $name;
    }
}
