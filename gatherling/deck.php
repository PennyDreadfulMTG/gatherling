<?php

use Gatherling\Models\Deck;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Models\Player;

require_once 'lib.php';
require_once 'lib_form_helper.php';

print_header('Deck Database');

?>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">

<?php
$event = null;

if (isset($_GET['event'])) {
    if (!Event::exists($_GET['event'])) {
        unset($_GET['event']);
        echo '<div class="uppertitle">Deck Database</div>';
    } else {
        $event = new Event($_GET['event']);
        echo '<div class="uppertitle">' . $event->name . '</div>';
    }
} else {
    echo '<div class="uppertitle">Deck Database</div>';
}
if (!isset($_REQUEST['mode'])) {
    $_REQUEST['mode'] = '';
}
if (!isset($_POST['mode'])) {
    $_POST['mode'] = '';
}
if (strcmp($_REQUEST['mode'], 'view') == 0) {
    $deck = null;
    if (isset($_GET['event'])) {
        $deck = $event->getPlaceDeck('1st');
    } else {
        if (isset($_GET['id'])) {
            $deck = new Deck($_GET['id']);
        }
    }
    deckProfile($deck);
} else {
    // Need to auth for everything else.
    if (!isset($_POST['player']) and isset($_GET['player'])) {
        $_POST['player'] = $_GET['player'];
    }
    $deck_player = isset($_POST['player']) ? $_POST['player'] : Player::loginName();
    $deck = isset($_POST['id']) ? new Deck($_POST['id']) : null;
    if (!isset($_POST['event'])) {
        if (!isset($_GET['event'])) {
            $_GET['event'] = '';
        }
        $_REQUEST['event'] = $_GET['event'];
    }

    if (isset($_REQUEST['event']) && is_null($event)) {
        $event = new Event($_REQUEST['event']);
    }

    // part of the reg-decklist feature. both "register" and "addregdeck" switches
    if (strcmp($_REQUEST['mode'], 'register') == 0) {
        deckRegisterForm();
    } elseif (strcmp($_REQUEST['mode'], 'addregdeck') == 0) {
        $deck = insertDeck($event);
        deckProfile($deck);
    } elseif (is_null($deck) && $event->name == '') {
        echo 'No deck or event id specifed.<br/>';
        echo "Go back to <a href='player.php'>Player CP</a>";
    } elseif (checkDeckAuth($event, $deck_player, $deck)) {
        if (strcmp($_POST['mode'], 'Create Deck') == 0) {
            $deck = insertDeck($event);
            if ($deck->isValid()) {
                deckProfile($deck);
            } else {
                deckForm($deck);
            }
        } elseif (strcmp($_POST['mode'], 'Update Deck') == 0) {
            $deck = updateDeck($deck);
            $deck = new Deck($deck->id); // had to do this to get the constructor to run, otherwise errors weren't loading
            if ($deck->isValid()) {
                deckProfile($deck);
            } else {
                deckForm($deck);
            }
        } elseif (strcmp($_POST['mode'], 'Edit Deck') == 0) {
            deckForm($deck);
        } elseif (strcmp($_REQUEST['mode'], 'create') == 0) {
            deckForm();
        }
    }
}

?>
</div> <!-- gatherling_main box -->
</div> <!-- grid 10 suf 1 pre 1 -->
<script type="text/javascript" src="deck.js"></script>
<?php print_footer(); ?>

<?php

function deckForm($deck = null)
{
    $create = is_null($deck) || $deck->id == 0;
    $mode = $create ? 'Create Deck' : 'Update Deck';
    if (!$create) {
        $player = $deck->playername;
        $event = new Event($deck->eventname);
    } else {
        $player = (isset($_POST['player'])) ? $_POST['player'] : $_GET['player'];
        $event = new Event((isset($_POST['player'])) ? $_REQUEST['event'] : $_GET['event']);
    }

    if (!checkDeckAuth($event, $player, $deck)) {
        return;
    }

    $vals = ['contents' => '', 'sideboard' => ''];
    if (!$create) {
        foreach ($deck->maindeck_cards as $card => $amt) {
            $line = $amt . ' ' . $card . "\n";
            $vals['contents'] = $vals['contents'] . $line;
        }
        foreach ($deck->sideboard_cards as $card => $amt) {
            $line = $amt . ' ' . $card . "\n";
            $vals['sideboard'] = $vals['sideboard'] . $line;
        }
        $vals['desc'] = $deck->notes;
        $vals['archetype'] = $deck->archetype;
        $vals['name'] = $deck->name;
    }
    if (!isset($vals['name'])) {
        $vals['name'] = '';
    }
    if (!isset($vals['archetype'])) {
        $vals['archetype'] = '';
    }

    echo "<form action=\"deck.php\" method=\"post\">\n";
    echo "<table align=\"center\" style=\"border-width: 0px;\">\n";
    echo "<tr><th valign=\"top\"><b>Directions:</th>\n";
    echo '<td>To enter your deck, please give it ';
    echo 'a name and select an archetype from the drop-down menu below. If ';
    echo 'you do not specify an archetype, your deck will be labeled as ';
    echo '"Unclassified". To enter cards, save your deck as a .txt or .dek file ';
    echo 'using the official MTGO client, and then copy and paste the maindeck ';
    echo 'and sideboard into the appropriate text boxes. ';
    //   echo "Do not use a format such as \"1x Card\". ";
    //   echo "The parser will not accept this structure. The correct pattern is ";
    //   echo "\"1 Card\".";
    echo "</td></tr>\n";
    echo "<tr><th><label for='autoenter-deck'>Recent Decks</label></td>\n<td>\n";
    echo "<select class=\"inputbox\" id=\"autoenter-deck\">\n";
    echo "<option value=\"0\">Select a recent deck to start from there</option>\n";
    $deckplayer = new Player($player);
    $recentdecks = $deckplayer->getRecentDecks();
    foreach ($recentdecks as $adeck) {
        echo "<option value=\"{$adeck->id}\">{$adeck->name}</option>\n";
    }

    echo '</select></td></tr>';
    if (!$create) {
        if (count($deck->errors) > 0) {
            echo '<tr><td class="error">Errors</td><td>There are some problems adding your deck:<ul>';
            foreach ($deck->errors as $error) {
                echo "<li class=\"error\">$error</li>";
            }
            echo '</ul></td></tr>';
        }
    }
    print_file_input('Import File', 'txt');
    echo "<tr><td></td><td><hr width='60%' ALIGN=\"left\"/></td></tr>";
    echo textInput('Name', 'name', $vals['name'], 40, null, 'deck-name');
    if (!$create) {
        echo "<input type=\"hidden\" name=\"id\" value=\"{$deck->id}\">\n";
    }
    archetypeDropMenu($vals['archetype']);
    echo "<tr><td valign=\"top\"><b>Main Deck</td>\n<td>";
    echo '<textarea id="deck-contents" class="inputbox" rows="20" cols="60" name="contents">';
    echo "{$vals['contents']}</textarea></td></tr>\n";
    echo "<tr><td valign=\"top\"><b>Sideboard</td>\n<td>";
    echo '<textarea id="deck-sideboard" class="inputbox" rows="10" cols="60" name="sideboard">';
    echo "{$vals['sideboard']}</textarea></td></tr>\n";
    echo "<tr><td valign=\"top\"><b>Comments</td>\n<td>";
    echo '<textarea class="inputbox" rows="10" cols="60" name="notes">';
    if (!isset($vals['desc'])) {
        $vals['desc'] = '';
    }
    echo "{$vals['desc']}</textarea></td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"2\" align=\"center\">\n";
    echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"$mode\">\n";
    echo "<input type=\"hidden\" name=\"player\" value=\"$player\">";
    echo "<input type=\"hidden\" name=\"event\" value=\"$event->id\">";
    echo "</td></tr></table></form>\n";
}

// deckRegisterForm is part of the reg-decklist feature
function deckRegisterForm()
{
    $player = (isset($_POST['player'])) ? $_POST['player'] : $_GET['player'];
    $event = (isset($_POST['player'])) ? $_POST['event'] : $_GET['event'];
    $vals = ['contents' => '', 'sideboard' => ''];

    echo "<form action=\"deck.php?mode=addregdeck\" method=\"post\">\n";
    echo "<table align=\"center\" style=\"border-width: 0px;\">\n";
    echo "<tr><td valign=\"top\"><b>Directions:</td>\n";
    echo '<td>To enter your deck, please give it ';
    echo 'a name and select an archetype from the drop-down menu below. If ';
    echo 'you do not specify an archetype, your deck will be labeled as ';
    echo '"Unclassified". To enter cards, save your deck as a .txt or .dek file ';
    echo 'using the official MTGO client, and then copy and paste the maindeck ';
    echo 'and sideboard into the appropriate text boxes. ';
    echo 'Do not use a format such as "1x Card". ';
    echo 'The parser will not accept this structure. The correct pattern is ';
    echo "\"1 Card\".</td></tr>\n";
    echo "<tr><td><b>Recent Decks</b></td>\n<td>\n";
    echo "<select class=\"inputbox\" id=\"autoenter-deck\">\n";
    echo "<option value=\"0\">Select a recent deck to start from there</option>\n";
    $deckplayer = new Player($player);
    $recentdecks = $deckplayer->getRecentDecks();
    foreach ($recentdecks as $adeck) {
        echo "<option value=\"{$adeck->id}\">{$adeck->name}</option>\n";
    }
    echo '</select></td></tr>';
    echo "<tr><td><b>Name</td>\n<td>";
    if (!isset($vals['name'])) {
        $vals['name'] = '';
    }
    echo "<input id=\"deck-name\" class=\"inputbox\" type=\"text\" name=\"name\" value=\"{$vals['name']}\" ";
    echo "size=\"40\"></td></tr>\n";
    if (!isset($vals['archetype'])) {
        $vals['archetype'] = '';
    }
    archetypeDropMenu($vals['archetype']);
    echo "<tr><td valign=\"top\"><b>Main Deck</td>\n<td>";
    echo '<textarea id="deck-contents" class="inputbox" rows="20" cols="60" name="contents">';
    echo "{$vals['contents']}</textarea></td></tr>\n";
    echo "<tr><td valign=\"top\"><b>Sideboard</td>\n<td>";
    echo '<textarea id="deck-sideboard" class="inputbox" rows="10" cols="60" name="sideboard">';
    echo "{$vals['sideboard']}</textarea></td></tr>\n";
    echo "<tr><td valign=\"top\"><b>Comments</td>\n<td>";
    echo '<textarea class="inputbox" rows="10" cols="60" name="notes">';
    if (!isset($vals['desc'])) {
        $vals['desc'] = '';
    }
    echo "{$vals['desc']}</textarea></td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"2\" align=\"center\">\n";
    echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Create Deck\">\n";
    echo "<input type=\"hidden\" name=\"player\" value=\"$player\">";
    echo "<input type=\"hidden\" name=\"event\" value=\"$event\">";
    echo "</td></tr></table></form>\n";
}

function archetypeDropMenu($def = '')
{
    $archetypes = Deck::getArchetypes();
    $archetypes = array_combine($archetypes, $archetypes);
    $archetypes = ['Unclassified' => '- Archetype -'] + $archetypes;
    echo selectInput('Archetype', 'archetype', $archetypes, $def, 'deck-archetype');
}

function insertDeck($event)
{
    $deck = new Deck(0);

    $deck->name = $_POST['name'];
    $deck->archetype = $_POST['archetype'];
    $deck->notes = $_POST['notes'];

    $deck->playername = $_POST['player'];
    $deck->eventname = $event->name;
    $deck->event_id = $event->id;

    $deck->maindeck_cards = parseCardsWithQuantity($_POST['contents']);
    $deck->sideboard_cards = parseCardsWithQuantity($_POST['sideboard']);

    if (!$deck->save()) {
        deckForm($deck);
    }

    return $deck;
}

function updateDeck($deck)
{
    $deck->archetype = $_POST['archetype'];
    $deck->name = $_POST['name'];
    $deck->notes = $_POST['notes'];

    $deck->maindeck_cards = parseCardsWithQuantity($_POST['contents']);
    $deck->sideboard_cards = parseCardsWithQuantity($_POST['sideboard']);

    if (!$deck->save()) {
        deckForm($deck);
    }

    return $deck;
}

function deckProfile($deck)
{
    if ($deck == null || $deck->id == 0) {
        echo '<span class="error"><center>Deck is not found.  It is possible that it is not entered yet.</center></span>';

        return;
    }
    if ($deck->canView(Player::loginName())) {
        echo "<center><form action=\"deckdl.php\" method=\"post\">\n";
        echo "<input type=\"hidden\" name=\"id\" value={$deck->id}>\n";
        echo '<input class="inputbutton" type="submit" name="mode" value="Download deck as .txt file">';
        echo "</form></center><br>\n";
        echo "<div class=\"grid_5 alpha\"><div id=\"gatherling_lefthalf\">\n";

        echo '<div class="clear"></div>';
        if (!$deck->isValid()) {
            $deckErrors = $deck->getErrors();
            deckErrorTable($deckErrors);
        }

        deckInfoCell($deck);
        maindeckTable($deck);
        sideboardTable($deck);
        echo "</div></div>\n";
        echo "<div class=\"grid_5 omega\"><div id=\"gatherling_righthalf\">\n";
        trophyCell($deck);
        matchupTable($deck);
        echo "<div class=\"grid_2 alpha\">\n";
        symbolTable($deck);
        echo "</div> <div class=\"grid_2 omega\">\n";
        ccTable($deck);
        echo "</div>\n";
        echo '<div class="clear"></div>';
        exactMatchTable($deck);
        echo "</div></div>\n";
        echo '<div class="clear"></div>';
        echo '<div>';
        commentsTable($deck);
        echo '</div>';
        echo '<div class="clear"></div>';
        echo "<center>\n";
        echo "<form action=\"deck.php\" method=\"post\">\n";
        echo "<input type=\"hidden\" name=\"id\" value=\"$deck->id\">\n";
        if ($deck->canEdit(Player::loginName())) {
            echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Edit Deck\">\n";
        }
        echo "</form></center>\n";
    } else {
        echo '<span class="error"><center>You do not have permission to view this deck. Decks are anonymous for privacy until event is finalized.</center></span>';
        echo '<br /><br />';
        echo '<ul>People who can see decks while events are active:</ul>';
        echo "<li>Gatherling Admin's</li>";
        echo '<li>The Series Organizer</li>';
        echo '<li>The Event Host</li>';
        echo '<li>The Player Who Created The Deck</li>';
    }
}

function commentsTable($deck)
{
    $notes = $deck->notes;
    if ($notes == '' || is_null($notes)) {
        $notes = '<i>No comments have been recorded for this deck.</i>';
    } else {
        $notes = strip_tags($notes);
        $notes = preg_replace("/\n/", '<br />', $notes);
        $notes = preg_replace("/\[b\]/", '<b>', $notes);
        $notes = preg_replace("/\[\/b\]/", '</b>', $notes);
        $notes = preg_replace("/\[i\]/", '<i>', $notes);
        $notes = preg_replace("/\[\/i\]/", '</i>', $notes);
    }
    echo '<table style="border-width: 0px; width: 100%; " cellpadding=1>';
    echo '<tr><td><b>COMMENTS</td></tr>';
    echo "<tr><td>{$notes}</td></tr>";
    echo '</table>';
}

function deckInfoCell($deck)
{
    $nmaincards = $deck->getCardCount($deck->maindeck_cards);
    $nsidecards = $deck->getCardCount($deck->sideboard_cards);
    $event = $deck->getEvent();
    $day = date('F j, Y', strtotime($event->start));
    $mstr = image_tag($deck->medal . '.png') . '&nbsp;';
    if ($deck->medal == '1st') {
        $placing = $mstr . '1st by';
    } elseif ($deck->medal == '2nd') {
        $placing = $mstr . '2nd by';
    } elseif ($deck->medal == 't4') {
        $placing = $mstr . 'Top 4 by';
    } elseif ($deck->medal == 't8') {
        $placing = $mstr . 'Top 8 by';
    } else {
        $placing = 'Played by';
    }
    $line3 = "{$placing} ";
    if ($deck->playername != null) {
        $deckplayer = new Player($deck->playername);
        $line3 .= $deckplayer->linkTo();
        $targetUrl = 'eventreport';
        $player = Player::loginName();
        if ($player && $event->authCheck($player)) {
            $targetUrl = 'event';
        }
        $line3 .= " in <a href=\"{$targetUrl}.php?event=" . rawurlencode($deck->eventname) . "\"><span class=\"eventname\" title=\"{$day}\">{$event->name}</span></a>\n";
    } else {
        $line3 .= 'Never played (?) according to records.';
    }

    $rstar = '<font color="#FF0000">*</font>';
    $name = $deck->name;
    if (empty($name)) {
        $name = '** NO NAME **';
    }
    $line1 = '<b>' . strtoupper($name) . '</b>';
    if (!$deck->isValid()) {
        $line1 .= $rstar;
    }
    $cardcountsline = "{$nmaincards} Maindeck cards and {$nsidecards} Sideboard";
    $line2 = $event->format . ' &middot; ' . $deck->getColorImages();
    $line3 .= '<i>(' . $deck->recordString() . ')</i>';
    $format = new Format($event->format);
    if ($format->tribal > 0) {
        $line2 .= ' ' . $deck->tribe . '  ' . $deck->archetype . "</td></tr>\n";
    } else {
        $line2 .= $deck->archetype . "</td></tr>\n ";
    }

    echo "<table style=\"border-width: 0px\">\n";
    echo "<tr><td style=\"font-size: 10pt;\">$line1 (deck id: $deck->id)</td></tr>\n";
    if ($format->tribal > 0) {
        echo '<tr><td>Tribe: ' . $deck->tribe . '</td></tr>';
    }
    echo "<tr><td>$cardcountsline</td></tr>\n";
    echo "<tr><td>$line2";
    echo "<tr><td>$line3</td></tr>\n";
    echo "</table>\n";
}

function trophyCell($deck)
{
    if ($deck->medal == '1st') {
        echo '<center>';
        if ($deck->getEvent()->hastrophy) {
            echo $deck->getEvent()->getTrophyImageLink();
        } else {
            echo 'No trophy uploaded yet!';
        }
        echo '</center><br /> <br />';
    }
}

function sideboardTable($deck)
{
    $sideboardcards = $deck->sideboard_cards;

    ksort($sideboardcards);
    arsort($sideboardcards, SORT_NUMERIC);
    echo "<table style=\"border-width: 0px\" cellpadding=1>\n";
    echo "<tr><td colspan=1><b>SIDEBOARD ({$deck->getCardCount($deck->sideboard_cards)} Cards)</td></tr>\n";
    foreach ($sideboardcards as $card => $amt) {
        echo "<tr><td>{$amt} ";
        printCardLink($card);
        echo '</td></tr>';
    }
    echo "</table>\n";
}

function exactMatchTable($deck)
{
    if ($deck->maindeck_cardcount < 5) {
        return;
    }
    $decks = $deck->findIdenticalDecks();
    if (count($decks) == 0) {
        return false;
    }
    echo "<table style=\"border-width: 0px\" cellpadding=1 align=\"right\">\n";
    echo "<tr><th colspan=5 align=\"left\"><b>THIS DECK ALSO PLAYED AS</td></tr>\n";
    foreach ($decks as $deck) {
        if (!isset($deck->playername)) {
            continue;
        }
        $cell1 = medalImgStr($deck->medal);
        $cell4 = $deck->recordString();
        echo "<tr><td>$cell1</td>\n";
        echo '<td style="width: 140px">' . $deck->linkTo() . "</td>\n";
        echo "<td>{$deck->playername}</td>\n";
        echo "<td><a href=\"{$deck->getEvent()->threadurl}\">{$deck->eventname}</a></td>\n";
        echo "<td style=\"text-align: right; width: 30px;\">$cell4</td></tr>\n";
    }
    echo "</table>\n";
}

function matchupTable($deck)
{
    echo "<table style=\"border-width: 0px\" cellpadding=1 align=\"right\">\n";
    $event = new Event($deck->eventname);

    if ($deck->canView(Player::loginName())) {
        $matches = $deck->getMatches();
        echo "<tr><td colspan=4 align=\"left\"><b>MATCHUPS</td></tr>\n";
        if (count($matches) == 0) {
            echo '<tr><td colspan=4><i>No matches were found for this deck</td></tr>';
        }

        foreach ($matches as $match) {
            $rnd = 'R' . $match->round;
            if ($match->timing > 1 && $match->type == 'Single Elimination') {
                $rnd = 'T' . pow(2, $match->rounds - $match->round + 1);
            }
            $color = '#FF9900';
            $res = 'Draw';
            if ($match->playerMatchInProgress($deck->playername)) {
                $res = 'In Progress';
            }
            if ($match->playerWon($deck->playername)) {
                $color = '#009900';
                $res = 'Win';
            }
            if ($match->playerLost($deck->playername)) {
                $color = '#FF0000';
                $res = 'Loss';
            }
            if ($match->playerBye($deck->playername)) {
                $res = 'Bye';
            }
            if ($res != 'Bye') {
                $opp = new Player($match->otherPlayer($deck->playername));
                $deckcell = 'No Deck Found';
                $oppdeck = $opp->getDeckEvent($deck->event_id);
                if ($oppdeck != null) {
                    $deckcell = $oppdeck->linkTo();
                }
                echo "<tr><td>$rnd:&nbsp;</td>\n";
                echo "<td><b style=\"color: $color\">$res</b></td>\n";
                echo "<td class=\"score\">{$match->getPlayerWins($deck->playername)}-{$match->getPlayerLosses($deck->playername)}</td>";
                echo "<td>vs.</td>\n";
                echo '<td class=\"player\">' . $opp->linkTo() . "</td>\n";
                if (!$event->active && $event->finalized) {
                    echo "<td>$deckcell</td></tr>\n";
                }
            } else {
                echo "<tr><td>$rnd:</td>\n";
                echo "<td><b>$res</td>\n";
                echo '<td class=\"score\">0 - 0</td>';
                echo "<td>vs.</td>\n";
                echo "<td>No Opponent</td>\n";
                echo "<td>No Deck Found</td></tr>\n";
            }
        }
    } else {
        echo '<tr><td>Decks are anonymous for privacy until event is finalized.</td><tr>';
    }
    echo '<tr><td>&nbsp;</td></tr>';
    echo "</table>\n";
}

function deckErrorTable($deckErrors)
{
    echo "<table style=\"border-width: 0px; width: 100%; \" cellpadding=1>\n";
    echo '<tr><td><span class="error"><b>ERRORS</spa></td></tr>';
    foreach ($deckErrors as $error) {
        echo "<tr><td><span class=\"error\">{$error}</span></td></tr>";
    }
    echo '</table>';
}

function maindeckTable($deck)
{
    $creatures = $deck->getCreatureCards();
    $creaturesCount = $deck->getCardCount($creatures);

    $lands = $deck->getLandCards();
    $landsCount = $deck->getCardCount($lands);

    $other = $deck->getOtherCardS();
    $otherSpellsCount = $deck->getCardCount($other);

    echo "<table style=\"border-width: 0px\" cellpadding=1>\n";
    echo "<tr><td colspan=1><b>MAINDECK ({$deck->getCardCount($deck->maindeck_cards)} Cards)</td></tr>\n";
    echo "<tr><td colspan=2><i>{$creaturesCount} Creatures</td></tr>\n";
    foreach ($creatures as $card => $amt) {
        echo "<tr><td>{$amt} ";
        printCardLink($card);
        echo "</td></tr>\n";
    }
    echo "<tr><td colspan=2><i>{$otherSpellsCount} Spells</td></tr>\n";
    foreach ($other as $card => $amt) {
        echo "<tr><td>{$amt} ";
        printCardLink($card);
        echo "</td></tr>\n";
    }
    echo "<tr><td colspan=2><i>{$landsCount} Lands</td></tr>\n";
    foreach ($lands as $card => $amt) {
        echo "<tr><td>{$amt} ";
        printCardLink($card);
        echo "</td></tr>\n";
    }
    echo "</table>\n";
}

function ccTable($deck)
{
    $convertedcosts = $deck->getCastingCosts();

    echo "<table style=\"border-width: 0px;\">\n";
    echo '<tr><td colspan=2 align="center" width=150><b>CASTING COSTS</td></tr>';
    $total = 0;
    $cards = 0;
    foreach ($convertedcosts as $cost => $amt) {
        echo '<tr><td align="right" width=75>';
        echo image_tag("mana{$cost}.png");
        echo " &nbsp;</td>\n";
        echo "<td width=75 align=\"left\">x {$amt}</td></tr>\n";
        $total += $cost * $amt;
        $cards += $amt;
    }
    if ($cards == 0) {
        $cards = 1;
    }
    $avg = $total / $cards;
    echo '<tr><td align="right"><i>Avg CMC:&nbsp;</td><td align="left"><i>';
    printf('%1.2f', $avg);
    echo "</td></tr>\n";
    echo '</table>';
}

function symbolTable($deck)
{
    echo "<table style=\"border-width: 0px\">\n";
    echo '<tr><td align="center" colspan=2 width=150><b>MANA SYMBOLS';
    echo "</td></tr>\n";
    $cnts = $deck->getColorCounts();
    asort($cnts);
    $cnts = array_reverse($cnts, true);
    $sum = 0;
    foreach ($cnts as $color => $num) {
        if ($num > 0) {
            echo '<tr><td align="right" width=75>';
            echo image_tag("mana{$color}.png");
            echo "&nbsp;</td>\n";
            echo "<td align=\"left\">$num</td></tr>\n";
            $sum += $num;
        }
    }
    echo "<tr><td align=\"right\"><i>Total:&nbsp;</td>\n";
    echo "<td align=\"left\"><i>$sum</td></tr>\n";
    echo "</table>\n";
}

function authFailed()
{
    echo 'You are not permitted to make that change.  Reasons why you cannot make changes to a deck are: <br /> ';
    echo '<ul>';
    echo '<li>You are not the player who played/created the deck for this event.';
    echo '<li>The event has already started and become active.</li>';
    echo '<li>The event has completed and become finalized.</li>';
    echo '</ul>';
    echo 'Please contact the event host to modify this deck. If you <b>are</b> the event host ';
    echo 'or feel that you should have privilege to modify this deck, you ';
    echo 'should contact the admin via <a href="https://discord.gg/2VJ8Fa6">the Discord server</a>.<br><br>';
}

function loginRequired()
{
    echo "<center>You can't do that unless you <a href=\"login.php\">log in first</a></center>";
}

function checkDeckAuth($event, $player, $deck = null)
{
    if (!Player::isLoggedIn()) {
        loginRequired();

        return false;
    }
    if (is_null($deck) && $event->id > 0) {
        // Creating a deck.
        $entry = new Entry($event->id, $player);
        $auth = $entry->canCreateDeck(Player::loginName());
    } else {
        // Updating a deck.
        $auth = $deck->canEdit(Player::loginName());
    }

    if (!$auth) {
        authFailed();
    }

    return $auth;
}
