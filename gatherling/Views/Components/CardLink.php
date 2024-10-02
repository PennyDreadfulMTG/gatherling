<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class CardLink extends Component
{
    public string $gathererLink;
    public string $imageSrc;

    public function __construct(public string $cardName)
    {
        parent::__construct('partials/cardLink');
        $gathererName = preg_replace('/ /', ']+[', $cardName);
        $gathererName = str_replace('/', ']+[', $gathererName);
        $this->gathererLink = 'https://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[' . rawurlencode($gathererName) . ']';
        $this->imageSrc = 'https://gatherer.wizards.com/Handlers/Image.ashx?name=' . rawurlencode($cardName) . '&type=card';
    }
}
