<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\SetScraper;

class AddCardSetForm extends Component
{
    public FileInput $cardSetFileInput;
    public Submit $submitButton;
    public SelectInput $missingSetsSelectInput;

    public function __construct()
    {
        $this->cardSetFileInput = new FileInput('Cardset JSON', 'cardsetfile');
        $this->submitButton = new Submit('Install New Cardset');
        $missingSets = SetScraper::getSetList();
        $this->missingSetsSelectInput = new SelectInput('Missing Sets', 'cardsetcode', $missingSets);
    }
}
