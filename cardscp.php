<?php
include 'lib.php';
include 'lib_form_helper.php';
session_start();

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

    if ($view == 'no_view') {
        // Show Nothing
    }
    else if ($view == 'list_sets') {
        printSetList();
    }
    else if ($view == 'edit_set') {
        printEditSet();
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
    }
    else if ($_POST['action'] == "modify_set") {
        if (isset($_POST['delentries'])) {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM `cards` WHERE `id` = ?');
            $stmt->bind_param('d', $playername);
            foreach ($_POST['delentries'] as $playername) {
                if (!$stmt->execute()){
                        global $hasError;
                        global $errormsg;
                        $hasError = true;
                        $errormsg = "<center>$stmt->error<br />";
                }

            }
        }

    }
}

function check_for_unique_cards_constraint() {
    global $CONFIG;
    $has_constraint = Database::single_result_single_param("SELECT COUNT(*)
                                                            FROM information_schema.TABLE_CONSTRAINTS
                                                            WHERE `CONSTRAINT_NAME` = 'unique_index' and TABLE_NAME = 'cards'
                                                            and TABLE_SCHEMA = ?;", 's', $CONFIG['db_database']);
    if  ($has_constraint == 1)
        return true;
    $db = Database::getConnection();

    $result = $db->query("ALTER TABLE `cards` ADD UNIQUE `unique_index`(`name`, `cardset`);");
    if (!$result){
        echo "<div class=\"error\">Warning! Cards table has no Unique constraint.<br/>$db->error</div>";
        return false;
    }
    return true;
}

function printSetList() {
    check_for_unique_cards_constraint();

    $sets = array();
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT `name`, `code`, `released`, `type` FROM gatherling.cardsets");
    $stmt->execute();
    $stmt->bind_result($name, $code, $released, $type);
    while ($stmt->fetch()) {
        $sets[] = array('name' => $name, 'code' => $code, 'released' => $released, 'type' => $type);
    }
    $stmt->close();

    echo '<table>';
    echo '<tr><th>Name</th><th>Code</th><th>Release Date</th><th>Set Type</th><th># Cards</th></tr>';
    foreach ($sets as $set) {
        $count = Database::single_result_single_param('SELECT COUNT(*) FROM `cards` WHERE `cardset` = ?', 's', $set['name']);
        echo "<tr><td><a href='cardscp.php?view=edit_set&set={$set['name']}'>{$set['name']}</a></td>";
        echo "<td>{$set['code']}</td><td>{$set['released']}</td><td>{$set['type']}</td><td>$count</td></tr>";
    }
    echo '</table>';
}

function printEditSet() {
    $is_unique = check_for_unique_cards_constraint();
    $names = array();

    $set = $_REQUEST['set'];
    echo "<h4>Editing '$set'</h4>";

    $cards = array();
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT `id`, `name`, `type`, `rarity`, `scryfallId` FROM `cards` WHERE `cardset` = ?");
    $stmt->bind_param('s', $set);
    $stmt->execute();
    $stmt->bind_result($id, $name, $type, $rarity, $sfId);
    while ($stmt->fetch()) {
        $cards[] = array('name' => $name, 'type' => $type, 'rarity' => $rarity, 'id' => $id, 'sfId' => $sfId);
    }
    $stmt->close();
    echo '<form action="cardscp.php" method="post"><table>';
    echo "<input type=\"hidden\" name=\"view\" value=\"edit_set\" />";
    echo "<input type=\"hidden\" name=\"set\" value=\"$set\" />";
    echo "<input type=\"hidden\" name=\"action\" value=\"modify_set\" />";

    echo '<tr><th>ID</th><th>Name</th><th>Type</th><th>Rarity</th><th># of Decks</th><th>Delete</th></tr>';
    foreach ($cards as $card) {
        $count = Database::single_result_single_param('SELECT COUNT(*) FROM `deckcontents` WHERE card = ?;', 'd', $card['id']);
        echo "<tr><td>{$card['id']}</td><td>{$card['name']}</td>";
        echo "<td>{$card['type']}</td><td>{$card['rarity']}</td><td>$count</td>";
        $checked = false;
        if (!$is_unique) {
            if(in_array($card['name'], $names) && $count == 0) {
                $checked = true;
            }
            $names[] = $card['name'];
        }
        echo "<td><input type=\"checkbox\" name=\"delentries[]\" value=\"{$card['id']}\"";
        if ($checked) {
            echo ' checked="yes"';
        }
        echo " /></td></tr>";
    }
    echo '</table>';
    echo '<input id="update_reg" class="inputbutton" type="submit" name="mode" value="Modify Set" />';
    echo '</form>';

}
