<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (file_exists('../lib.php')) {
    require_once '../lib.php';
} else {
    require_once 'gatherling/lib.php';
}

function info($text, $newline = true)
{
    if ($newline) {
        if (PHP_SAPI == 'cli') {
            echo "\n";
        } else {
            echo '<br/>';
        }
    }
    echo $text;
}

session_start();

if (PHP_SAPI == 'cli') {
    if (isset($argv[1])) {
        if (strlen($argv[1]) < 4) {
            $file = file_get_contents("https://mtgjson.com/json/{$argv[1]}.json");
        } else {
            $file = file_get_contents($argv[1]);
        }
    } else {
        exit('No set provided.');
    }
} else { // CGI
    ini_set('max_execution_time', 300);
    if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
        redirect('index.php');
    }

    if (isset($_REQUEST['cardsetcode'])) {
        if ($_REQUEST['cardsetcode'] == 'CON') { // Due to an ancient bug in MS-DOS, Windows-based computers can't handle Conflux correctly.
            $_REQUEST['cardsetcode'] = 'CON_';
        }

        $file = file_get_contents("https://mtgjson.com/json/{$_REQUEST['cardsetcode']}.json");
    } elseif (isset($_FILES['cardsetfile'])) {
        $file = file_get_contents($_FILES['cardsetfile']['tmp_name']);
    } else {
        exit('No set provided.');
    }
}

if ($file == false) {
    exit("Can't open the file you uploaded: {$_FILES['cardsetfile']['tmp_name']}");
}

$data = json_decode($file);

$set = $data->name;
if ($set == 'Time Spiral "Timeshifted"') {
    // Terrible hack, but needed.
    $set = 'Time Spiral Timeshifted';
}
$settype = $data->type;
switch ($settype) {
  case 'core':
  case 'starter':
    $settype = 'Core';
    break;
  case 'expansion':
    $settype = 'Block';
    break;
  default:
    $settype = 'Extra';
    break;
}
$releasedate = $data->releaseDate;

$card = [];
$rarity = 'Common';
$cardsparsed = 0;
$cardsinserted = 0;

$database = Database::getConnection();

$stmt = $database->prepare('SELECT * FROM cardsets where name = ?');

$stmt->bind_param('s', $set);

$set_already_in = false;

if (!$stmt->execute()) {
    info('!!!!!!!!!! Set Insertion Error !!!!!!!!!');
    exit($stmt->error);
} else {
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $set_already_in = true;

        $row = $result->fetch_array();
        if (is_null($row['code'])) {
            info("$set is missing code ($data->code) in db.");
            $stmt = $database->prepare('UPDATE cardsets SET code = ? WHERE name = ?');
            $stmt->bind_param('ss', $data->code, $row['name']);
            if (!$stmt->execute()) {
                info('!!!!!!!!!! Set Insertion Error !!!!!!!!!');
                exit($stmt->error);
            }
        }
    }
}

if (!$set_already_in) {
    echo "Inserting card set ($set, $releasedate, $settype)...<br />";

    // Insert the card set
    $stmt = $database->prepare('INSERT INTO cardsets(released, name, type, code) values(?, ?, ?, ?)');
    $stmt->bind_param('ssss', $releasedate, $set, $settype, $data->code);

    if (!$stmt->execute()) {
        echo '!!!!!!!!!! Set Insertion Error !!!!!!!!!<br /><br /><br />';
        exit($stmt->error);
    } else {
        echo "Inserted new set {$set}!<br /><br />";
    }
    $stmt->close();
}

$stmt = $database->prepare('INSERT INTO cards(cost, convertedcost, name, cardset, type,
  isw, isu, isb, isr, isg, isp, rarity, scryfallId, is_changeling, is_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`),
  isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
  `rarity` = VALUES(`rarity`),scryfallId = VALUES(`scryfallId`), is_changeling = VALUES(`is_changeling`), is_online = VALUES(`is_online`);');

foreach ($data->cards as $card) {
    $cardsparsed++;
    insertCard($card, $set, $card->rarity, $stmt);
    $cardsinserted++;
}

echo 'End of File Reached<br />';
echo "Total Cards Parsed: {$cardsparsed}<br />";
echo "Total Cards Inserted: {$cardsinserted}<br />";
$stmt->close();

Format::constructTribes($set);

function insertCard($card, $set, $rarity, $stmt)
{
    $typeline = implode($card->types, ' ');
    if (isset($card->subtypes) && count($card->subtypes) > 0) {
        $typeline = $typeline.' - '.implode($card->subtypes, ' ');
    }
    $name = $card->name;
    if ($card->layout == 'split' || $card->layout == 'aftermath') {
        // SPLIT CARDS!!!!!!
        $name = implode('/', $card->names);
        // TODO: Make sure we get the $ism flags right. (We currently don't)
    }
    $name = normaliseCardName($name);
    echo '<table class="new_card">';
    echo '<tr><th>Name:</th><td>'.$name.'</td></tr>';
    foreach (['manaCost', 'convertedManaCost', 'type', 'rarity'] as $attr) {
        if (isset($card->{$attr})) {
            echo "<tr><th>{$attr}:</th><td>".$card->{$attr}.'</td></tr>';
        }
    }
    echo '<tr><th>Card Colors:</th><td>';
    $isw = $isu = $isb = $isr = $isg = $isp = 0;
    if (isset($card->manaCost)) {
        if (preg_match('/W/', $card->manaCost)) {
            $isw = 1;
            echo 'White ';
        }
        if (preg_match('/U/', $card->manaCost)) {
            $isu = 1;
            echo 'Blue ';
        }
        if (preg_match('/B/', $card->manaCost)) {
            $isb = 1;
            echo 'Black ';
        }
        if (preg_match('/R/', $card->manaCost)) {
            $isr = 1;
            echo 'Red ';
        }
        if (preg_match('/G/', $card->manaCost)) {
            $isg = 1;
            echo 'Green ';
        }
        if (preg_match('/P/', $card->manaCost)) {
            $isp = 1;
            echo 'Phyrexian ';
        }
    }
    echo '</td></tr>';

    $changeling = 0;
    if (preg_match('/Creature|Tribal/', $typeline)) {
        $changeling = 1;
    }
    if (isset($card->text) && preg_match('/is every creature type/', $card->text)) {
        $changeling = 1;
    }

    $online = isset($card->isMtgo);

    $empty_string = '';
    $zero = 0;

    if (property_exists($card, 'manaCost')) {
        $stmt->bind_param('sdsssddddddssdd', $card->manaCost, $card->convertedManaCost, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
    } else {
        $stmt->bind_param('sdsssddddddssdd', $empty_string, $zero, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
    }

    if (!$stmt->execute()) {
        echo '<tr><td colspan="2" style="background-color: LightRed;">!!!!!!!!!! Card Insertion Error !!!!!!!!!</td></tr>';
        echo '</table>';
        exit($stmt->error);
    } else {
        echo '<tr><th colspan="2" style="background-color: LightGreen;">Card Inserted Successfully</th></tr>';
        echo '</table>';
    }
}

if (isset($_REQUEST['return'])) {
    $args = '';
    if (isset($_REQUEST['ret_args'])) {
        $args = $_REQUEST['ret_args'];
    }

    echo "Return to <a href='{$CONFIG['base_url']}{$_REQUEST['return']}?{$args}'>{$_REQUEST['return']}</a><br/>";
    echo '<script>';
    echo "  window.setTimeout(() => { location.href = \"{$CONFIG['base_url']}{$_REQUEST['return']}?{$args}\"}, 5000);";
    echo '</script>';
}
