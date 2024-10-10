<?php

declare(strict_types=1);

use Gatherling\Models\CardSet;
use Gatherling\Models\Format;
use Gatherling\Models\Player;
use Gatherling\Views\Components\BAndR;
use Gatherling\Views\Components\CardSets;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\ErrorMessage;
use Gatherling\Views\Components\FormatDeleteForm;
use Gatherling\Views\Components\FormatError;
use Gatherling\Views\Components\FormatRenameForm;
use Gatherling\Views\Components\FormatSaveAsForm;
use Gatherling\Views\Components\FormatSettings;
use Gatherling\Views\Components\FormatSuccess;
use Gatherling\Views\Components\LoadFormatForm;
use Gatherling\Views\Components\NewFormatForm;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Components\TribalBAndR;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Pages\FormatAdmin;
use Gatherling\Views\Pages\InsufficientPermissions;

use function Gatherling\Views\post;
use function Gatherling\Views\server;
use function Gatherling\Views\request;

require_once 'lib.php';
include 'lib_form_helper.php';

function main(): void
{
    if (!Player::isLoggedIn()) {
        (new LoginRedirect())->send();
    }

    $player = Player::getSessionPlayer();

    if (!$player->isOrganizer() && !$player->isSuper()) {
        (new InsufficientPermissions($player->isOrganizer()))->send();
    }

    $playerSeries = Player::getSessionPlayer()->organizersSeries();
    if ($player->isSuper()) {
        array_unshift($playerSeries, 'System');
    }

    $seriesName = request()->optionalString('series') ?? $playerSeries[0];

    if (!in_array($seriesName, $playerSeries)) {
        (new InsufficientPermissions($player->isOrganizer()))->send();
    }

    $actionResultComponent = handleAction($seriesName);

    if (!isset($_REQUEST['format']) || empty($_REQUEST['format'])) {
        if (!($actionResultComponent instanceof LoadFormatForm)) {
            $actionResultComponent = [$actionResultComponent, new LoadFormatForm($seriesName)];
        }
        $page = new FormatAdmin(server()->string('PHP_SELF'), $playerSeries, $seriesName, new Format(''), $actionResultComponent);
        $page->send();
    }

    $format = request()->optionalString('format');
    if ($format && Format::doesFormatExist($format)) {
        $activeFormat = new Format($format);
    } else {
        $activeFormat = new Format('');
    }

    switch ($_REQUEST['view']) {
        case 'bandr':
            $view = new BAndR($seriesName, $activeFormat);
            break;
        case 'tribal':
            $view = new TribalBAndR($seriesName, $activeFormat);
            break;
        case 'cardsets':
            $view = new CardSets($seriesName, $activeFormat);
            break;
        case 'no_view':
            $view = new NullComponent();
            break;
        case 'settings':
        default:
            $view = new FormatSettings($seriesName, $activeFormat);
    }
    $page = new FormatAdmin(server()->string('PHP_SELF'), $playerSeries, $seriesName, $activeFormat, $actionResultComponent, $view);
    $page->send();
}

function handleAction(string $seriesName): Component
{
    if (!isset($_POST['action']) ||$_POST['action'] == 'Continue' || $_POST['action'] == 'Load Format') {
        return new NullComponent();
    }
    if ($_POST['action'] == 'New') {
        return new NewFormatForm($seriesName);
    }
    if ($_POST['action'] == 'Load') {
        return new LoadFormatForm($seriesName);
    }
    if ($_POST['action'] == 'Update Banlist') {
        return updateBanlist(post()->string('format'), post()->string('addbancard', ''), post()->listString('delbancards'));
    }
    if ($_POST['action'] == 'Delete Entire Banlist') {
        $format = new Format(post()->string('format'));
        $success = $format->deleteEntireBanlist(); // leave a message of success
        return $success ? new NullComponent() : new ErrorMessage(['Failed to delete banlist']);
    }
    if ($_POST['action'] == 'Update Legal List') {
        return updateLegalList(post()->string('format'), post()->string('addlegalcard'), post()->listString('dellegalcards'));
    }
    if ($_POST['action'] == 'Delete Entire Legal List') {
        $format = new Format(post()->string('format'));
        $success = $format->deleteEntireLegallist(); // leave a message of success
        return $success ? new NullComponent() : new ErrorMessage(['Failed to delete legal list']);
    }
    if ($_POST['action'] == 'Update Cardsets') {
        return updateCardSets(post()->string('format'), post()->string('cardsetname', ''), post()->listString('delcardsetname'));
    }
    if (strncmp(post()->string('action', ''), 'Add All', 7) == 0) {
        return addAll(post()->string('format'), substr(post()->string('action', ''), 8));
    }
    if ($_POST['action'] == 'Update Restricted List') {
        return updateRestrictedList(post()->string('format'), post()->string('addrestrictedcard', ''), post()->listString('delrestrictedcards'));
    }
    if ($_POST['action'] == 'Delete Entire Restricted List') {
        $format = new Format(post()->string('format'));
        $success = $format->deleteEntireRestrictedlist(); // leave a message of success
        return $success ? new NullComponent() : new ErrorMessage(['Failed to delete restricted list']);
    }
    if ($_POST['action'] == 'Update Format') {
        return updateFormat($_POST);
    }
    if ($_POST['action'] == 'Create New Format') {
        return createNewFormat($seriesName, post()->string('newformatname'));
    }
    if ($_POST['action'] == 'Save As') {
        return saveAsForm(post()->string('format'));
    }
    if ($_POST['action'] == 'Save') {
        return save($seriesName, post()->string('newformat'), post()->string('oldformat'));
    }
    if ($_POST['action'] == 'Rename') {
        return renameForm($seriesName);
    }
    if ($_POST['action'] == 'Rename Format') {
        return renameFormat($seriesName, post()->string('newformat'), post()->string('format'));
    }
    if ($_POST['action'] == 'Delete') {
        return deleteForm($seriesName, post()->string('format'));
    }
    if ($_POST['action'] == 'Delete Format') {
        return deleteFormat(post()->string('format'));
    }
    if ($_POST['action'] == 'Update Restricted To Tribe List') {
        return updateRestrictedToTribeList(post()->string('format'), post()->string('addrestrictedtotribecreature'), post()->listString('delrestrictedtotribe'));
    }
    if ($_POST['action'] == 'Delete Entire Restricted To Tribe List') {
        $format = new Format(post()->string('format'));
        $success = $format->deleteEntireRestrictedToTribeList(); // leave a message of success
        return $success ? new NullComponent() : new ErrorMessage(['Failed to delete restricted to tribe list']);
    }
    if ($_POST['action'] == 'Update Subtype Ban') {
        return updateSubtypeBan(post()->string('format'), post()->string('subtypeban'), post()->listString('delbannedsubtype'));
    }
    if ($_POST['action'] == 'Update Tribe Ban') {
        return updateTribeBan(post()->string('format'), post()->string('tribeban'), post()->listString('delbannedtribe'));
    }
    if ($_POST['action'] == 'Ban All Tribes') {
        $format = new Format(post()->string('format'));
        $format->banAllTribes();
        return new NullComponent();
    }
    return new ErrorMessage(["Unknown action '{$_POST['action']}'"]);
}

/**
 * @param string|array<string> $addBanCards
 * @param array<string> $delBanCards
 */
function updateBanlist(string $activeFormat, string|array $addBanCards, array $delBanCards): Component
{
    $format = new Format($activeFormat);
    if ($addBanCards) {
        $cards = parseCards($addBanCards);
        foreach ($cards as $card) {
            if (!$format->insertCardIntoBanlist($card)) {
                return new ErrorMessage(["Can't add {$card} to Ban list, it is either not in the database, or it's on the legal card list"]);
            }
        }
    }
    $errors = [];
    foreach ($delBanCards as $cardName) {
        $success = $format->deleteCardFromBanlist($cardName);
        if (!$success) {
            $errors[] = "Can't delete {$cardName} from ban list.";
        }
    }
    return $errors ? new ErrorMessage($errors) : new NullComponent();
}

/**
 * @param string|array<string> $addLegalCards
 * @param array<string> $delLegalCards
 */
function updateLegalList(string $formatName, array|string $addLegalCards, array $delLegalCards): Component
{
    $errors = [];
    $format = new Format($formatName);

    if ($addLegalCards) {
        $cards = parseCards($addLegalCards);
        if (count($cards) > 0) {
            foreach ($cards as $card) {
                if (!in_array($card, $format->card_legallist)) {
                    $success = $format->insertCardIntoLegallist($card);
                    if (!$success) {
                        $errors[] = "Can't add {$card} to Legal list, it is either not in the database or on the ban list.";
                    }
                }
            }
        }
    }

    foreach ($delLegalCards as $cardName) {
        $success = $format->deleteCardFromLegallist($cardName);
        if (!$success) {
            $errors[] = "Can't delete {$cardName} from legal list.";
        }
    }

    return $errors ? new ErrorMessage($errors) : new NullComponent();
}

/**
 * @param string $cardSetName
 * @param array<string> $delCardSets
 */
function updateCardSets(string $formatName, string $cardSetName, array $delCardSets): Component
{
    $format = new Format($formatName);

    if ($cardSetName && $cardSetName != 'Unclassified') {
        $format->insertNewLegalSet($cardSetName);
    }

    $errors = [];
    foreach ($delCardSets as $cardset) {
        $success = $format->deleteLegalCardSet($cardset);
        if (!$success) {
            $errors[] = "Can't delete {$cardset} from allowed cardsets ";
        }
    }
    return $errors ? new ErrorMessage($errors) : new NullComponent();
}

function addAll(string $formatName, string $cardsetType): Component
{
    $format = new Format($formatName);
    $missing = CardSet::getMissingSets($cardsetType, $format);
    foreach ($missing as $set) {
        $format->insertNewLegalSet($set);
    }
    return new NullComponent();
}

/**
 * @param string|array<string> $addRestrictedCards
 * @param array<string> $delRestrictedCards
 */
function updateRestrictedList(string $formatName, string|array $addRestrictedCards, array $delRestrictedCards): Component
{
    $format = new Format($formatName);

    if ($addRestrictedCards) {
        $cards = parseCards($addRestrictedCards);
        if (count($cards) > 0) {
            foreach ($cards as $card) {
                $success = $format->insertCardIntoRestrictedlist($card);
            }
            if (!$success) {
                return new ErrorMessage(["Can't add {$card} to Restricted list, it is either not in the database, on the ban list, legal card list, or already on the restricted list"]);
            }
        }
    }

    $errors = [];
    foreach ($delRestrictedCards as $cardName) {
        $success = $format->deleteCardFromRestrictedlist($cardName);
        if (!$success) {
            $errors[] = "Can't delete {$cardName} from restricted list";
        }
    }

    return $errors ? new ErrorMessage($errors) : new NullComponent();
}

/** @param array<string, string> $values */
function updateFormat(array $values): Component
{
    $format = new Format($values['format']);

    if (isset($values['formatdescription'])) {
        $format->description = $values['formatdescription'];
    }

    if (isset($values['minmain'])) {
        $format->min_main_cards_allowed = (int) $values['minmain'];
    }
    if (isset($values['maxmain'])) {
        $format->max_main_cards_allowed = (int) $values['maxmain'];
    }
    if (isset($values['minside'])) {
        $format->min_side_cards_allowed = (int) $values['minside'];
    }
    if (isset($values['maxside'])) {
        $format->max_side_cards_allowed = (int) $values['maxside'];
    }

    if (isset($values['singleton'])) {
        $format->singleton = 1;
    } else {
        $format->singleton = 0;
    }
    if (isset($values['commander'])) {
        $format->commander = 1;
    } else {
        $format->commander = 0;
    }
    if (isset($values['vanguard'])) {
        $format->vanguard = 1;
    } else {
        $format->vanguard = 0;
    }
    if (isset($values['planechase'])) {
        $format->planechase = 1;
    } else {
        $format->planechase = 0;
    }
    if (isset($values['prismatic'])) {
        $format->prismatic = 1;
    } else {
        $format->prismatic = 0;
    }
    if (isset($values['tribal'])) {
        $format->tribal = 1;
    } else {
        $format->tribal = 0;
    }

    if (isset($values['underdog'])) {
        $format->underdog = 1;
    } else {
        $format->underdog = 0;
    }
    if (isset($values['pure'])) {
        $format->pure = 1;
    } else {
        $format->pure = 0;
    }

    if (isset($values['allowcommons'])) {
        $format->allow_commons = 1;
    } else {
        $format->allow_commons = 0;
    }
    if (isset($values['allowuncommons'])) {
        $format->allow_uncommons = 1;
    } else {
        $format->allow_uncommons = 0;
    }
    if (isset($values['allowrares'])) {
        $format->allow_rares = 1;
    } else {
        $format->allow_rares = 0;
    }
    if (isset($values['allowmythics'])) {
        $format->allow_mythics = 1;
    } else {
        $format->allow_mythics = 0;
    }
    if (isset($values['allowtimeshifted'])) {
        $format->allow_timeshifted = 1;
    } else {
        $format->allow_timeshifted = 0;
    }

    if (isset($values['eternal'])) {
        $format->eternal = 1;
    } else {
        $format->eternal = 0;
    }
    if (isset($values['modern'])) {
        $format->modern = 1;
    } else {
        $format->modern = 0;
    }
    if (isset($values['standard'])) {
        $format->standard = 1;
    } else {
        $format->standard = 0;
    }

    if (isset($values['is_meta_format'])) {
        $format->is_meta_format = 1;
    } else {
        $format->is_meta_format = 0;
    }

    $format->save();

    return new NullComponent();
}

function createNewFormat(string $seriesName, string $newFormatName): Component
{
    $format = new Format('');
    $format->name = $newFormatName;
    $seriesType = 'Private';
    if ($seriesName == 'System') {
        $seriesType = 'System';
    }
    $format->type = $seriesType;
    $format->series_name = $seriesName;
    $success = $format->save();
    if ($success) {
        return new FormatSuccess("New Format $format->name Created Successfully!", $format->name);
    }
    return new FormatError("New Format {$newFormatName} Could Not Be Created:-(", '', 'settings');
}

function saveAsForm(string $formatName): Component
{
    $format = new Format($formatName);
    $oldformatname = $format->name;
    return new FormatSaveAsForm($oldformatname);
}

function save(string $seriesName, string $newFormatName, string $oldFormatName): Component
{
    $format = new Format('');
    $format->name = $newFormatName;
    $seriesType = 'Private';
    if ($seriesName == 'System') {
        $seriesType = 'System';
    }
    $format->type = $seriesType;
    $format->series_name = $seriesName;
    $success = $format->saveAs($oldFormatName);
    if ($success) {
        return new FormatSuccess("New Format $format->name Saved Successfully!", $format->name);
    }
    return new FormatError("New Format {$newFormatName} Could Not Be Saved :-(", $oldFormatName, 'settings');
}

function renameForm(string $seriesName): Component
{
    return new FormatRenameForm($seriesName);
}

function renameFormat(string $seriesName, string $newFormatName, string $formatName): Component
{
    $format = new Format('');
    $format->name = $newFormatName;
    $seriesType = 'Private';
    if ($seriesName == 'System') {
        $seriesType = 'System';
    }
    $format->type = $seriesType;
    $format->series_name = $seriesName;
    $success = $format->rename($formatName);
    if ($success) {
        return new FormatSuccess("Format {$formatName} Renamed as $format->name Successfully!", $format->name);
    }
    return new FormatError("Format {$formatName} Could Not Be Renamed :-(", $formatName);
}

function deleteForm(string $seriesName, string $formatName): Component
{
    return new FormatDeleteForm($seriesName);
}

function deleteFormat(string $formatName): Component
{
    $format = new Format($formatName);

    $success = $format->delete();
    if ($success) {
        return new FormatSuccess("Format {$formatName} Deleted Successfully!");
    }
    return new FormatError("Could Not Delete {$formatName}!", $formatName);
}

/**
 * @param string|array<string> $addRestrictedToTribeCreature
 * @param array<string> $delRestrictedToTribe
 */
function updateRestrictedToTribeList(string $formatName, string|array $addRestrictedToTribeCreature, array $delRestrictedToTribe): Component
{
    $format = new Format($formatName);
    if ($addRestrictedToTribeCreature) {
        $cards = parseCards($addRestrictedToTribeCreature);
        foreach ($cards as $card) {
            $success = $format->insertCardIntoRestrictedToTribeList($card);
            if (!$success) {
                return new ErrorMessage(["Can't add {$card} to Restricted to tribe list, it is either not in the database, currently on the ban list, or is already on the Restricted to Tribe List"]);
            }
        }
    }
    foreach ($delRestrictedToTribe as $cardName) {
        $success = $format->deleteCardFromRestrictedToTribeList($cardName);
        if (!$success) {
            return new ErrorMessage(["Can't delete {$cardName} from restricted to tribe list."]);
        }
    }
    return new NullComponent();
}

/**
 * @param string $subTypeBan
 * @param array<string> $delBannedSubType
 */
function updateSubtypeBan(string $formatName, string $subTypeBan, array $delBannedSubType): Component
{
    $format = new Format($formatName);

    if ($subTypeBan) {
        $format->insertNewSubTypeBan($subTypeBan);
    }
    foreach ($delBannedSubType as $bannedSubType) {
        $success = $format->deleteSubTypeBan($bannedSubType);
        if (!$success) {
            return new ErrorMessage(["Can't delete {$bannedSubType} from banned subtypes"]);
        }
    }
    return new NullComponent();
}

/**
 * @param string $tribeBan
 * @param array<string> $delBannedTribe
 */
function updateTribeBan(string $formatName, string $tribeBan, array $delBannedTribe): Component
{
    $format = new Format($formatName);

    if ($tribeBan) {
        $format->insertNewTribeBan($tribeBan);
    }

    foreach ($delBannedTribe as $bannedtribe) {
        $success = $format->deleteTribeBan($bannedtribe);
        if (!$success) {
            return new ErrorMessage(["Can't delete {$bannedtribe} from banned tribes"]);
        }
    }
    return new NullComponent();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
