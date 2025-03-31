/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `archetypes`
--

DROP TABLE IF EXISTS `archetypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `archetypes` (
  `name` varchar(40) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bans` (
  `card_name` varchar(160) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  UNIQUE KEY `unique_card_format` (`card_name`,`format`),
  KEY `format` (`format`),
  CONSTRAINT `bans_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bans_ibfk_2` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bans_ibfk_3` FOREIGN KEY (`format`) REFERENCES `formats` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cost` varchar(40) DEFAULT NULL,
  `convertedcost` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `isw` tinyint(1) NOT NULL DEFAULT 0,
  `isr` tinyint(1) NOT NULL DEFAULT 0,
  `isg` tinyint(1) NOT NULL DEFAULT 0,
  `isu` tinyint(1) NOT NULL DEFAULT 0,
  `isb` tinyint(1) NOT NULL DEFAULT 0,
  `isp` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(160) NOT NULL,
  `cardset` varchar(60) NOT NULL,
  `type` varchar(80) NOT NULL,
  `rarity` varchar(40) NOT NULL,
  `scryfallId` varchar(36) DEFAULT NULL,
  `is_changeling` tinyint(1) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`cardset`),
  UNIQUE KEY `unique_index` (`name`,`cardset`),
  KEY `cardset` (`cardset`),
  CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=187032 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cardsets`
--

DROP TABLE IF EXISTS `cardsets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cardsets` (
  `released` date NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` enum('Core','Block','Extra') NOT NULL,
  `code` varchar(7) DEFAULT NULL,
  `standard_legal` tinyint(1) NOT NULL,
  `modern_legal` tinyint(1) NOT NULL,
  `last_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `client` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `db_version` (
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deckcontents`
--

DROP TABLE IF EXISTS `deckcontents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `deckcontents` (
  `card` bigint(20) unsigned NOT NULL,
  `deck` bigint(20) unsigned NOT NULL,
  `qty` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `issideboard` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`deck`,`issideboard`),
  KEY `deck` (`deck`),
  CONSTRAINT `deckcontents_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deckcontents_ibfk_2` FOREIGN KEY (`deck`) REFERENCES `decks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deckerrors`
--

DROP TABLE IF EXISTS `deckerrors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `deckerrors` (
  `deck` bigint(20) unsigned NOT NULL,
  `error` text DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `decks`
--

DROP TABLE IF EXISTS `decks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `decks` (
  `archetype` varchar(40) NOT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `playername` varchar(40) NOT NULL,
  `deck_colors` varchar(6) DEFAULT NULL,
  `format` varchar(40) NOT NULL,
  `tribe` varchar(40) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `deck_hash` varchar(40) DEFAULT NULL,
  `sideboard_hash` varchar(40) DEFAULT NULL,
  `whole_hash` varchar(40) DEFAULT NULL,
  `deck_contents_cache` mediumtext DEFAULT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `archetype` (`archetype`),
  KEY `FK_decks_players` (`playername`),
  KEY `FK_decks_formats` (`format`),
  CONSTRAINT `FK_decks_formats` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`playername`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `decks_ibfk_2` FOREIGN KEY (`archetype`) REFERENCES `archetypes` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=136539 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entries`
--

DROP TABLE IF EXISTS `entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entries` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `player` varchar(40) NOT NULL,
  `medal` enum('1st','2nd','t4','t8','dot') NOT NULL DEFAULT 'dot',
  `deck` bigint(20) unsigned DEFAULT NULL,
  `drop_round` smallint(5) unsigned NOT NULL DEFAULT 0,
  `registered_at` datetime NOT NULL,
  `initial_byes` tinyint(4) NOT NULL DEFAULT 0,
  `initial_seed` int(11) NOT NULL DEFAULT 127,
  PRIMARY KEY (`event_id`,`player`) USING BTREE,
  KEY `player` (`player`),
  KEY `deck` (`deck`),
  CONSTRAINT `entries_ibfk_2` FOREIGN KEY (`player`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entries_ibfk_3` FOREIGN KEY (`deck`) REFERENCES `decks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entries_ibfk_4` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER prevent_null_deck
BEFORE UPDATE ON entries
FOR EACH ROW
BEGIN
    IF OLD.deck IS NOT NULL AND NEW.deck IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: Attempt to set entries.deck to NULL';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `format` varchar(40) NOT NULL,
  `host` varchar(40) NOT NULL,
  `kvalue` tinyint(3) unsigned NOT NULL DEFAULT 16,
  `metaurl` varchar(240) NOT NULL,
  `name` varchar(80) NOT NULL DEFAULT '',
  `number` tinyint(3) unsigned NOT NULL,
  `season` int(11) NOT NULL,
  `series` varchar(40) NOT NULL,
  `threadurl` varchar(240) NOT NULL,
  `reporturl` varchar(240) NOT NULL,
  `finalized` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `prereg_allowed` int(11) NOT NULL DEFAULT 0,
  `prereg_cap` int(11) NOT NULL DEFAULT 0,
  `cohost` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `current_round` tinyint(3) NOT NULL DEFAULT 0,
  `player_reportable` smallint(6) NOT NULL DEFAULT 1,
  `player_reported_draws` tinyint(1) NOT NULL,
  `private_decks` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `private_finals` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `player_editdecks` tinyint(1) NOT NULL DEFAULT 1,
  `late_entry_limit` smallint(5) unsigned NOT NULL DEFAULT 0,
  `private` tinyint(1) DEFAULT 0,
  `client` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`),
  KEY `format` (`format`),
  KEY `host` (`host`),
  KEY `series` (`series`),
  KEY `cohost` (`cohost`),
  KEY `client` (`client`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`host`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `events_ibfk_3` FOREIGN KEY (`series`) REFERENCES `series` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `events_ibfk_4` FOREIGN KEY (`cohost`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `events_ibfk_5` FOREIGN KEY (`client`) REFERENCES `client` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formats`
--

DROP TABLE IF EXISTS `formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formats` (
  `name` varchar(40) NOT NULL,
  `description` mediumtext NOT NULL,
  `type` varchar(40) NOT NULL,
  `series_name` varchar(40) NOT NULL,
  `singleton` tinyint(3) NOT NULL DEFAULT 0,
  `commander` tinyint(3) NOT NULL DEFAULT 0,
  `planechase` tinyint(3) NOT NULL DEFAULT 0,
  `vanguard` tinyint(3) NOT NULL DEFAULT 0,
  `prismatic` tinyint(3) NOT NULL DEFAULT 0,
  `tribal` tinyint(3) NOT NULL DEFAULT 0,
  `pure` tinyint(3) NOT NULL DEFAULT 0,
  `underdog` tinyint(3) NOT NULL DEFAULT 0,
  `limitless` tinyint(3) NOT NULL,
  `eternal` tinyint(3) NOT NULL DEFAULT 0,
  `standard` tinyint(4) NOT NULL DEFAULT 0,
  `modern` tinyint(4) NOT NULL DEFAULT 0,
  `allow_commons` tinyint(3) NOT NULL DEFAULT 0,
  `allow_uncommons` tinyint(3) NOT NULL DEFAULT 0,
  `allow_rares` tinyint(3) NOT NULL DEFAULT 0,
  `allow_mythics` tinyint(3) NOT NULL DEFAULT 0,
  `allow_timeshifted` tinyint(3) NOT NULL DEFAULT 0,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `min_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `max_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `min_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `max_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `playera` varchar(40) NOT NULL,
  `playerb` varchar(40) NOT NULL,
  `round` tinyint(3) unsigned NOT NULL,
  `subevent` bigint(20) unsigned NOT NULL,
  `result` enum('A','B','D','BYE','P') NOT NULL DEFAULT 'P',
  `playera_wins` int(11) NOT NULL DEFAULT 0,
  `playera_losses` int(11) NOT NULL DEFAULT 0,
  `playera_draws` int(11) NOT NULL DEFAULT 0,
  `playerb_wins` int(11) NOT NULL DEFAULT 0,
  `playerb_losses` int(11) NOT NULL DEFAULT 0,
  `playerb_draws` int(11) NOT NULL DEFAULT 0,
  `verification` enum('unverified','verified','failed') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `playera` (`playera`),
  KEY `playerb` (`playerb`),
  KEY `subevent` (`subevent`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`playera`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`playerb`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`subevent`) REFERENCES `subevents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=270724 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playerbans`
--

DROP TABLE IF EXISTS `playerbans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playerbans` (
  `series` varchar(40) NOT NULL DEFAULT 'All',
  `player` varchar(40) NOT NULL,
  `date` date NOT NULL,
  `reason` mediumtext NOT NULL,
  KEY `PBIndex` (`series`,`player`),
  KEY `player` (`player`),
  CONSTRAINT `playerbans_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`),
  CONSTRAINT `playerbans_ibfk_2` FOREIGN KEY (`player`) REFERENCES `players` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `email_privacy` tinyint(3) NOT NULL DEFAULT 0,
  `pkmember` tinyint(4) NOT NULL DEFAULT 0,
  `host` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `password` varchar(80) DEFAULT NULL,
  `timezone` decimal(10,0) NOT NULL DEFAULT -5,
  `super` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mtgo_confirmed` tinyint(1) DEFAULT NULL,
  `mtgo_challenge` varchar(5) DEFAULT NULL,
  `discord_id` varchar(20) DEFAULT NULL,
  `discord_handle` varchar(37) DEFAULT NULL,
  `mtga_username` varchar(32) DEFAULT NULL,
  `mtgo_username` varchar(40) DEFAULT NULL,
  `api_key` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `discord_id` (`discord_id`),
  UNIQUE KEY `mtga_username` (`mtga_username`),
  UNIQUE KEY `mtgo_username` (`mtgo_username`)
) ENGINE=InnoDB AUTO_INCREMENT=39503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratings` (
  `event` varchar(80) NOT NULL,
  `player` varchar(40) NOT NULL,
  `rating` smallint(5) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `updated` datetime NOT NULL,
  `wins` bigint(20) unsigned NOT NULL,
  `losses` bigint(20) unsigned NOT NULL,
  KEY `player` (`player`),
  KEY `ratings_ibfk_1` (`event`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`player`) REFERENCES `players` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restricted`
--

DROP TABLE IF EXISTS `restricted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `restricted` (
  `card_name` varchar(40) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  UNIQUE KEY `unique_card_format` (`card_name`,`format`),
  KEY `format` (`format`),
  CONSTRAINT `restricted_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restrictedtotribe`
--

DROP TABLE IF EXISTS `restrictedtotribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `restrictedtotribe` (
  `card_name` varchar(40) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  UNIQUE KEY `unique_card_format` (`card_name`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `season_points`
--

DROP TABLE IF EXISTS `season_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_points` (
  `series` varchar(40) NOT NULL,
  `season` int(11) NOT NULL,
  `event` varchar(80) NOT NULL,
  `player` varchar(40) NOT NULL,
  `adjustment` int(11) NOT NULL,
  `reason` varchar(140) NOT NULL,
  KEY `series` (`series`),
  KEY `event` (`event`),
  KEY `player` (`player`),
  CONSTRAINT `fk_players_name` FOREIGN KEY (`player`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `season_points_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `series` (
  `name` varchar(40) NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `logo` mediumblob DEFAULT NULL,
  `imgtype` varchar(40) DEFAULT NULL,
  `imgsize` bigint(20) unsigned DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `normalstart` time NOT NULL,
  `prereg_default` tinyint(1) NOT NULL,
  `mtgo_room` varchar(20) DEFAULT NULL,
  `discord_guild_id` varchar(20) DEFAULT NULL,
  `discord_channel_id` varchar(20) DEFAULT NULL,
  `discord_channel_name` varchar(50) DEFAULT NULL,
  `discord_guild_name` varchar(50) DEFAULT NULL,
  `discord_guild_invite` varchar(50) DEFAULT NULL,
  `discord_require_membership` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series_organizers`
--

DROP TABLE IF EXISTS `series_organizers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `series_organizers` (
  `player` varchar(40) NOT NULL,
  `series` varchar(40) NOT NULL,
  KEY `player` (`player`),
  KEY `series` (`series`),
  CONSTRAINT `series_organizers_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`),
  CONSTRAINT `series_organizers_ibfk_2` FOREIGN KEY (`series`) REFERENCES `series` (`name`),
  CONSTRAINT `series_organizers_ibfk_3` FOREIGN KEY (`player`) REFERENCES `players` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series_seasons`
--

DROP TABLE IF EXISTS `series_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `series_seasons` (
  `series` varchar(40) NOT NULL DEFAULT '',
  `season` int(11) NOT NULL DEFAULT 0,
  `first_pts` int(11) NOT NULL,
  `second_pts` int(11) NOT NULL,
  `semi_pts` int(11) NOT NULL,
  `quarter_pts` int(11) NOT NULL,
  `participation_pts` int(11) NOT NULL,
  `rounds_pts` int(11) NOT NULL,
  `decklist_pts` int(11) NOT NULL,
  `win_pts` int(11) NOT NULL,
  `loss_pts` int(11) NOT NULL,
  `bye_pts` int(11) NOT NULL,
  `must_decklist` int(11) DEFAULT NULL,
  `cutoff_ord` int(11) NOT NULL,
  `format` varchar(40) NOT NULL,
  `master_link` varchar(140) NOT NULL,
  PRIMARY KEY (`series`,`season`),
  CONSTRAINT `series_seasons_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `details` longtext NOT NULL,
  `expiry` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1467899 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setlegality`
--

DROP TABLE IF EXISTS `setlegality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `setlegality` (
  `format` varchar(40) NOT NULL,
  `cardset` varchar(40) NOT NULL,
  PRIMARY KEY (`format`,`cardset`),
  KEY `cardset` (`cardset`),
  CONSTRAINT `setlegality_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `setlegality_ibfk_2` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `setlegality_ibfk_3` FOREIGN KEY (`format`) REFERENCES `formats` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `standings`
--

DROP TABLE IF EXISTS `standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `standings` (
  `player` varchar(40) NOT NULL,
  `event` varchar(80) NOT NULL,
  `active` tinyint(3) NOT NULL DEFAULT 0,
  `matches_played` tinyint(3) NOT NULL DEFAULT 0,
  `games_won` tinyint(3) NOT NULL DEFAULT 0,
  `games_played` tinyint(3) NOT NULL DEFAULT 0,
  `byes` tinyint(3) NOT NULL DEFAULT 0,
  `OP_Match` decimal(4,3) NOT NULL DEFAULT 0.000,
  `PL_Game` decimal(4,3) NOT NULL DEFAULT 0.000,
  `OP_Game` decimal(4,3) NOT NULL DEFAULT 0.000,
  `score` tinyint(3) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seed` tinyint(3) NOT NULL,
  `matched` tinyint(1) NOT NULL,
  `matches_won` tinyint(3) NOT NULL DEFAULT 0,
  `draws` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  KEY `event` (`event`),
  CONSTRAINT `standings_ibfk_2` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `standings_ibfk_3` FOREIGN KEY (`player`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111615 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subevents`
--

DROP TABLE IF EXISTS `subevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subevents` (
  `parent` varchar(80) NOT NULL,
  `rounds` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `timing` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `type` enum('Swiss','Swiss (Blossom)','Single Elimination','League','League Match') NOT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_timing_parent` (`timing`,`parent`),
  KEY `parent` (`parent`),
  CONSTRAINT `subevents_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17864 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subtype_bans`
--

DROP TABLE IF EXISTS `subtype_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtype_bans` (
  `name` varchar(40) NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tribe_bans`
--

DROP TABLE IF EXISTS `tribe_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tribe_bans` (
  `name` varchar(40) NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` smallint(5) unsigned NOT NULL,
  KEY `TribeBansIndex` (`format`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tribes`
--

DROP TABLE IF EXISTS `tribes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tribes` (
  `name` varchar(40) NOT NULL,
  UNIQUE KEY `name_2` (`name`),
  KEY `Tribe` (`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trophies`
--

DROP TABLE IF EXISTS `trophies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trophies` (
  `event` varchar(80) NOT NULL DEFAULT '',
  `image` mediumblob NOT NULL,
  `type` varchar(40) NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`event`),
  CONSTRAINT `trophies_ibfk_1` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-03-25 12:30:47
/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Dumping data for table `archetypes`
--

LOCK TABLES `archetypes` WRITE;
/*!40000 ALTER TABLE `archetypes` DISABLE KEYS */;
INSERT INTO `archetypes` VALUES
('Aggro','Aggro (short for \"aggressive\") decks attempt to reduce their opponents from 20 life to 0 life as quickly as possible, rather than emphasize a long-term game plan. Aggro decks focus on converting their cards into damage; they prefer to engage in a tempo-based race rather than a card advantage-based attrition war. Aggro generally relies upon creatures as a cumulative source of damage. While strategically simple, aggro decks can quickly overwhelm unprepared opponents and proceed to eke out the last bit of damage they need to end the game. Aggro decks also generally have access to disruptive elements, which can inhibit the opponent\'s attempts to respond.',2),
('Aggro-Combo','Aggro-combo decks employ aggressive creature strategies along with some combination of cards that can win in \"combo\" fashion with one big turn. For instance, Ravager Affinity decks that include Disciple of the Vault can win by attacking with creatures and also with a combo finish of sacrificing multiple artifacts to Arcbound Ravager and killing the opponent with Disciple triggers.',1),
('Aggro-Control','Aggro-control is a hybrid archetype that contains both aggressive creatures and control elements. These decks attempt to deploy quick threats while protecting them with light permission and disruption long enough to win. These are frequently referred to as \"tempo\" strategies, as their control elements are often more temporary; for instance, they may return opposing creatures to their owners\' hands rather than remove them entirely.',1),
('Combo','Combo decks utilize the interaction of two or more cards (a \"combination\") to create a powerful effect that either wins the game immediately or creates a situation that subsequently leads to a win. The term \"combo\" can also describe a deck built around resolving a single powerful spell such as Tooth and Nail to create the same kind of insurmountable advantage. Combo decks value power, consistency, and speed: the combo should be strong enough to win, the deck should be reliable enough to produce the combo on a regular basis, and the deck should be able to use the combo fast enough to win before the opponent.',2),
('Combo-Control','Control-Combo is a control deck with a combo finisher that it can spring quickly if need be. A notable subtype of Control-Combo is \"prison,\" which institutes control through resource denial (usually via a combo).',1),
('Control','Control decks avoid racing and attempt to slow the game down by executing an attrition plan. These decks attempt to accumulate resource advantage, contain threats, and run opponents out of options. The primary strength of control decks is their ability to devalue the opponent’s cards. They do this in four ways:\r\n\r\n   1) Erasing threats at a reduced cost. Given the opportunity, Control decks can gain card advantage by answering multiple threats with one spell, stopping expensive threats with cheaper spells, and drawing multiple cards or forcing the opponent to discard multiple cards with one spell.\r\n    2) Not playing threats to be answered. By playing few proactive spells of their own, control decks gain virtual card advantage by reducing the usefulness of opposing removal cards.\r\n    3) Disrupting synergies. Even if control decks do not deal with every threat directly, they can leave out whichever ones stand poorly on their own; e.g., a creature enchantment which will never need attention if all enemy creatures are quickly removed.\r\n    4) Dragging the game out past opposing preparations. An opponent\'s faster, efficient cards will become less effective over time.\r\n\r\nOften control decks end the game with the very same threats midrange or ramp decks use. The difference is that they\'re not focused on getting those threats out as soon as they possibly can. Instead, they use them to mop up a game they\'ve already secured and stabilized. Alternatively, the large threat itself can be used as a tool to stabilize, either by virtue of its size or its ability to remove threats.\r\n',2),
('Midrange','Midrange tends to feature one-drops with abilities (e.g., Llanowar Elf) and early threats that are more defined by their resilience than their raw size, speed, and power. These decks tend to be a turn slower than the aggro decks—although still reasonably fast—and oftentimes use Planeswalkers to generate advantage on the battlefield. They will sometimes use a few reactive cards to deal with key threats, but tend to be at a disadvantage if they draw too many of this type of card and are unable to develop their board. Some midrange decks trend toward the aggressive end of the spectrum, and others toward control. What they hold in common is their focus on accumulating advantage on the battlefield itself, as opposed to gaining an advantage in raw resources (having a 4/4 versus a 2/1, as opposed to having two cards in hand versus a single card, for example).',1),
('Ramp','Ramp decks tend to spend their early turns developing their mana advantage instead of deploying threats to the board in an attempt to play larger more powerful mana-advantage spells (often spells that have an X in the casting cost). In order to be successful the card that provides the win condition needs to have a greater return than several smaller spells that can be played faster. Ramp decks rely upon one or two threats to do a lot of work for them.',1),
('Unclassified',NULL,0);
/*!40000 ALTER TABLE `archetypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES
(87);
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES
(1,'mtgo'),
(2,'arena'),
(3,'paper');
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-03-25 12:30:50
/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Dumping data for table `formats`
--
-- WHERE:  name IN ('Standard', 'Modern', 'Penny Dreadful')

LOCK TABLES `formats` WRITE;
/*!40000 ALTER TABLE `formats` DISABLE KEYS */;
INSERT INTO `formats` VALUES
('Modern','Regular DCI-sanctioned Modern format.','Private','Modern Times',0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,60,500,0,15),
('Penny Dreadful','','Private','Penny Dreadful Thursdays',0,0,0,0,0,0,0,0,0,1,0,0,1,1,1,1,1,1,60,300,0,15),
('Standard','The Standard format is continually one of the most popular formats in the constructed deck tournament scene. It is the format most commonly found at Friday Night Magic tournaments, played weekly at many hobby shops. Standard used to be referred to alternatively as \"Type 2\". While the name, \"Type 2\" has been dropped officially, it is still commonplace that the standard format be referred to this way. This format consists of the most recent \"Core Set\" release and the two most recent \"Block\" releases, with one exception. \"Rotation\" occurs every fall when the first set of the new \"Block\" releases and becomes Standard Legal. From the time the new \"Core Set\" is released in early summer, until rotation occurs, 2 core sets are legal. ','Private','Friday Night Standard',0,0,0,0,0,0,0,0,0,0,1,0,1,1,1,1,0,1,60,1500,0,15);
/*!40000 ALTER TABLE `formats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-03-25 12:30:53
