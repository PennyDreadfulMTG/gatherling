<?php

use Gatherling\Auth\Session;
use Gatherling\Models\Database;
use Gatherling\Models\Format;
use Gatherling\Models\Player;

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

function page($title, $contents): string
{
    ob_start();
    print_header($title);
    echo $contents;
    print_footer();

    return ob_get_clean();
}

function renderTemplate(string $template_name, array|object $context = []): string
{
    $m = new Mustache_Engine([
        'cache'            => '/tmp/gatherling/mustache/templates',
        'loader'           => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
        'partials_loader'  => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views/partials'),
        'entity_flags'     => ENT_QUOTES,
        'strict_callables' => true,
    ]);

    return $m->render($template_name, $context);
}

/** Gets the correct name, relative to the gatherling root dir, for a file in the theme.
 *  Allows for overrides, falls back to default/.
 */
function theme_file($name): string
{
    global $CONFIG;
    if (Player::isLoggedIn()) {
        $user_dir = 'styles/'.Player::getSessionPlayer()->theme.'/';
        if (file_exists($user_dir.$name)) {
            return $user_dir.$name;
        }
    }
    $theme_dir = "styles/{$CONFIG['style']}/";
    $default_dir = 'styles/Chandra/';
    if (file_exists($theme_dir.$name)) {
        return $theme_dir.$name;
    }

    return $default_dir.$name;
}

function print_header($title, $enable_vue = false): void
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

    echo renderTemplate('partials/header', [
        'siteName'       => $CONFIG['site_name'],
        'title'          => $title,
        'cssLink'        => theme_file('css/stylesheet.css') . '?v=' . rawurlencode(git_hash()),
        'enableVue'      => $enable_vue,
        'gitHash'        => git_hash(),
        'headerLogoSrc'  => theme_file('images/header_logo.png'),
        'player'         => $player,
        'isHost'         => $isHost,
        'isOrganizer'    => $isOrganizer,
        'isSuper'        => $isSuper,
        'versionTagline' => version_tagline(),
    ]);
}

function print_footer(): void
{
    echo renderTemplate('partials/footer', [
        'versionTagline' => version_tagline(),
        'gitHash'        => git_hash(),
    ]);
}

function headerColor(): string
{
    global $HC, $CC, $R1, $R2;
    $CC = $R2;

    return $HC;
}

function linkToLogin($pagename = null, $redirect = null, $message = null, $username = null): void
{
    if (is_null($redirect)) {
        $redirect = $_SERVER['REQUEST_URI'];
    }
    if (is_null($message)) {
        $message = "You must log in to access $pagename.";
    }
    redirect("login.php?target=$redirect&message=$message&username=$username");

    echo "<div class=\"c error\">$message</div>";
    echo "Please <a href=\"login.php?target={$_SERVER['REQUEST_URI']}\">Click Here</a> to log in.\n";
}

function printCardLink($card): void
{
    $gathererName = preg_replace('/ /', ']+[', $card);
    $gathererName = str_replace('/', ']+[', $gathererName);
    echo '<span class="cardHoverImageWrapper">';
    echo "<a href=\"http://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[{$gathererName}]\" ";
    echo "class=\"linkedCardName\" target=\"_blank\">{$card}<span class=\"linkCardHoverImage\"><p class=\"crop\" style=\"background-image: url(http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"><img alt=\"{$card}\" src=\"http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"></p></span></a></span>";
}

function image_tag($filename, $extra_attr = null): string
{
    $tag = '<img ';
    if (is_array($extra_attr)) {
        foreach ($extra_attr as $key => $value) {
            $tag .= "{$key}=\"{$value}\" ";
        }
    }
    $tag .= 'src="'.theme_file("images/{$filename}").'" />';

    return $tag;
}

function medalImgStr($medal): string
{
    return image_tag("$medal.png", ['style' => 'border-width: 0px']);
}

function seasonDropMenu(int|string|null $season, bool $useall = false): string
{
    $args = seasonDropMenuArgs($season, $useall);

    return renderTemplate('partials/dropMenu', $args);
}

function seasonDropMenuArgs(int|string|null $season, bool $useall = false): array
{
    $db = Database::getConnection();
    $query = 'SELECT MAX(season) AS m FROM events';
    $result = $db->query($query) or exit($db->error);
    $maxArr = $result->fetch_assoc();
    $max = $maxArr['m'];
    $title = ($useall == 0) ? '- Season - ' : 'All';
    $result->close();

    return numDropMenuArgs('season', $title, max(10, $max + 1), $season);
}

function formatDropMenu(?string $format, bool $useAll = false, string $formName = 'format', bool $showMeta = true): string
{
    $args = formatDropMenuArgs($format, $useAll, $formName, $showMeta);

    return renderTemplate('partials/dropMenu', $args);
}

function formatDropMenuArgs(?string $format, bool $useAll = false, string $formName = 'format', bool $showMeta = true): array
{
    $db = Database::getConnection();
    $query = 'SELECT name FROM formats';
    if (!$showMeta) {
        $query .= ' WHERE NOT is_meta_format ';
    }
    $query .= ' ORDER BY priority desc, name';
    $result = $db->query($query) or exit($db->error);
    $formats = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
    $default = $useAll == 0 ? '- Format -' : 'All';
    foreach ($formats as &$f) {
        $f['text'] = $f['value'] = $f['name'];
        $f['isSelected'] = $f['name'] === $format;
    }

    return [
        'name'    => $formName,
        'default' => $default,
        'options' => $formats,
    ];
}

function emailStatusDropDown($currentStatus = 1): void
{
    echo '<select class="inputbox" name="emailstatus">';
    if ($currentStatus == 0) {
        echo '<option value="0" selected>Private</option>';
    } else {
        echo '<option value="0">Private</option>';
    }
    if ($currentStatus == 1) {
        echo '<option value="1" selected>Public</option>';
    } else {
        echo '<option value="1">Public</option>';
    }
    echo '</select>';
}

function numDropMenuArgs(string $field, string $title, int $max, string|int|null $def, int $min = 0, ?string $special = null): array
{
    if ($def && strcmp($def, '') == 0) {
        $def = -1;
    }

    $options = [];
    if ($special) {
        $options[] = [
            'text'       => $special,
            'value'      => 128,
            'isSelected' => $def == 128,
        ];
    }
    for ($n = $min; $n <= $max; $n++) {
        $options[] = [
            'text'       => $n,
            'value'      => $n,
            'isSelected' => $n == $def,
        ];
    }

    return [
        'name'    => $field,
        'default' => $title,
        'options' => $options,
    ];
}

function timeDropMenu(int|string $hour, int|string $minutes = 0): string
{
    $args = timeDropMenuArgs($hour, $minutes);

    return renderTemplate('partials/dropMenu', $args);
}

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

function notAllowed(string $reason): string
{
    $args = notAllowedArgs($reason);

    return renderTemplate('partials/notAllowed', $args);
}

function notAllowedArgs(string $reason): array
{
    return ['reason' => $reason];
}

function tribeBanDropMenu($format): void
{
    $allTribes = Format::getTribesList();
    $bannedTribes = $format->getTribesBanned();
    $tribes = array_diff($allTribes, $bannedTribes); // remove tribes banned from drop menu

    echo '<select class="inputbox" name="tribeban">';
    echo '<option value="Unclassified">- Tribe to Ban - </option>';
    foreach ($tribes as $tribe) {
        echo "<option value=\"$tribe\">$tribe</option>";
    }
    echo '</select>';
}

function subTypeBanDropMenu($format): void
{
    $allSubTypes = Format::getTribesList();
    $bannedSubTypes = $format->getSubTypesBanned();
    $subTypes = array_diff($allSubTypes, $bannedSubTypes); // remove sub types banned from drop menu

    echo '<select class="inputbox" name="subtypeban">';
    echo '<option value="Unclassified">- Subtype to Ban - </option>';
    foreach ($subTypes as $subType) {
        echo "<option value=\"$subType\">$subType</option>";
    }
    echo '</select>';
}

function formatsDropMenu($formatType = '', $seriesName = 'System'): void
{
    $formatNames = [];

    if ($formatType == 'System') {
        $formatNames = Format::getSystemFormats();
    }
    if ($formatType == 'Public') {
        $formatNames = Format::getPublicFormats();
    }
    if ($formatType == 'Private') {
        $formatNames = Format::getPrivateFormats($seriesName);
    }
    if ($formatType == 'Private+') {
        $formatNames = array_merge(
            Format::getSystemFormats(),
            Format::getPublicFormats(),
            Format::getPrivateFormats($seriesName)
        );
    }
    if ($formatType == 'All') {
        $formatNames = Format::getAllFormats();
    }

    echo "<select class=\"inputbox\" name=\"format\" STYLE=\"width: 250px\">\n";
    echo "<option value=\"Unclassified\">- {$formatType} Format Name -</option>\n";
    foreach ($formatNames as $formatName) {
        echo "<option value=\"$formatName\">$formatName</option>\n";
    }
}

function printOrganizerSelect($player_series, $selected): void
{
    $page = $_SERVER['PHP_SELF'];
    echo '<center>';
    echo "<form action=\"$page\" method=\"get\">";
    echo '<select class="inputbox" name="series">';
    foreach ($player_series as $series) {
        echo "<option value=\"{$series}\"";
        if ($series == $selected) {
            echo ' selected';
        }
        echo ">{$series}</option>";
    }
    echo '</select>';
    echo '<input class="inputbutton" type="submit" value="Select Series">';
    echo '</form>';
}

function print_warning_if($conditional): void
{
    if ($conditional) {
        echo '<span style="color: red;">⚠</span>';
    }
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
    return 'Gatherling version 5.1.0 ("Have no fear of perfection – you’ll never reach it.")';
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

function redirect($page): void
{
    global $CONFIG;
    header("Location: {$CONFIG['base_url']}{$page}");
    exit(0);
}

function parseCards($cards): array
{
    if (!is_array($cards)) {
        $cardarr = [];
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

function normaliseCardName($card, $toLower = false): string
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

function parseCardsWithQuantity($cards): array
{
    $cards = parseCards($cards);
    $badcards = [];
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

function print_tooltip($text, $tooltip): void
{
    echo "<span class=\"tooltip\" title=\"$tooltip\">$text</span>";
}

// Our standard template variable naming is camelCase.
// Some of our objects have properties named in snake_case.
// So when we grab the values from an object to pass into
// a template with get_object_vars let's also preserve the
// naming standard by transforming the case.
function getObjectVarsCamelCase(object $obj): array
{
    $vars = get_object_vars($obj);

    return arrayMapRecursive('snakeToCamel', $vars);
}

function snakeToCamel(string $string): string
{
    return lcfirst(str_replace('_', '', ucwords($string, '_')));
}

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
