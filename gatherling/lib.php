<?php

declare(strict_types=1);

use Gatherling\Auth\Session;
use Gatherling\Models\Player;
use Gatherling\Views\TemplateHelper;

use function Gatherling\Helpers\config;

require_once 'bootstrap.php';

ob_start();

header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (php_sapi_name() !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    Session::start();
}

date_default_timezone_set('US/Eastern'); // force time functions to use US/Eastern time

require_once 'util/time.php';

const MTGO = 1;
const MTGA = 2;
const PAPER = 3;

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

function json_headers(): void
{
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Access-Control-Allow-Origin: *');
    header('HTTP_X_USERNAME: ' . Player::loginName());
}

function git_hash(): string
{
    $hash = config()->string('GIT_HASH', '');
    return substr($hash, 0, 7);
}

function version_tagline(): string
{
    return 'Gatherling version 6.0.2 ("Nixon was normalizing relations with China. I figured that if he could normalize relations, then so could I.")';
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

/**
 * @param string|array<string> $cards
 * @return list<string>
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
