<?php

use Gatherling\Database;
use Gatherling\Decksearch;
use Gatherling\Pagination;

require_once 'lib.php';

function main(): void {
    ob_start();
    ?>
    <script src="/styles/Chandra/js/sorttable.js"></script>
    <div class="grid_10 suffix_1 prefix_1">
        <div id="gatherling_main" class="box">
            <div class="uppertitle group">Deck Search</div>
            <?php handleRequest(); ?>
        </div>
    </div>
    <?php
    echo page('Deck Search', ob_get_clean());
}

function handleRequest()
{
    if (count($_POST) > 0) {
        unset($_SESSION['search_results']);
    }

    $decksearch = new Decksearch();
    if (!isset($_GET['mode'])) {
        $_GET['mode'] = '';
    }

    if (strcmp($_GET['mode'], 'search') == 0 && !isset($_GET['page'])) {
        if (!empty($_POST['format'])) {
            $decksearch->searchByFormat($_POST['format']);
            $_SESSION['format'] = $_POST['format'];
        } else {
            unset($_SESSION['format']);
        }
        if (!empty($_POST['cardname'])) {
            $decksearch->searchByCardName($_POST['cardname']);
            $_SESSION['cardname'] = $_POST['cardname'];
        } else {
            unset($_SESSION['cardname']);
        }
        if (!empty($_POST['player'])) {
            $decksearch->searchByPlayer($_POST['player']);
            $_SESSION['player'] = $_POST['player'];
        } else {
            unset($_SESSION['player']);
        }
        if (!empty($_POST['archetype'])) {
            $decksearch->searchByArchetype($_POST['archetype']);
            $_SESSION['archetype'] = $_POST['archetype'];
        } else {
            unset($_SESSION['archetype']);
        }
        if (!empty($_POST['medals'])) {
            $decksearch->searchByMedals($_POST['medals']);
            $_SESSION['medals'] = $_POST['medals'];
        } else {
            unset($_SESSION['medals']);
        }
        if (!empty($_POST['series'])) {
            $decksearch->searchBySeries($_POST['series']);
            $_SESSION['series'] = $_POST['series'];
        } else {
            unset($_SESSION['series']);
        }
        if (isset($_POST['color'])) {
            $decksearch->searchByColor($_POST['color']);
            if (isset($_POST['color']['w'])) {
                $_SESSION['color']['w'] = $_POST['color']['w'];
            }
            if (isset($_POST['color']['b'])) {
                $_SESSION['color']['b'] = $_POST['color']['b'];
            }
            if (isset($_POST['color']['u'])) {
                $_SESSION['color']['u'] = $_POST['color']['u'];
            }
            if (isset($_POST['color']['g'])) {
                $_SESSION['color']['g'] = $_POST['color']['g'];
            }
            if (isset($_POST['color']['r'])) {
                $_SESSION['color']['r'] = $_POST['color']['r'];
            }
        } else {
            unset($_SESSION['color']);
        }
        $results = $decksearch->getFinalResults();
        if ($results) {
            $_SESSION['search_results'] = $results;
            showSearchForm(count($results));
            displayDecksFromID($results);
        } else {
            showSearchForm(0);
            foreach ($decksearch->errors as $value) {
                echo $value . '<br />';
            }
        }
    } else {
        if (isset($_GET['page']) && isset($_SESSION['search_results'])) {
            showSearchForm(count($_SESSION['search_results']));
            displayDecksFromID($_SESSION['search_results']);
        } else {
            unset($_SESSION['search_results']);
            unset($_SESSION['archetype']);
            unset($_SESSION['format']);
            unset($_SESSION['series']);
            unset($_SESSION['name']);
            unset($_SESSION['player']);
            unset($_SESSION['cardname']);
            unset($_SESSION['color']);
            showSearchForm();
            showMostPlayedDecks();
        }
    }
}

function showMostPlayedDecks()
{
    echo '<br />';
    echo '<div class="ds_inputbox group">';
    echo '<div class="uppertitle group"><center>Most Played Decks</center></div>';
    echo '<table id="table-decksearch"">';
    echo '<thead>';
    echo '<th>Played</th>';
    echo '<th>Player Name</th>';
    echo '<th>Deck Name</th>';
    echo '<th>Archetype</th>';
    echo '<th>Format</th>';
    echo '<th>Created</th>';
    echo '</thead>';
    echo '<tbody>';
    $db = Database::getConnection();
    $db->query("set session sql_mode='';"); // Disable ONLY_FULL_GROUP_BY
    $stmt = $db->prepare('SELECT count(d.deck_hash) as cnt, d.playername, d.name, d.archetype, d.format, d.created_date, d.id
                          FROM decks d, entries n
                          WHERE n.deck = d.id
                          AND 5 < (SELECT count(*)
                          FROM deckcontents
                          WHERE deck = d.id
                          GROUP BY deck)
                          GROUP BY d.deck_hash
                          ORDER BY cnt DESC
                          LIMIT 20');
    $stmt || exit($db->error);
    $stmt->execute();
    $stmt->bind_result($count, $playerName, $deckName, $archetype, $format, $created, $deckid);

    while ($stmt->fetch()) {
        echo "<td>{$count} times</td>";
        echo '<td><a href="profile.php?player=' . $playerName . '&mode=Lookup+Profile">' . $playerName . '</a></td>';
        echo '<td><a href="deck.php?mode=view&id=' . $deckid . '">' . $deckName . '</a></td>';
        echo '<td>' . $archetype . '</td>';
        echo '<td>' . $format . '</td>';
        echo '<td>' . time_element(strtotime($created), time()) . '</td>';
        echo '</tr>';
    }

    echo "<center>\n";
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '<br />';
}

function showSearchForm($res_num = null)
{
    echo '<br />';
    echo '<div class="ds_inputbox group">';
    echo '<div class="ds_left group">';
    echo '<form  method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?mode=search">';
    echo '<label class="ds_label" for="player">Player Name: <input class="ds_input" type="text" name="player"  value="';
    if (isset($_SESSION['player'])) {
        echo $_SESSION['player'];
    }
    echo '"/></label>';

    echo '<label class="ds_label" for="cardname">Card name:   <input class="ds_input" type="text" name="cardname" value="';
    if (isset($_SESSION['cardname'])) {
        echo $_SESSION['cardname'];
    }
    echo '"/></label>';

    echo '<div class="ds_select group">';
    if (isset($_SESSION['format'])) {
        formatDropMenuDS($_SESSION['format']);
    } else {
        formatDropMenuDS($arg = null);
    }

    if (isset($_SESSION['archetype'])) {
        archetypeDropMenu($_SESSION['archetype']);
    } else {
        archetypeDropMenu();
    }

    if (isset($_SESSION['series'])) {
        seriesDropMenu($_SESSION['series']);
    } else {
        seriesDropMenu();
    }

    if (isset($_SESSION['medals'])) {
        medalsDropMenu('medals', ['1st', '2nd', 't4', 't8'], $_SESSION['medals']);
    } else {
        medalsDropMenu('medals', ['1st', '2nd', 't4', 't8']);
    }

    echo '</div>';

    echo '<div class="ds_color">';
    if (isset($_SESSION['color'])) {
        checkboxMenu($_SESSION['color']);
    } else {
        checkboxMenu();
    }
    echo '</div>';

    echo '</div>';
    echo '<div class="ds_right group">';
    echo '<input class="ds_submit"type="submit" name="submitbuttom" value="Submit">';
    echo '<br /><center>';
    echo image_tag('search.png', ['class' => 'ds_image', 'height' => '75', 'width' => '75']);
    echo '</center>';
    echo '<div class="ds_results group">';
    if ($res_num) {
        echo $res_num . ' decks found';
    }
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
}

function checkboxMenu($colors = null)
{

    //check it see if the colors were set and check any boxes that were
    if (isset($colors['w'])) {
        $w = 'checked';
    } else {
        $w = '';
    }
    if (isset($colors['b'])) {
        $b = 'checked';
    } else {
        $b = '';
    }
    if (isset($colors['u'])) {
        $u = 'checked';
    } else {
        $u = '';
    }
    if (isset($colors['g'])) {
        $g = 'checked';
    } else {
        $g = '';
    }
    if (isset($colors['r'])) {
        $r = 'checked';
    } else {
        $r = '';
    }

    echo "<label class=\"ds_checkbox\" for=\"color[g]\"><input type=\"checkbox\" name=\"color[g]\" value=\"g\" $g/>" . image_tag('manag.png', ['class' => 'ds_mana_image']) . '</label>';
    echo "<label class=\"ds_checkbox\" for=\"color[u]\"><input type=\"checkbox\" name=\"color[u]\" value=\"u\" $u/>" . image_tag('manau.png', ['class' => 'ds_mana_image']) . '</label>';
    echo "<label class=\"ds_checkbox\" for=\"color[w]\"><input type=\"checkbox\" name=\"color[w]\" value=\"w\" $w/>" . image_tag('manaw.png', ['class' => 'ds_mana_image']) . '</label>';
    echo "<label class=\"ds_checkbox\" for=\"color[b]\"><input type=\"checkbox\" name=\"color[b]\" value=\"b\" $b/>" . image_tag('manab.png', ['class' => 'ds_mana_image']) . '</label>';
    echo "<label class=\"ds_checkbox\" for=\"color[r]\"><input type=\"checkbox\" name=\"color[r]\" value=\"r\" $r/>" . image_tag('manar.png', ['class' => 'ds_mana_image']) . '</label>';
}

function archetypeDropMenu($archetype = null, $useAll = 0, $form_name = 'archetype')
{
    $db = Database::getConnection();
    $query = 'SELECT name FROM archetypes WHERE priority > 0 ORDER BY  name';
    $result = $db->query($query) or exit($db->error);
    echo "<select id=\"ds_select\"  name=\"{$form_name}\">";
    $title = ($useAll == 0) ? '- Archetype -' : 'All';
    echo "<option value=\"\">$title</option>";
    while ($thisArchetype = $result->fetch_assoc()) {
        $name = $thisArchetype['name'];
        $selStr = (strcmp($name, $archetype) == 0) ? 'selected' : '';
        echo "<option value=\"$name\" $selStr>$name</option>";
    }
    $selStr = (strcmp('Unclassified', $archetype) == 0) ? 'selected' : '';
    echo "<option value=\"Unclassified\" $selStr>Unclassified</option>";
    echo '</select>';
    $result->close();
}

function seriesDropMenu($series = null, $useAll = 0, $form_name = 'series')
{
    $db = Database::getConnection();
    $query = 'SELECT name FROM series ORDER BY name';
    $result = $db->query($query) or exit($db->error);
    echo "<select id=\"ds_select\" name=\"{$form_name}\">";
    $title = ($useAll == 0) ? '- Series -' : 'All';
    echo "<option value=\"\">$title</option>";
    while ($thisSeries = $result->fetch_assoc()) {
        $name = $thisSeries['name'];
        $selStr = ($series && strcmp($name, $series) == 0) ? 'selected' : '';
        echo "<option value=\"$name\" $selStr>$name</option>";
    }
    echo '</select>';
    $result->close();
}

function medalsDropMenu($name, $options, $selected = null)
{
    echo "<select id=\"ds_select\" name=\"{$name}\">";
    echo '<option value="">- Medals -</option>';
    foreach ($options as $option) {
        $setxt = '';
        if (!is_null($selected) && $selected == $option) {
            $setxt = ' selected';
        }
        echo "<option value=\"{$option}\"{$setxt}>{$option}</option>";
    }
    echo '</select>';
}

function formatDropMenuDS($format, $useAll = 0, $form_name = 'format')
{
    if (is_null($format)) {
        $format = '';
    }

    $db = Database::getConnection();
    $query = 'SELECT name FROM formats ORDER BY priority desc, name';
    $result = $db->query($query) or exit($db->error);
    echo "<select id=\"ds_select\" name=\"{$form_name}\">";
    $title = ($useAll == 0) ? '- Format -' : 'All';
    echo "<option value=\"\">$title</option>";
    while ($thisFormat = $result->fetch_assoc()) {
        $name = $thisFormat['name'];
        $selStr = (strcmp($name, $format) == 0) ? 'selected' : '';
        echo "<option value=\"$name\" $selStr>$name</option>";
    }
    echo '</select>';
    $result->close();
}

function displayDecksFromID($id_arr)
{
    // grab an array of the values of the decks with matching id's
    $decksearch = new Decksearch();
    $ids_populated = $decksearch->idsToSortedInfo($id_arr);

    $records_per_page = 25;

    $pagination = new Pagination();
    $pagination->records(count($ids_populated));
    $pagination->records_per_page($records_per_page);
    $pagination->avoid_duplicate_content(false);

    //get the ids for the current page
    $ids_populated = array_slice($ids_populated, (($pagination->get_page() - 1)
        * $records_per_page), $records_per_page);

    echo '<br />';
    echo '<div class="ds_inputbox group">';
    echo '<table class="sortable" id="table-decksearch"">';
    echo '<thead>';
    echo '<th>Player Name</th>';
    echo '<th>Deck Name</th>';
    echo '<th>Archetype</th>';
    echo '<th>Format</th>';
    echo '<th>Created</th>';
    echo '<th>Win-Loss</th>';
    echo '</thead>';
    echo '<tbody>';

    $now = time();
    foreach ($ids_populated as $index => $deckinfo) { ?>
        <tr<?php echo $index % 2 ? ' class="even"' : '' ?>>
        <?php
        if (strlen($deckinfo['name']) > 23) {
            $deckinfo['name'] = preg_replace('/\s+?(\S+)?$/', '', substr($deckinfo['name'], 0, 22)) . '...';
        }
        echo '<td><a href="profile.php?player=' . $deckinfo['playername'] . '&mode=Lookup+Profile">' . $deckinfo['playername'] . '</a></td>';
        echo '<td><a href="deck.php?mode=view&id=' . $deckinfo['id'] . '">' . $deckinfo['name'] . '</a></td>';
        echo '<td>' . $deckinfo['archetype'] . '</td>';
        echo '<td>' . $deckinfo['format'] . '</td>';
        echo '<td>' . time_element(strtotime($deckinfo['created_date']), $now) . '</td>';
        echo '<td align=center>' . $deckinfo['record'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '<br />';
    $pagination->render();
    echo '<br />';
    echo '<br />';
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
