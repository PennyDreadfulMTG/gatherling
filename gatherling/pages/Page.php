<?php

namespace Gatherling\Pages;

use Gatherling\Player;

abstract class Page {
    public bool $enableVue = false;
    public string $contentSafe;
    public string $css;
    public string $gitHash;
    public string $headerLogoImg;
    public bool $includeSorttable;
    public bool $isHost;
    public bool $isOrganizer;
    public bool $isSuper;
    public string $js;
    public Player $player;
    public string $siteName;
    public string $tabs;
    public string $title;
    public string $versionTagline;

    public function __construct() {
        global $CONFIG;

        $this->siteName = $CONFIG['site_name'];
        $this->css = theme_file('css/stylesheet.css');
        $this->gitHash = git_hash();
        $this->headerLogoImg = theme_file('images/header_logo.png');
        $this->player = Player::getSessionPlayer();
        $this->isHost = $this->player->isHost();
        $this->isOrganizer = count($this->player->organizersSeries()) > 0;
        $this->isSuper = $this->player->isSuper();
        $this->tabs = 5 + (int)$this->isHost + (int)$this->isOrganizer + (int)$this->isSuper;
        $this->versionTagline = version_tagline();
    }

    public function template(): string {
        $className = get_called_class();
        $baseName = ($pos = strrpos($className, '\\')) !== false ? substr($className, $pos + 1) : $className;
        return lcfirst($baseName);
    }

    public function render(): string {
        $this->contentSafe = renderTemplate($this->template(), $this);
        return renderTemplate('page', $this);
    }
}
