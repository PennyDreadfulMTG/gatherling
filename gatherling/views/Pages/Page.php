<?php

namespace Gatherling\Views\Pages;

use Gatherling\Models\Player;
use Gatherling\Views\TemplateHelper;

abstract class Page
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

    public function __construct()
    {
        global $CONFIG;

        $this->siteName = $CONFIG['site_name'];
        $this->gitHash = git_hash();
        $this->cssLink = theme_file('css/stylesheet.css') . '?v=' . rawurlencode($this->gitHash);
        $this->headerLogoSrc = theme_file('images/header_logo.png');
        $this->player = Player::getSessionPlayer() ?? null;
        $this->isHost = $this->player?->isHost() ?? false;
        $this->isOrganizer = count($this->player?->organizersSeries() ?? []) > 0;
        $this->isSuper = $this->player?->isSuper() ?? false;
        $this->tabs = 5 + (int) $this->isHost + (int) $this->isOrganizer + (int) $this->isSuper;
        $this->versionTagline = version_tagline();
    }

    public function template(): string
    {
        $className = get_called_class();
        $baseName = ($pos = strrpos($className, '\\')) !== false ? substr($className, $pos + 1) : $className;

        return lcfirst($baseName);
    }

    public function render(): string
    {
        $this->contentSafe = TemplateHelper::render($this->template(), $this);
        return TemplateHelper::render('page', $this);
    }
}
