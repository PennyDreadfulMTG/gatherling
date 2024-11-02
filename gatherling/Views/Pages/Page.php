<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Player;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\TemplateResponse;

use function Gatherling\Helpers\config;

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
    public string $versionTagline;
    public string $jsLink;

    public function __construct(public string $title)
    {
        $this->gitHash = substr(config()->string('GIT_HASH', ''), 0, 7);
        $this->cssLink = 'styles/css/stylesheet.css?v=' . rawurlencode($this->gitHash);
        $this->headerLogoSrc = 'styles/images/header_logo.png';
        $this->player = Player::getSessionPlayer() ?? null;
        $this->isHost = $this->player?->isHost() ?? false;
        $this->isOrganizer = count($this->player?->organizersSeries() ?? []) > 0;
        $this->isSuper = $this->player?->isSuper() ?? false;
        $this->versionTagline = $this->version();
        $this->jsLink = 'gatherling.js?v=' . rawurlencode($this->gitHash);
    }

    public function body(): string
    {
        $this->contentSafe = $this->render();
        return TemplateHelper::render('page', $this);
    }

    private function version(): string
    {
        return 'Gatherling version 6.0.4 ("One thing\'s for sure – we\'re all gonna be a lot thinner.")';
        // return 'Gatherling version 6.0.3 ("Nothing is so painful to the human mind as a great and sudden change.")';
        // return 'Gatherling version 6.0.2 ("Nixon was normalizing relations with China. I figured that if he could normalize relations, then so could I.")';
        // return 'Gatherling version 6.0.1 ("A guilty system recognizes no innocents.")';
        // return 'Gatherling version 6.0.0 ("A foolish consistency is the hobgoblin of little minds")';
        // return 'Gatherling version 5.2.0 ("I mustache you a question...")';
        // return 'Gatherling version 5.1.0 ("Have no fear of perfection – you’ll never reach it.")';
        // 'Gatherling version 5.0.1 ("No rest. No mercy. No matter what.")';
        // 'Gatherling version 5.0.0 ("Hulk, no! Just for once in your life, don\'t smash!")';
        // 'Gatherling version 4.9.0 ("Where we’re going, we don’t need roads")';
        // 'Gatherling version 4.8.8 ("Fish fingers and custard")';
        // 'Gatherling version 4.8.7 ("Step 7: Steal a bagel.")';
        // 'Gatherling version 4.8.6.1 ("I\'m gonna steal the declaration of independence.")';
        // 'Gatherling version 4.8.6 ("I\'m gonna steal the declaration of independence.")';
        // 'Gatherling version 4.8.5 ("That\'s my secret, Captain: I\'m always angry...")';
        // 'Gatherling version 4.8.4 ("It doesn\'t look like anything to me.")';
        // 'Gatherling version 4.8.3 ("These violent delights have violent ends.")';
        // "Gatherling version 4.8.2 (\"Zagreus taking time apart. / Zagreus fears the hero heart. / Zagreus seeks the final part. / The reward that he is reaping..\")";
        // "Gatherling version 4.8.1 (\"Zagreus at the end of days / Zagreus lies all other ways / Zagreus comes when time's a maze / And all of history is weeping.\")";
        // "Gatherling version 4.8.0 (\"Zagreus sits inside your head / Zagreus lives among the dead / Zagreus sees you in your bed / And eats you when you're sleeping.\")";
        // "Gatherling version 4.7.0 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
        // "Gatherling version 4.5.2 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
        // "Gatherling version 4.0.0 (\"Call me old fashioned, but, if you really wanted peace, couldn't you just STOP FIGHTING?\")";
        // "Gatherling version 3.3.0 (\"Do not offend the Chair Leg of Truth. It is wise and terrible.\")";
        // "Gatherling version 2.1.27PK (\"Please give us a simple answer, so that we don't have to think, because if we think, we might find answers that don't fit the way we want the world to be.\")";
        // "Gatherling version 2.1.26PK (\"The program wasn't designed to alter the past. It was designed to affect the future.\")";
        // "Gatherling version 2.0.6 (\"We stole the Statue of Liberty! ...  The small one, from Las Vegas.\")";
        // "Gatherling version 2.0.5 (\"No, that's perfectly normal paranoia. Everyone in the universe gets that.\")";
        // "Gatherling version 2.0.4 (\"This is no time to talk about time. We don't have the time!\")";
        // "Gatherling version 2.0.3 (\"Are you hungry? I haven't eaten since later this afternoon.\")";
        // "Gatherling version 2.0.2 (\"Woah lady, I only speak two languages, English and bad English.\")";
        // "Gatherling version 2.0.1 (\"Use this to defend yourself. It's a powerful weapon.\")";
        // "Gatherling version 2.0.0 (\"I'm here to keep you safe, Sam.  I want to help you.\")";
        // "Gatherling version 1.9.9 (\"You'd think they'd never seen a girl and a cat on a broom before\")";
        // "Gatherling version 1.9.8 (\"I'm tellin' you, man, every third blink is slower.\")";
        // "Gatherling version 1.9.7 (\"Try blue, it's the new red!\")";
        // "Gatherling version 1.9.6 (\"Just relax and let your mind go blank. That shouldn't be too hard for you.\")";
        // "Gatherling version 1.9.5 (\"The grade that you receive will be your last, WE SWEAR!\")";
        // "Gatherling version 1.9.4 (\"We're gonna need some more FBI guys, I guess.\")";
        // "Gatherling version 1.9.3 (\"This is the Ocean, silly, we're not the only two in here.\")";
        // "Gatherling version 1.9.2 (\"So now you're the boss. You're the King of Bob.\")";
        // "Gatherling version 1.9.1 (\"It's the United States of Don't Touch That Thing Right in Front of You.\")";
        // "Gatherling version 1.9 (\"It's funny 'cause the squirrel got dead\")";
    }
}
