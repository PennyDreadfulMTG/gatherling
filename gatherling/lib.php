<?php

declare(strict_types=1);

use Gatherling\Auth\Session;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\Components\CardLink;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\OrganizerSelect;
use Gatherling\Views\Components\EmailStatusDropDown;

require_once 'bootstrap.php';
ob_start();
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (php_sapi_name() !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    Session::start();
}

$HC = '#DDDDDD';
$R1 = '#EEEEEE';
$R2 = '#FFFFFF';
$CC = $R1;
date_default_timezone_set('US/Eastern'); // force time functions to use US/Eastern time

require_once 'util/time.php';

const MTGO = 1;
const MTGA = 2;
const PAPER = 3;

function page(string $title, string $contents): string
{
    ob_start();
    print_header($title);
    echo $contents;
    print_footer();

    return ob_get_clean();
}

function print_header(string $title, bool $enable_vue = false): void
{
    global $CONFIG;

    $player = Player::getSessionPlayer();
    if (!$player) {
        $isHost = $isOrganizer = $isSuper = false;
    } else {
        $isSuper = $player->isSuper();
        $isHost = $isSuper || $player->isHost();
        $isOrganizer = count($player->organizersSeries()) > 0;
    }

    echo TemplateHelper::render('partials/header', [
        'siteName' => $CONFIG['site_name'],
        'title' => $title,
        'cssLink' => 'styles/css/stylesheet.css?v=' . rawurlencode(git_hash()),
        'enableVue' => $enable_vue,
        'gitHash' => git_hash(),
        'headerLogoSrc' => 'styles/images/header_logo.png',
        'player' => $player,
        'isHost' => $isHost,
        'isOrganizer' => $isOrganizer,
        'isSuper' => $isSuper,
        'versionTagline' => version_tagline(),
    ]);
}

function print_footer(): void
{
    echo TemplateHelper::render('partials/footer', [
        'versionTagline' => version_tagline(),
        'gitHash' => git_hash(),
        'jsLink' => 'gatherling.js?v=' . rawurlencode(git_hash()),
    ]);
}

function headerColor(): string
{
    global $HC, $CC, $R1, $R2;
    $CC = $R2;

    return $HC;
}

function linkToLogin(string $_pagename = null, ?string $redirect = null, ?string $message = null, ?string $username = null): void
{
    (new LoginRedirect($redirect ?? '', $message ?? '', $username ?? ''))->send();
}

function printCardLink(string $card): string
{
    return (new CardLink($card))->render();
}

/** @param array<string, string|int> $extra_attr */
function image_tag(string $filename, ?array $extra_attr = null): string
{
    $tag = '<img ';
    if (is_array($extra_attr)) {
        foreach ($extra_attr as $key => $value) {
            $tag .= "{$key}=\"{$value}\" ";
        }
    }
    $tag .= 'src="styles/images/' . rawurlencode($filename) . '" />';

    return $tag;
}

function medalImgStr(string $medal): string
{
    return image_tag("$medal.png", ['style' => 'border-width: 0px']);
}

function seasonDropMenu(int|string|null $season, bool $useall = false): string
{
    return (new SeasonDropMenu($season, $useall))->render();
}

function formatDropMenu(?string $format, bool $useAll = false, string $formName = 'format'): string
{
    return (new FormatDropMenu($format, $useAll, $formName))->render();
}

function emailStatusDropDown(int $currentStatus = 1): string
{
    return (new EmailStatusDropDown($currentStatus))->render();
}

function timeDropMenu(int|string $hour, int|string $minutes = 0): string
{
    $args = timeDropMenuArgs($hour, $minutes);

    return TemplateHelper::render('partials/dropMenu', $args);
}

/** @return array<string, string|array<string, string|bool|null>> */
function timeDropMenuArgs(int|string $hour, int|string $minutes = 0): array
{
    if (strcmp($hour, '') == 0) {
        $hour = -1;
    }
    $options = [];
    for ($h = 0; $h < 24; $h++) {
        for ($m = 0; $m < 60; $m += 30) {
            $hstring = $h;
            if ($m == 0) {
                $mstring = ':00';
            } else {
                $mstring = ":$m";
            }
            if ($h == 0) {
                $hstring = '12';
            }
            $apstring = ' AM';
            if ($h >= 12) {
                $hstring = $h != 12 ? $h - 12 : $h;
                $apstring = ' PM';
            }
            if ($h == 0 && $m == 0) {
                $hstring = 'Midnight';
                $mstring = '';
                $apstring = '';
            } elseif ($h == 12 && $m == 0) {
                $hstring = 'Noon';
                $mstring = '';
                $apstring = '';
            }
            $options[] = [
                'value'      => "$h:$m",
                'text'       => "$hstring$mstring$apstring",
                'isSelected' => $hour == $h && $minutes == $m,
            ];
        }
    }

    return [
        'name'    => 'hour',
        'default' => '- Hour -',
        'options' => $options,
    ];
}

function json_headers(): void
{
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Access-Control-Allow-Origin: *');
    header('HTTP_X_USERNAME: '.Player::loginName());
}

/** @param array<string> $player_series */
function printOrganizerSelect(array $player_series, string $selected): string
{
    return (new OrganizerSelect($_SERVER['PHP_SELF'], $player_series, $selected))->render();
}

function git_hash(): string
{
    global $CONFIG;
    if (!is_null($hash = $CONFIG['GIT_HASH'])) {
        return substr($hash, 0, 7);
    }
    return '';
}

function version_tagline(): string
{
    return 'Gatherling version 5.2.0 ("I mustache you a question...")';
    // return 'Gatherling version 5.1.0 ("Have no fear of perfection – you’ll never reach it.")';
    // echo 'Gatherling version 5.0.1 ("No rest. No mercy. No matter what.")';
    // echo 'Gatherling version 5.0.0 ("Hulk, no! Just for once in your life, don\'t smash!")';
    // echo 'Gatherling version 4.9.0 ("Where we’re going, we don’t need roads")';
    // echo 'Gatherling version 4.8.8 ("Fish fingers and custard")';
    // echo 'Gatherling version 4.8.7 ("Step 7: Steal a bagel.")';
    // echo 'Gatherling version 4.8.6.1 ("I\'m gonna steal the declaration of independence.")';
    // echo 'Gatherling version 4.8.6 ("I\'m gonna steal the declaration of independence.")';
    // echo 'Gatherling version 4.8.5 ("That\'s my secret, Captain: I\'m always angry...")';
    // echo 'Gatherling version 4.8.4 ("It doesn\'t look like anything to me.")';
    // echo 'Gatherling version 4.8.3 ("These violent delights have violent ends.")';
    // print "Gatherling version 4.8.2 (\"Zagreus taking time apart. / Zagreus fears the hero heart. / Zagreus seeks the final part. / The reward that he is reaping..\")";
    // print "Gatherling version 4.8.1 (\"Zagreus at the end of days / Zagreus lies all other ways / Zagreus comes when time's a maze / And all of history is weeping.\")";
    // print "Gatherling version 4.8.0 (\"Zagreus sits inside your head / Zagreus lives among the dead / Zagreus sees you in your bed / And eats you when you're sleeping.\")";
    // print "Gatherling version 4.7.0 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
    // print "Gatherling version 4.5.2 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
    // print "Gatherling version 4.0.0 (\"Call me old fashioned, but, if you really wanted peace, couldn't you just STOP FIGHTING?\")";
    // print "Gatherling version 3.3.0 (\"Do not offend the Chair Leg of Truth. It is wise and terrible.\")";
    // print "Gatherling version 2.1.27PK (\"Please give us a simple answer, so that we don't have to think, because if we think, we might find answers that don't fit the way we want the world to be.\")";
    // print "Gatherling version 2.1.26PK (\"The program wasn't designed to alter the past. It was designed to affect the future.\")";
    // print "Gatherling version 2.0.6 (\"We stole the Statue of Liberty! ...  The small one, from Las Vegas.\")";
    // print "Gatherling version 2.0.5 (\"No, that's perfectly normal paranoia. Everyone in the universe gets that.\")";
    // print "Gatherling version 2.0.4 (\"This is no time to talk about time. We don't have the time!\")";
    // print "Gatherling version 2.0.3 (\"Are you hungry? I haven't eaten since later this afternoon.\")";
    // print "Gatherling version 2.0.2 (\"Woah lady, I only speak two languages, English and bad English.\")";
    // print "Gatherling version 2.0.1 (\"Use this to defend yourself. It's a powerful weapon.\")";
    // print "Gatherling version 2.0.0 (\"I'm here to keep you safe, Sam.  I want to help you.\")";
    // print "Gatherling version 1.9.9 (\"You'd think they'd never seen a girl and a cat on a broom before\")";
    // print "Gatherling version 1.9.8 (\"I'm tellin' you, man, every third blink is slower.\")";
    // print "Gatherling version 1.9.7 (\"Try blue, it's the new red!\")";
    // print "Gatherling version 1.9.6 (\"Just relax and let your mind go blank. That shouldn't be too hard for you.\")";
    // print "Gatherling version 1.9.5 (\"The grade that you receive will be your last, WE SWEAR!\")";
    // print "Gatherling version 1.9.4 (\"We're gonna need some more FBI guys, I guess.\")";
    // print "Gatherling version 1.9.3 (\"This is the Ocean, silly, we're not the only two in here.\")";
    // print "Gatherling version 1.9.2 (\"So now you're the boss. You're the King of Bob.\")";
    // print "Gatherling version 1.9.1 (\"It's the United States of Don't Touch That Thing Right in Front of You.\")";
    // print "Gatherling version 1.9 (\"It's funny 'cause the squirrel got dead\")";
}

function redirect(string $page): void
{
    global $CONFIG;
    header("Location: {$CONFIG['base_url']}{$page}");
    exit(0);
}

/**
 * @param string|array<string> $cards
 * @return array<string>
 */
function parseCards(string|array $cards): array
{
    $cardarr = [];
    if (!is_array($cards)) {
        $cards = explode("\n", $cards);
    }
    foreach ($cards as $card) {
        // AE Litigation
        $card = normaliseCardName($card);
        if ($card != '') {
            $cardarr[] = $card;
        }
    }
    return $cardarr;
}

function normaliseCardName(string $card, bool $toLower = false): string
{
    $pattern = ['/é/', '/è/', '/ë/', '/ê/', '/É/', '/È/', '/Ë/', '/Ê/', '/á/', '/à/', '/ä/', '/â/', '/å/', '/Á/', '/À/', '/Ä/', '/Â/', '/Å/', '/ó/', '/ò/', '/ö/', '/ô/', '/Ó/', '/Ò/', '/Ö/', '/Ô/', '/í/', '/ì/', '/ï/', '/î/', '/Í/', '/Ì/', '/Ï/', '/Î/', '/ú/', '/ù/', '/ü/', '/û/', '/Ú/', '/Ù/', '/Ü/', '/Û/', '/ý/', '/ÿ/', '/Ý/', '/ø/', '/Ø/', '/œ/', '/Œ/', '/Æ/', '/AE/', '/ç/', '/Ç/', '/—/', '/−/', '/â€”/', '/’/', '/½/'];
    $replace = ['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'ae', 'ae', 'Ae', 'Ae', 'c', 'C', '-', '-', '-', "'", '{1/2}'];
    $card = preg_replace($pattern, $replace, $card);
    $card = preg_replace("/\306/", 'AE', $card);
    $card = preg_replace("/ \/\/\/? /", '/', $card);
    if ($toLower) {
        $card = strtolower($card);
    }

    return trim($card);
}

/**
 * @param string|array<string> $cards
 * @return array<string, int>
 */
function parseCardsWithQuantity(string|array $cards): array
{
    $cards = parseCards($cards);
    $cardarr = [];
    foreach ($cards as $line) {
        $chopped = rtrim($line);
        if (preg_match("/^[ \t]*([0-9]+)x?[ \t]+(.*?)( \(\w+\) \d+)?$/i", $chopped, $m)) {
            $qty = $m[1];
            $card = rtrim($m[2]);
            if (isset($cardarr[$card])) {
                $cardarr[$card] += $qty;
            } else {
                $cardarr[$card] = $qty;
            }
        }
    }

    return $cardarr;
}

// Our standard template variable naming is camelCase.
// Some of our objects have properties named in snake_case.
// So when we grab the values from an object to pass into
// a template with get_object_vars let's also preserve the
// naming standard by transforming the case.
/** @return array<string, mixed> */
function getObjectVarsCamelCase(object $obj): array
{
    $vars = get_object_vars($obj);
    return arrayMapRecursive(fn($key) => is_string($key) ? toCamel($key) : $key, $vars);
}

// https://stackoverflow.com/a/45440841/375262
function toCamel(string $string): string
{
    // Convert to ASCII, remove apostrophes, and split into words
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = str_replace("'", "", $string);
    $words = preg_split('/[^a-zA-Z0-9]+/', $string);

    // Convert each word to camel case
    $camelCase = array_map(function ($word) {
        // Split words that are already in camel case
        $word = preg_replace('/(?<=\p{Ll})(?=\p{Lu})/u', ' ', $word);
        $word = preg_replace('/(?<=\p{Lu})(?=\p{Lu}\p{Ll})/u', ' ', $word);
        $subWords = explode(' ', $word);

        // Lowercase each subword
        $subWords = array_map('strtolower', $subWords);
        // Capitalize each subword
        $subWords = array_map('ucfirst', $subWords);

        return implode('', $subWords);
    }, $words);

    // Join words and lowercase the first character
    $result = implode('', $camelCase);
    $result = lcfirst($result);
    return $result;
}

/**
 * @param array<string, mixed> $arr
 * @return array<string, mixed>
 */
function arrayMapRecursive(callable $func, array $arr): array
{
    $result = [];

    foreach ($arr as $key => $value) {
        $newKey = $func($key);

        if (is_array($value)) {
            $result[$newKey] = arrayMapRecursive($func, $value);
        } elseif (is_object($value)) {
            $result[$newKey] = getObjectVarsCamelCase($value);
        } else {
            $result[$newKey] = $value;
        }
    }

    return $result;
}
