<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\SetScraper;

class AddCardSetForm extends Component
{
    public FileInput $cardSetFileInput;
    public Submit $submitButton;
    public SelectInput $missingSetSelectInput;

    public function __construct()
    {
        parent::__construct('partials/addCardSetForm');
        $this->cardSetFileInput = new FileInput('Cardset JSON', 'cardsetfile');
        $this->submitButton = new Submit('Install New Cardset');
        $missingSets = SetScraper::getSetList();
        $this->missingSetSelectInput = new SelectInput('Missing Sets', 'cardsetcode', $missingSets);
    }
}
