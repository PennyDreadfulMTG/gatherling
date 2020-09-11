<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__.'/../lib.php';

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

$sets = [];
$db = Database::getConnection();
$stmt = $db->prepare('SELECT `name`, `code`, `released`, `type`, `last_updated` FROM `cardsets`');
$stmt->execute();
$stmt->bind_result($name, $code, $released, $type, $updated);
while ($stmt->fetch()) {
    $sets[$name] = ['name' => $name, 'code' => $code, 'released' => $released, 'type' => $type, 'last_updated' => $updated];
}
$stmt->close();

$client = new Ypho\Scryfall\Client();

// Fetches all sets
$collSets = $client->sets()->all();
$arrSets = $collSets->sets();
$now = time();
$threshold = $now - 60 * 60 * 24 * 1;
foreach ($arrSets as $set) {
    if (array_key_exists($set->name, $sets)) {
        /** @var int $updated */
        $updated = $sets[$set->name]['last_updated'];
        if (empty($updated) || $updated < $threshold) {
            info($set->name);
            info("-Last updated: $updated. Updating");
        } else {
            // info("-Last updated: $updated. Skipping");
            continue;
        }
    } else {
        // info('-Inserting new set');
        $name = $set->name;
        $releasedate = $set->release;
        $code = strtoupper($set->code);
        $settype = convert_settype($set->setType);
        if ($settype == 'Ignore') {
            // info("Skipping card set ($name, $releasedate, $settype, $code)");
            continue;
        }

        info("Inserting card set ($name, $releasedate, $settype, $code)...");

        // Insert the card set
        $stmt = $db->prepare('INSERT INTO cardsets(released, name, type, code) values(?, ?, ?, ?)');
        $stmt->bind_param('ssss', $releasedate, $name, $settype, $code);

        if (!$stmt->execute()) {
            info('!!!!!!!!!! Set Insertion Error !!!!!!!!!');

            throw new Exception($stmt->error, 1);
        } else {
            info("Inserted new set {$name}!");
        }
        $stmt->close();
    }
    sync($set->name, $set->getCards($client));
    flush();
    // return;
}

function convert_settype($sf_type)
{
    switch ($sf_type) {
        case 'core':
        case 'starter':
          return 'Core';

        case 'expansion':
          return 'Block';

        case 'token':
        case 'memorabilia':
            return 'Ignore';
          default:
          return 'Extra';
    }
}

function sync($setname, $cards)
{
    global $db;
    $stmt = $db->prepare('SELECT `id`, `name`, `scryfallId`, `type`, `is_online` FROM `cards` WHERE `name` = ? AND `cardset` = ?');
    if (!$stmt) {
        throw new Exception($db->error, 1);
    }
    $newCards = [];
    $names = [];
    foreach ($cards as $c) {
        $name = normaliseCardName($c->name);
        if ($c->layout == 'flip' || $c->layout == 'adventure') {
            $name = explode('/', $name)[0];
            $c->name = $name;
        }
        if (in_array($name, $names)) {
            continue;
        }
        if ($c->borderColor == 'silver' || $c->borderColor == 'gold') {
            continue;
        } // Not worth my time right now.
        info($name);
        $names[] = $name; // Ignore Borderless/promo/whatnots
        $typeline = str_replace('—', '-', $c->type);
        $stmt->bind_param('ss', $name, $setname);
        $stmt->execute();
        $stmt->bind_result($id, $name, $printing_id, $type, $is_online);
        $exists = $stmt->fetch();
        if (is_null($exists)) {
            $newCards[] = $c;
            info('New');
        } elseif ($exists == false) {
            throw new Exception($stmt->error, 1);
        } elseif ($printing_id != $c->idScryfall) {
            info('Needs Updating');
            info("$printing_id != $c->idScryfall");
            $newCards[] = $c;
        } elseif ($typeline != $type) {
            info('Needs Updating');
            info("$typeline != $type");
            $newCards[] = $c;
        } elseif ($is_online != boolval($c->idMtgo)) {
            info('Needs Updating');
            info("$is_online != boolval($c->idMtgo)");
            $newCards[] = $c;
        } else {
            info('Okay');
        }
    }
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO cards(cost, convertedcost, name, cardset, type,
            isw, isu, isb, isr, isg, isp, rarity, scryfallId, is_changeling, is_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`), `name` = VALUES(`name`),
            isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
            `rarity` = VALUES(`rarity`), scryfallId = VALUES(`scryfallId`), is_changeling = VALUES(`is_changeling`), is_online = VALUES(`is_online`);');
    foreach ($newCards as $c) {
        $typeline = str_replace('—', '-', $c->type);
        insertCard($c, $setname, $typeline, $stmt);
    }
    $stmt->close();
    Database::no_result_single_param('UPDATE `cardsets` SET last_updated = UNIX_TIMESTAMP() WHERE `name` = ?;', 's', $setname);
}

function insertCard($card, $set, $typeline, $stmt)
{
    $name = $card->name;
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
    if (isset($card->text) && preg_match('/is every creature type/', $card->text)) {
        $changeling = 1;
    }

    $online = isset($card->isMtgo);

    $empty_string = '';
    $zero = 0;

    if (property_exists($card, 'manaCost')) {
        $stmt->bind_param('sdsssddddddssdd', $card->manaCost, $card->cmc, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $card->rarity, $card->idScryfall, $changeling, $online);
    } else {
        $stmt->bind_param('sdsssddddddssdd', $empty_string, $zero, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $card->rarity, $card->idScryfall, $changeling, $online);
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
