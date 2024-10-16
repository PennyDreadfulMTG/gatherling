<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ReportLink extends Component
{
    public string $reportLink;
    public string $text;

    public function __construct(string $name)
    {
        $this->reportLink = 'eventreport.php?event=' . rawurlencode($name);
        $this->text = $name;
    }
}
