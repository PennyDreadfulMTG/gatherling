<?php

namespace Gatherling\Views\Components;

class ReportLink extends Component
{
    public string $reportLink;
    public string $text;

    public function __construct(string $name)
    {
        parent::__construct('partials/reportLink');
        $this->reportLink = 'eventreport.php?event=' . rawurlencode($name);
        $this->text = $name;
    }
}
