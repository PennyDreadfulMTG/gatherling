<?php

ini_set('max_execution_time', 300);

// Upgrades the database.  There are a couple of pretty crude checks for
// versions 0 (no database) and 1 (no version table).  Hopefully it will
// work for you, but you can always just run the schema yourself.
//
// Use at your own risk!

// If this fails with foreign key errors, execute `ALTER SCHEMA <gatherling> DEFAULT CHARACTER SET latin1;`

if (file_exists('../lib.php')) {
    require_once '../lib.php';
} else {
    require_once 'lib.php';
}

session_start();
error_reporting(E_ALL);

$db = Database::getConnection();

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

function do_query($query)
{
    if (!trim($query)) {
        // Ignore empty queries
        return;
    }
    global $db;
    info("Executing Query: $query");
    $result = $db->query($query);
    if (!$result) {
        echo '!!!! - Error: ';
        echo $db->error;
        exit(0);
    }

    return $result;
}

function set_version($version)
{
    do_query("UPDATE db_version SET version = {$version}");
    global $db;
    $db->commit();
    info("... DB now at version {$version}!");
}

function redirect_deck_update($latest_id = 0)
{
    $url = explode('?', $_SERVER['REQUEST_URI']);
    $url = $url[0].'?deckupdate='.$latest_id;
    echo "<a href=\"{$url}\">Continue</a>";
    echo "<script type=\"text/javascript\"> window.location = \"http://{$_SERVER['SERVER_NAME']}$url\"; </script>";
    exit(0);
}

if (isset($_GET['deckupdate'])) {
    $deckquery = do_query('SELECT id FROM decks WHERE id > '.$_GET['deckupdate']);
    $timestart = time();
    while ($deckid = $deckquery->fetch_array()) {
        flush();
        $deck = new Deck($deckid[0]);
        $deck->save();
        flush();
        if ((time() - $timestart) > 5) {
            echo "-> Updating decks, ID: {$deck->id}... <br />";
            redirect_deck_update($deck->id);
        }
    }
    echo 'Done with deck updates...<br />';
    // exit(0);
}

// Check for version 0.  (no players table)

if (!$db->query('SELECT name FROM players LIMIT 1')) {
    // Version 0.  Enter the whole schema.
    info('DETECTED NO DATABASE.  Importing schema.sql');
    if (file_exists('../schema.sql')) {
        $schema = file_get_contents('../schema.sql');
    } elseif (file_exists('schema.sql')) {
        $schema = file_get_contents('schema.sql');
    } else {
        die('Could not find schema.sql');
    }
    $split = explode(';', $schema);
    foreach ($split as $line) {
        do_query($line);
    }
} elseif (!$db->query('SELECT version FROM db_version LIMIT 1')) {
    // Version 1.  Add our version table.
    info('Detected VERSION 1 DATABASE. Marking as such..');
    $db->query('CREATE TABLE db_version (version integer);');
    set_version(1);
}

if (!isset($_GET['version'])) {
    $result = do_query('SELECT version FROM db_version LIMIT 1');
    $obj = $result->fetch_object();
    $version = $obj->version;
} else {
    $version = $_GET['version'];
}

$db->autocommit(false);

if ($version < 2) {
    info('Updating to version 2..');
    // Version 2 Changes:
    //  - Add 'mtgo_confirmed', 'mtgo_challenge' field to players, and initialize them
    do_query('ALTER TABLE players ADD COLUMN (mtgo_confirmed tinyint(1), mtgo_challenge varchar(5))');
    do_query('UPDATE players SET mtgo_confirmed = 0');
    do_query('UPDATE players SET mtgo_challenge = NULL');
    //  - Add 'deck_hash', 'sideboard_hash' and 'whole_hash' to decks, and initialize them
    do_query('ALTER TABLE decks ADD COLUMN (deck_hash varchar(40), sideboard_hash varchar(40), whole_hash varchar(40))');
    $deckquery = do_query('SELECT id FROM decks');
    while ($deckid = $deckquery->fetch_array()) {
        $deck = new Deck($deckid[0]);
        $deck->calculateHashes();
        info("-> Calculating deck hash for {$deck->id}...");
        flush();
    }

    //  - Add 'notes' to entries and copy the current notes in the decks
    do_query('ALTER TABLE entries ADD COLUMN (notes text)');
    do_query('UPDATE entries e, decks d SET e.notes = d.notes WHERE e.deck = d.id');

    //  - and of course, set the version number to 2.
    set_version(2);
}

if ($version < 3) {
    echo 'Updating to version 3... <br />';
    // Version 3 Changes:
    //  - Add "series_stewards" table with playername, series name.
    //  - Add "day" and "time" to "series" table to track when they start (eastern times)
    do_query('CREATE TABLE series_stewards (player varchar(40), series varchar(40), FOREIGN KEY (player) REFERENCES players(name), FOREIGN KEY (series) REFERENCES series(name))');
    do_query("ALTER TABLE series ADD COLUMN (day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), normalstart TIME)");
    do_query('UPDATE db_version SET version = 3');
    $db->commit();
    echo '... DB now at version 3! <br />';
}

if ($version < 4) {
    echo 'Updating to version 4... <br />';
    // Version 4 changes:
    //  - Add "series_seasons" table for tracking seasons in a series, and "standard" rewards for each season.
    //  - Add "player_points" table for tracking "extra" player points.
    do_query('CREATE TABLE series_seasons (series varchar(40), season integer, first_pts integer, second_pts integer, semi_pts integer, quarter_pts integer, participation_pts integer, rounds_pts integer, decklist_pts integer, win_pts integer, loss_pts integer, bye_pts integer, FOREIGN KEY (series) REFERENCES series(name), PRIMARY KEY(series, season))');
    do_query('CREATE TABLE season_points (series varchar(40), season integer, event varchar(40), player varchar(40), adjustment integer, reason varchar(140), FOREIGN KEY (series) REFERENCES series(name), FOREIGN KEY (event) REFERENCES events(name), FOREIGN KEY (player) REFERENCES players(name))');

    do_query('UPDATE db_version SET version = 4');
    $db->commit();
    echo '... DB now at version 4! <br />';
}

if ($version < 5) {
    echo 'Updating to version 5... <br />';

    // Version 5 changes:
    //  - Add "must_decklist" column for series_seasons.
    //  - Add "cutoff_ord" column for series_seasons.
    do_query('ALTER TABLE series_seasons ADD COLUMN must_decklist integer');
    do_query('ALTER TABLE series_seasons ADD COLUMN cutoff_ord integer');

    do_query('UPDATE db_version SET version = 5');
    $db->commit();
    echo '... DB now at version 5! <br />';
}

if ($version < 6) {
    echo 'Updting to version 6... <br />';

    // Version 6 changes:
    //  - Add "format" column for series_seasons.
    //  - Add "master_link" column for series_seasons.
    do_query('ALTER TABLE series_seasons ADD COLUMN format varchar(40)');
    do_query('ALTER TABLE series_seasons ADD COLUMN master_link varchar(140)');
    do_query('UPDATE db_version SET version = 6');
    $db->commit();
    echo '... DB now at version 6! <br />';
}

if ($version < 7) {
    echo 'Updating to version 7... <br />';

    do_query("UPDATE decks SET archetype = 'Unclassified' WHERE archetype = 'Rogue'");
    do_query("UPDATE archetypes SET name = 'Unclassified' WHERE name = 'Rogue'");
    do_query('ALTER TABLE events MODIFY COLUMN name VARCHAR(80)');
    do_query('UPDATE db_version SET version = 7');
    $db->commit();
    echo '... DB now at version 7! <br />';
}

if ($version < 8) {
    echo 'Updating to version 8 (alter tables that reference event to have longer name too, make trophy image column larger).... <br />';

    do_query('ALTER TABLE entries MODIFY COLUMN event VARCHAR(80)');
    do_query('ALTER TABLE trophies MODIFY COLUMN event VARCHAR(80)');
    do_query('ALTER TABLE trophies MODIFY COLUMN image MEDIUMBLOB');
    do_query('ALTER TABLE subevents MODIFY COLUMN parent VARCHAR(80)');
    do_query('ALTER TABLE stewards MODIFY COLUMN event VARCHAR(80)');
    do_query('UPDATE db_version SET version = 8');
    $db->commit();
    echo '... DB now at version 8! <br />';
}

if ($version < 9) {
    echo 'Updating to version 9 (add deck contents cache column for searching, series logo column larger).... <br />';
    do_query('ALTER TABLE decks ADD COLUMN (deck_contents_cache text)');
    do_query('ALTER TABLE series MODIFY COLUMN logo MEDIUMBLOB');
    do_query('UPDATE db_version SET version = 9');
    $db->commit();
    echo '... DB now at version 9! <br />';
    redirect_deck_update();
}

if ($version < 10) {
    echo 'Updating to version 10 (add database stuff for pre-registration)... <br />';
    do_query('ALTER TABLE events ADD COLUMN (prereg_allowed INTEGER DEFAULT 0)');
    do_query('ALTER TABLE series ADD COLUMN (prereg_default INTEGER DEFAULT 0)');
    do_query('ALTER TABLE entries ADD COLUMN (registered_at DATETIME)');
    do_query('UPDATE db_version SET version = 10');
    $db->commit();
    echo '... DB now at version 10! <br />';
}

if ($version < 11) {
    // Match Pairing Updates
    // Reconstructed from schemas.
    echo 'Updating to version 11 (add pairing system stuff, cards)... <br />';
    do_query("ALTER TABLE cards ADD COLUMN (isp TINYINT(1) DEFAULT '0')");
    do_query('ALTER TABLE cards ADD COLUMN (rarity VARCHAR(40) DEFAULT NULL)');

    do_query("ALTER TABLE events ADD COLUMN (active TINYINT(1) DEFAULT '0')");
    do_query('ALTER TABLE events ADD COLUMN (current_round TINYINT(3) NOT NULL)');
    do_query("ALTER TABLE events ADD COLUMN (player_reportable TINYINT(1) NOT NULL DEFAULT '0')");

    do_query("ALTER TABLE matches ADD COLUMN (playera_wins INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (playera_losses INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (playera_draws INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (playerb_wins INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (playerb_losses INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (playerb_draws INT(11) NOT NULL DEFAULT '0')");
    do_query("ALTER TABLE matches ADD COLUMN (verification varchar(40) NOT NULL DEFAULT 'unverified')");
    // Automatically verify all the ones that are in here already
    do_query("UPDATE matches SET verification = 'verified'");

    do_query(<<<'EOS'
CREATE TABLE IF NOT EXISTS `standings` (
  `player` varchar(40) DEFAULT NULL,
  `event` varchar(40) DEFAULT NULL,
  `active` tinyint(3) DEFAULT '0',
  `matches_played` tinyint(3) DEFAULT '0',
  `games_won` tinyint(3) DEFAULT '0',
  `games_played` tinyint(3) DEFAULT '0',
  `byes` tinyint(3) DEFAULT '0',
  `OP_Match` decimal(3,3) DEFAULT '0.000',
  `PL_Game` decimal(3,3) DEFAULT '0.000',
  `OP_Game` decimal(3,3) DEFAULT '0.000',
  `score` tinyint(3) DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seed` tinyint(3) NOT NULL,
  `matched` tinyint(1) NOT NULL,
  `matches_won` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  KEY `event` (`event`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=266 ;
EOS
);

    do_query('UPDATE db_version SET version = 11');
    $db->commit();
    echo '... DB now at version 11! <br />';
}

if ($version < 12) {
    // Fixes to the pairing updates
    echo 'Updating to version 12 (final changes for the pairing)... <br />';
    do_query('ALTER TABLE matches MODIFY COLUMN result VARCHAR(5) not null');
    do_query('UPDATE db_version SET version = 12');

    $db->commit();
    echo '... DB now at version 12! <br />';
}

if ($version < 13) {
    echo 'Updating to version 13 (add players_editdecks to events)... <br />';
    do_query("ALTER TABLE events ADD COLUMN player_editdecks TINYINT(1) NOT NULL DEFAULT '1'");
    do_query('UPDATE events SET player_editdecks = (finalized != 1)');

    do_query('UPDATE db_version SET version = 13');
    $db->commit();
    echo '... DB now at version 13! <br />';
}

if ($version < 14) {
    echo 'Updating to version 14 (add drop_round to entries)... <br />';
    do_query('ALTER TABLE entries ADD COLUMN drop_round INTEGER');

    do_query('UPDATE db_version SET version = 14');
    $db->commit();
    echo '... DB now at version 14! <br />';
}

if ($version < 15) {
    echo "Updating to version 15 (Reverse-engineered migrations from Dabil's tweaking)... <br />";
    do_query('ALTER TABLE `players` ADD COLUMN `rememberme` INT NULL AFTER `mtgo_challenge`, ADD COLUMN `ipaddress` INT NULL AFTER `rememberme`, ADD COLUMN `pkmember` INT NULL AFTER `ipaddress`;');
    do_query('ALTER TABLE `events`
            ADD COLUMN `pkonly` TINYINT(3) NULL AFTER `prereg_allowed`;');
    do_query('ALTER TABLE `series` ADD COLUMN `pkonly_default` TINYINT(1) NULL AFTER `prereg_default`;');
    do_query('ALTER TABLE `series_stewards` RENAME TO  `series_organizers`;');
    do_query('ALTER TABLE `formats`
    ADD COLUMN `type` VARCHAR(45) NULL AFTER `priority`,
    ADD COLUMN `series_name` VARCHAR(40) NULL AFTER `type`,
    ADD COLUMN `singleton` TINYINT(3) NULL AFTER `series_name`,
    ADD COLUMN `commander` TINYINT(3) NULL AFTER `singleton`,
    ADD COLUMN `planechase` TINYINT(3) NULL AFTER `commander`,
    ADD COLUMN `vanguard` TINYINT(3) NULL AFTER `planechase`,
    ADD COLUMN `prismatic` TINYINT(3) NULL AFTER `vanguard`,
    ADD COLUMN `allow_commons` TINYINT(3) NULL AFTER `prismatic`,
    ADD COLUMN `allow_uncommons` TINYINT(3) NULL AFTER `allow_commons`,
    ADD COLUMN `allow_rares` TINYINT(3) NULL AFTER `allow_uncommons`,
    ADD COLUMN `allow_mythics` TINYINT(3) NULL AFTER `allow_rares`,
    ADD COLUMN `allow_timeshifted` TINYINT(3) NULL AFTER `allow_mythics`,
    ADD COLUMN `min_main_cards_allowed` INT NULL AFTER `allow_timeshifted`,
    ADD COLUMN `max_main_cards_allowed` INT NULL AFTER `min_main_cards_allowed`,
    ADD COLUMN `min_side_cards_allowed` INT NULL AFTER `max_main_cards_allowed`,
    ADD COLUMN `max_side_cards_allowed` INT NULL AFTER `min_side_cards_allowed`;');
    // This should 100% be done with a JOIN, but it wasn't, and I'm not going to break stuff refactoring yet.
    do_query('ALTER TABLE `bans` ADD COLUMN `card_name` VARCHAR(40) NULL AFTER `allowed`;');
    do_query("CREATE TABLE `restricted` (
    `card` bigint(20) unsigned NOT NULL,
    `format` varchar(40) NOT NULL,
    `allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
    `card_name` varchar(40) DEFAULT NULL,
    PRIMARY KEY (`card`,`format`),
    KEY `format` (`format`),
    FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    do_query('CREATE TABLE `deckerrors` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `deck` BIGINT(20) NOT NULL,
    `error` TEXT NOT NULL,
    PRIMARY KEY (`id`));');
    do_query("ALTER TABLE `standings` ADD COLUMN `draws` TINYINT(3) NULL DEFAULT '0' AFTER `matches_won`;");
    do_query('ALTER TABLE `ratings`
  ADD COLUMN `event` VARCHAR(80) NULL AFTER `losses`;');
    do_query('UPDATE db_version SET version = 15');
    $db->commit();
    echo '... DB now at version 15! <br />';
}
if ($version < 16) {
    echo 'Updating to version 16 (add code to cardsets)... <br />';
    do_query('ALTER TABLE cardsets ADD COLUMN code VARCHAR(3)');
    do_query('UPDATE db_version SET version = 16');
    $db->commit();
    echo '... DB now at version 16! <br />';
}
if ($version < 17) {
    /// The below migrations don't really work, and I'm inclined to just drop the db to push it through.
    /// If you have real data, maybe try to get it working?
    echo 'Updating to version 17 (Add unique constraint to cards)... <br />';

    // do_query("SET FOREIGN_KEY_CHECKS=0;");
    // do_query("CREATE TEMPORARY TABLE `cards_tmp` (
    //   `cost` varchar(40) DEFAULT NULL,
    //   `convertedcost` tinyint(3) unsigned NOT NULL DEFAULT '0',
    //   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    //   `isw` tinyint(1) DEFAULT '0',
    //   `isr` tinyint(1) DEFAULT '0',
    //   `isg` tinyint(1) DEFAULT '0',
    //   `isu` tinyint(1) DEFAULT '0',
    //   `isb` tinyint(1) DEFAULT '0',
    //   `name` varchar(40) NOT NULL,
    //   `cardset` varchar(40) NOT NULL,
    //   `type` varchar(40) NOT NULL,
    //   `isp` tinyint(1) DEFAULT '0',
    //   `rarity` varchar(40) DEFAULT NULL,
    //   PRIMARY KEY (`id`),
    //   KEY `cardset` (`cardset`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    // $select = $db->prepare("SELECT cost, convertedcost, name, cardset, type,
    // isw, isu, isb, isr, isg, isp, rarity FROM `cards`;");
    // $insert = $db->prepare("INSERT INTO cards_tmp(cost, convertedcost, name, cardset, type,
    //   isw, isu, isb, isr, isg, isp, rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    //   ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`),
    //     isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
    //     `rarity` = VALUES(`rarity`);");

    // $select->execute();
    // $select->bind_result($cost, $cmc, $name, $cardset, $type, $isw, $isu, $isb, $isr, $isg, $isp, $rarity);
    // while ($select->fetch()) {
    //   $insert->bind_param("sdsssdddddds", $cost, $cmc, $name, $cardset, $type, $isw, $isu, $isb, $isr, $isg, $isp, $rarity);
    //   if (!$insert->execute()) {
    //     die("Failed to copy {$name} to temporary table.");
    //   }
    // }
    // do_query("TRUNCATE `cards`;");
    do_query('ALTER TABLE `cards` ADD UNIQUE `unique_index`(`name`, `cardset`);');
    // do_query("SET FOREIGN_KEY_CHECKS=1;");

    // $select = $db->prepare("SELECT cost, convertedcost, name, cardset, type,
    // isw, isu, isb, isr, isg, isp, rarity FROM `cards_tmp`;");
    // $insert = $db->prepare("INSERT INTO cards(cost, convertedcost, name, cardset, type,
    //   isw, isu, isb, isr, isg, isp, rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    //   ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`),
    //     isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
    //     `rarity` = VALUES(`rarity`);");

    // $select->execute();
    // $select->bind_result($cost, $cmc, $name, $cardset, $type, $isw, $isu, $isb, $isr, $isg, $isp, $rarity);
    // while ($select->fetch()) {
    //   $insert->bind_param("sdsssdddddds", $cost, $cmc, $name, $cardset, $type, $isw, $isu, $isb, $isr, $isg, $isp, $rarity);
    //   if (!$insert->execute()) {
    //     die("Failed to copy {$name} to temporary table.");
    //   }
    // }
    do_query('UPDATE db_version SET version = 17');
    $db->commit();
    echo '... DB now at version 17! <br />';
}
if ($version < 18) {
    echo 'Updating to version 18 (add prereg_cap to events)... <br />';
    do_query('ALTER TABLE events ADD COLUMN `prereg_cap` int(11) DEFAULT NULL;');
    do_query('UPDATE db_version SET version = 18');
    $db->commit();
    echo '... DB now at version 18! <br />';
}
if ($version < 19) {
    echo 'Updating to version 19 (Fix rounding error with standings)... <br />';
    do_query("ALTER TABLE `standings`
  CHANGE COLUMN `OP_Match` `OP_Match` DECIMAL(4,3) NULL DEFAULT '0.000',
  CHANGE COLUMN `PL_Game` `PL_Game` DECIMAL(4,3) NULL DEFAULT '0.000',
  CHANGE COLUMN `OP_Game` `OP_Game` DECIMAL(4,3) NULL DEFAULT '0.000';");
    do_query("ALTER TABLE `events`
  CHANGE COLUMN `prereg_cap` `prereg_cap` INT(11) NULL DEFAULT '0';");
    do_query('UPDATE db_version SET version = 19');
    $db->commit();
    echo '... DB now at version 19! <br />';
}
if ($version < 20) {
    // This one is messy, because we need to skip it if we're applying to the production DB on gatherling.com 😕
    if (!$db->query('select 1 from `restrictedtotribe` LIMIT 1')) {
        echo 'Updating to version 20 (Tribal Wars, Player profiles, and Banned Players)... <br />';
        do_query('ALTER TABLE `decks`
      ADD COLUMN `playername` varchar(40) NOT NULL,
      ADD COLUMN `deck_colors` varchar(6) DEFAULT NULL,
      ADD COLUMN `format` varchar(40) DEFAULT NULL,
      ADD COLUMN `tribe` varchar(40) DEFAULT NULL,
      ADD COLUMN `created_date` datetime DEFAULT NULL;');
        do_query("ALTER TABLE `events`
      ADD COLUMN `player_reported_draws` tinyint(1) NOT NULL,
      ADD COLUMN `private_decks` tinyint(3) unsigned NOT NULL DEFAULT '1';");
        do_query("ALTER TABLE `formats`
      ADD COLUMN `tribal` tinyint(3) NOT NULL DEFAULT '0',
      ADD COLUMN `pure` tinyint(3) NOT NULL DEFAULT '0',
      ADD COLUMN `underdog` tinyint(3) NOT NULL DEFAULT '0';");
        do_query("CREATE TABLE IF NOT EXISTS `playerbans` (
      `series` varchar(40) NOT NULL DEFAULT 'All',
      `player` varchar(40) NOT NULL,
      `date` date NOT NULL,
      `reason` text NOT NULL,
      KEY `PBIndex` (`series`,`player`)
    );");
        do_query("CREATE TABLE IF NOT EXISTS `restrictedtotribe` (
      `card_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
      `card` bigint(20) unsigned NOT NULL,
      `format` varchar(40) NOT NULL,
      `allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`card`,`format`),
      KEY `format` (`format`)
    );");
        do_query('CREATE TABLE IF NOT EXISTS `subtype_bans` (
      `name` varchar(40) NOT NULL,
      `format` varchar(40) NOT NULL,
      `allowed` smallint(5) unsigned NOT NULL
    );');
        do_query('CREATE TABLE IF NOT EXISTS `tribes` (
      `name` varchar(40) NOT NULL,
      KEY `Tribe` (`name`)
    );');
        do_query('CREATE TABLE IF NOT EXISTS `tribe_bans` (
      `name` varchar(40) NOT NULL,
      `format` varchar(40)  NOT NULL,
      `allowed` smallint(5) unsigned NOT NULL,
      KEY `TribeBansIndex` (`format`,`name`)
    );');
        do_query("ALTER TABLE `players`
      ADD COLUMN `email` varchar(40) DEFAULT NULL,
      ADD COLUMN `email_privacy` tinyint(3) NOT NULL,
      ADD COLUMN `timezone` decimal(10,0) NOT NULL DEFAULT '-5';");
    }
    do_query('UPDATE db_version SET version = 20');
    $db->commit();
    echo '... DB now at version 20! <br />';
}
if ($version < 21) {
    echo 'Updating to version 21 (add `series`.`mtgo_room` and a default value to `events`.`current_round`)... <br />';
    do_query("ALTER TABLE `events` CHANGE COLUMN `current_round` `current_round` INT(11) NULL DEFAULT '0';");
    do_query('ALTER TABLE `series` ADD COLUMN `mtgo_room` VARCHAR(10) NULL DEFAULT NULL AFTER `pkonly_default`;');
    do_query('UPDATE db_version SET version = 21');
    $db->commit();
    echo '... DB now at version 21! <br />';
}
if ($version < 22) {
    echo 'Updating to version 22 (Ensure that the ratings table is in the expected order)... <br />';
    do_query('ALTER TABLE `ratings` CHANGE COLUMN `event` `event` VARCHAR(80) NULL DEFAULT NULL FIRST;');
    do_query('UPDATE db_version SET version = 22');
    $db->commit();
    echo '... DB now at version 22! <br />';
}
if ($version < 23) {
    echo 'Updating to version 23 (Add default value to player.email_privacy)... <br />';
    do_query("ALTER TABLE `players` CHANGE COLUMN `email_privacy` `email_privacy` TINYINT(3) NOT NULL DEFAULT '0';");
    do_query('UPDATE db_version SET version = 23');
    $db->commit();
    echo '... DB now at version 23! <br />';
}
if ($version < 24) {
    echo 'Updating to version 24 (Eternal Format flag)... <br />';
    do_query("ALTER TABLE `formats` ADD COLUMN `eternal` TINYINT(3) NOT NULL DEFAULT '0' AFTER `underdog`;");
    do_query('UPDATE db_version SET version = 24');
    $db->commit();
    echo '... DB now at version 24! <br />';
}
if ($version < 25) {
    echo 'Updating to version 25 (Masterpiece sets have a longer code)... <br />';
    do_query('ALTER TABLE `cardsets` CHANGE COLUMN `code` `code` VARCHAR(7) NULL DEFAULT NULL ;');
    do_query("ALTER TABLE `events` ADD COLUMN `late_entry_limit` smallint(5) UNSIGNED NOT NULL DEFAULT '0';");
    do_query('UPDATE db_version SET version = 25');
    $db->commit();
    echo '... DB now at version 25! <br />';
}
if ($version < 26) {
    echo 'Updating to version 26 (Standard and Modern flags)... <br />';
    do_query("ALTER TABLE `cardsets`
    ADD COLUMN `standard_legal` tinyint(4) DEFAULT '0',
    ADD COLUMN `modern_legal` tinyint(4) DEFAULT '0';");
    do_query("ALTER TABLE `formats`
    ADD COLUMN `standard` TINYINT NOT NULL DEFAULT '0' AFTER `eternal`,
    ADD COLUMN `modern` TINYINT NOT NULL DEFAULT '0' AFTER `standard`;");
    do_query('ALTER TABLE `players`
    ADD COLUMN `theme` VARCHAR(45) NULL;');
    set_version(26);
}
if ($version < 27) {
    info('Updating to version 27 (Separate setting for hiding decklists during the finals)...');
    do_query("ALTER TABLE `events`
    ADD COLUMN `private_finals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `private_decks`;");
    set_version(27);
}
if ($version < 28) {
    info('Updating to version 28 (Metaformats)...');
    do_query("ALTER TABLE `formats`
    ADD COLUMN `is_meta_format` TINYINT NOT NULL DEFAULT '0';");
    do_query('CREATE TABLE `subformats` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `parentformat` VARCHAR(40) NOT NULL,
        `childformat` VARCHAR(40) NOT NULL,
        PRIMARY KEY (`id`),
        INDEX `_idx` (`parentformat` ASC, `childformat` ASC)
        );');
    do_query('ALTER TABLE `subformats` ADD FOREIGN KEY (`parentformat`) REFERENCES `formats`(`name`) ON DELETE CASCADE ON UPDATE CASCADE;');
    do_query('ALTER TABLE `subformats` ADD FOREIGN KEY (`childformat`) REFERENCES `formats`(`name`) ON DELETE CASCADE ON UPDATE CASCADE;');
    set_version(28);
}
$db->autocommit(true);

info('DB is up to date!');
