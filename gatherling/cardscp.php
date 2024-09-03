<?php

namespace Gatherling;

include 'lib.php';
include 'lib_form_helper.php';

$hasError = false;
$errormsg = '';

if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
    redirect('index.php');
}

print_header('Admin Control Panel');
?>

<div class="grid_10 suffix_1 prefix_1">
  <div id="gatherling_main" class="box">
    <div class="uppertitle"> Admin Control Panel </div>
    <center>
      <?php do_page(); ?>
    </center>
    <div class="clear"></div>
  </div>
</div>

<?php print_footer(); ?>

<?php

function do_page()
{
    $player = Player::getSessionPlayer();
    if (!$player->isSuper()) {
        printNoAdmin();

        return;
    }

    echo 'Welcome to the Database Viewer! <br />';
    handleActions();
    printError();
    cardsCPMenu();

    $view = 'list_sets';

    if (isset($_GET['view']) && ($_GET['view'] != '')) {
        $view = $_GET['view'];
    }
    if (isset($_POST['view'])) {
        $view = $_POST['view'];
    }

    switch ($view) {
        case 'edit_card':
            printEditCard();
            break;
        case 'edit_set':
            printEditSet();
            break;
        case 'list_sets':
            printSetList();
            break;
        case 'no_view':
        default:
            break;
    }
}

function printNoAdmin()
{
    global $hasError;
    global $errormsg;
    $hasError = true;
    $errormsg = "<center>You're not an Admin here on Gatherling.com! Access Restricted.<br />";
    printError();
    echo '<a href="player.php">Back to the Player Control Panel</a></center>';
}
function printError()
{
    global $hasError;
    global $errormsg;
    if ($hasError) {
        echo "<div class=\"error\">{$errormsg}</div>";
    }
}

function cardsCPMenu()
{
    echo '<table><tr><td colspan="2" align="center">';
    echo '<a href="cardscp.php?view=list_sets">List Card Sets</a>';
    echo ' | <a href="admincp.php?view">Back to AdminCP</a>';
    echo '</td></tr></table>';
}

function handleActions()
{
    if (!isset($_POST['action'])) {
        return;
    } elseif ($_POST['action'] == 'modify_set') {
        if (isset($_POST['delentries'])) {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM `cards` WHERE `id` = ?');
            $stmt->bind_param('d', $playername);
            foreach ($_POST['delentries'] as $playername) {
                if (!$stmt->execute()) {
                    global $hasError;
                    global $errormsg;
                    $hasError = true;
                    $errormsg = "<center>$stmt->error<br />";
                }
            }
        }
    } elseif ($_POST['action'] == 'modify_card') {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE `cards` SET `name` = ?, `type` = ?, `rarity` = ?, `scryfallId` = ? WHERE `id` = ?');
        if (!$stmt) {
            echo $db->error;
        }
        $stmt->bind_param('ssssd', $_REQUEST['name'], $_REQUEST['type'], $_REQUEST['rarity'], $_REQUEST['sfId'], $_REQUEST['id']);
        $stmt->execute();
    }
}

function check_for_unique_cards_constraint()
{
    global $CONFIG;
    $has_constraint = Database::single_result_single_param("SELECT COUNT(*)
                                                            FROM information_schema.TABLE_CONSTRAINTS
                                                            WHERE `CONSTRAINT_NAME` = 'unique_index' and TABLE_NAME = 'cards'
                                                            and TABLE_SCHEMA = ?;", 's', $CONFIG['db_database']);
    if ($has_constraint == 1) {
        return true;
    }
    $db = Database::getConnection();

    $result = $db->query('ALTER TABLE `cards` ADD UNIQUE `unique_index`(`name`, `cardset`);');
    if (!$result) {
        echo "<div class=\"error\">Warning! Cards table has no Unique constraint.<br/>$db->error</div>";

        return false;
    }

    return true;
}

function printSetList()
{
    check_for_unique_cards_constraint();

    $sets = [];
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT `name`, `code`, `released`, `type`, `last_updated` FROM `cardsets`');
    $stmt->execute();
    $stmt->bind_result($name, $code, $released, $type, $updated);
    while ($stmt->fetch()) {
        $sets[] = ['name' => $name, 'code' => $code, 'released' => $released, 'type' => $type, 'last_updated' => $updated];
    }
    $stmt->close();

    echo '<table>';
    echo '<tr><th>Name</th><th>Code</th><th>Release Date</th><th>Set Type</th><th># Cards</th><th>Last Updated</th></tr>';
    foreach ($sets as $set) {
        $count = Database::single_result_single_param('SELECT COUNT(*) FROM `cards` WHERE `cardset` = ?', 's', $set['name']);
        echo "<tr><td><a href='cardscp.php?view=edit_set&set={$set['name']}'>{$set['name']}</a></td>";
        echo "<td>{$set['code']}</td><td>{$set['released']}</td><td>{$set['type']}</td><td>$count</td><td>{$set['last_updated']}</td></tr>";
    }
    echo '</table>';
}

function printEditSet()
{
    $is_unique = check_for_unique_cards_constraint();
    $names = [];

    $set = $_REQUEST['set'];
    echo "<h4>Editing '$set'</h4>";

    echo '<form action="cardscp.php" method="post"><table>';
    echo '<input type="hidden" name="view" value="edit_set" />';
    echo "<input type=\"hidden\" name=\"set\" value=\"$set\" />";
    echo '<input type="hidden" name="action" value="modify_set" />';

    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT `code`, `released`, `standard_legal`, `modern_legal` FROM `cardsets` WHERE `name` = ?');
    $stmt->bind_param('s', $set);
    $stmt->execute();
    $stmt->bind_result($setcode, $released, $standard_legal, $modern_legal);
    $stmt->fetch();
    $stmt->close();

    echo textInput('Set Code', 'code', $setcode);
    echo textInput('Release Date', 'released', $released);
    echo checkboxInput('Standard Legal', 'standard_legal', $standard_legal);
    echo checkboxInput('Modern Legal', 'modern_legal', $modern_legal);

    $cards = [];
    $stmt = $db->prepare('SELECT `id`, `name`, `type`, `rarity`, `scryfallId` FROM `cards` WHERE `cardset` = ?');

    $stmt->bind_param('s', $set);
    $stmt->execute();
    $stmt->bind_result($id, $name, $type, $rarity, $sfId);
    while ($stmt->fetch()) {
        $cards[] = ['name' => $name, 'type' => $type, 'rarity' => $rarity, 'id' => $id, 'sfId' => $sfId];
    }
    $stmt->close();

    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Rarity</th><th># of Decks</th><th>Delete</th></tr>\n";
    foreach ($cards as $card) {
        $count = Database::single_result_single_param('SELECT COUNT(*) FROM `deckcontents` WHERE card = ?;', 'd', $card['id']);
        echo "<tr><td>{$card['id']}</td><td>{$card['name']}</td>";
        echo "<td>{$card['type']}</td><td>{$card['rarity']}</td><td>$count</td>";
        $checked = false;
        if (!$is_unique) {
            if (in_array($card['name'], $names) && $count == 0) {
                $checked = true;
            }
            $names[] = $card['name'];
        }
        echo "<td><input type=\"checkbox\" name=\"delentries[]\" value=\"{$card['id']}\"";
        if ($checked) {
            echo ' checked="yes"';
        }
        echo " /></td><td><a href='cardscp.php?view=edit_card&id={$card['id']}'>Edit</a></td>";
        echo "</tr>\n";
    }
    echo '</table>';
    echo '<input id="update_reg" class="inputbutton" type="submit" name="mode" value="Modify Set" />';
    echo '</form>';
}

function printEditCard()
{
    $id = $_REQUEST['id'];

    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT `id`, `name`, `type`, `rarity`, `scryfallId`, `is_changeling`, `cardset` FROM `cards` WHERE `id` = ?');

    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $type, $rarity, $sfId, $is_changeling, $cardset);
    $stmt->fetch();
    $stmt->close();

    echo "<h4>Editing '$id' ($cardset)</h4>";

    echo '<form action="cardscp.php" method="post"><table>';
    echo '<input type="hidden" name="view" value="edit_card" />';
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo '<input type="hidden" name="action" value="modify_card" />';

    echo '<table style="border-width: 0px" align="center">';

    echo textInput('Card Name', 'name', $name, 100);
    echo textInput('Typeline', 'type', $type, 100);
    echo textInput('Rarity', 'rarity', $rarity);
    echo textInput('Scryfall ID', 'sfId', $sfId, 36);
    echo checkboxInput('Changeling', 'is_changeling', $is_changeling);

    echo '</table>';
    echo '<input id="update_reg" class="inputbutton" type="submit" name="mode" value="Update Card" />';
    echo '</form>';

    if (str_contains($type, 'Creature')) {
        $creatureType = Format::removeTypeCrap($type);
        echo "Calculated Tribe(s): $creatureType";
    }
}
