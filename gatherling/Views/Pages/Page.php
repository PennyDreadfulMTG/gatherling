<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Player;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\TemplateResponse;

abstract class Page extends TemplateResponse
{
    public bool $enableVue = false;
    public string $contentSafe;
    public string $gitHash;
    public string $cssLink;
    public string $headerLogoSrc;
    public bool $includeSorttable;
    public bool $isHost;
    public bool $isOrganizer;
    public bool $isSuper;
    public string $js;
    public ?Player $player;
    public string $siteName;
    public int $tabs;
    public string $title;
    public string $versionTagline;
    public string $jsLink;

    public function __construct()
    {
        global $CONFIG;

        $this->siteName = $CONFIG['site_name'];
        $this->gitHash = git_hash();
        $this->cssLink = 'styles/css/stylesheet.css?v=' . rawurlencode($this->gitHash);
        $this->headerLogoSrc = 'styles/images/header_logo.png';
        $this->player = Player::getSessionPlayer() ?? null;
        $this->isHost = $this->player?->isHost() ?? false;
        $this->isOrganizer = count($this->player?->organizersSeries() ?? []) > 0;
        $this->isSuper = $this->player?->isSuper() ?? false;
        $this->tabs = 5 + (int) $this->isHost + (int) $this->isOrganizer + (int) $this->isSuper;
        $this->versionTagline = version_tagline();
        $this->jsLink = 'gatherling.js?v=' . rawurlencode(git_hash());
    }

    public function body(): string
    {
        $this->contentSafe = $this->render();
        return TemplateHelper::render('page', $this);
    }
}
