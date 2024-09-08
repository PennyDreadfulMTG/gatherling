/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.5.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.5.2-MariaDB

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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `card_name` varchar(160) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`),
  CONSTRAINT `bans_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bans_ibfk_2` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cost` varchar(40) DEFAULT NULL,
  `convertedcost` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `isw` tinyint(1) DEFAULT 0,
  `isr` tinyint(1) DEFAULT 0,
  `isg` tinyint(1) DEFAULT 0,
  `isu` tinyint(1) DEFAULT 0,
  `isb` tinyint(1) DEFAULT 0,
  `isp` tinyint(1) DEFAULT 0,
  `name` varchar(160) NOT NULL,
  `cardset` varchar(60) NOT NULL,
  `type` varchar(80) NOT NULL,
  `rarity` varchar(40) DEFAULT NULL,
  `scryfallId` varchar(36) DEFAULT NULL,
  `is_changeling` tinyint(1) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`cardset`),
  KEY `cardset` (`cardset`),
  CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=184919 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cardsets`
--

DROP TABLE IF EXISTS `cardsets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cardsets` (
  `released` date NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` enum('Core','Block','Extra') DEFAULT 'Block',
  `code` varchar(7) DEFAULT NULL,
  `standard_legal` tinyint(1) DEFAULT 0,
  `modern_legal` tinyint(1) DEFAULT 0,
  `last_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deckcontents`
--

DROP TABLE IF EXISTS `deckcontents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deckerrors` (
  `deck` bigint(20) unsigned NOT NULL,
  `error` varchar(250) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `decks`
--

DROP TABLE IF EXISTS `decks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `decks` (
  `archetype` varchar(40) DEFAULT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `playername` varchar(40) NOT NULL,
  `deck_colors` varchar(6) DEFAULT NULL,
  `format` varchar(40) DEFAULT NULL,
  `tribe` varchar(40) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `deck_hash` varchar(40) DEFAULT NULL,
  `sideboard_hash` varchar(40) DEFAULT NULL,
  `whole_hash` varchar(40) DEFAULT NULL,
  `deck_contents_cache` mediumtext DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archetype` (`archetype`),
  KEY `FK_decks_players` (`playername`),
  KEY `FK_decks_formats` (`format`),
  CONSTRAINT `FK_decks_formats` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`playername`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131827 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `decktypes`
--

DROP TABLE IF EXISTS `decktypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `decktypes` (
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entries`
--

DROP TABLE IF EXISTS `entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entries` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `player` varchar(40) NOT NULL,
  `medal` enum('1st','2nd','t4','t8','dot') NOT NULL DEFAULT 'dot',
  `deck` bigint(20) unsigned DEFAULT NULL,
  `ignored` tinyint(1) DEFAULT NULL,
  `drop_round` smallint(5) unsigned NOT NULL DEFAULT 0,
  `notes` mediumtext DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `initial_byes` tinyint(4) NOT NULL DEFAULT 0,
  `initial_seed` int(11) DEFAULT 127,
  PRIMARY KEY (`event_id`,`player`) USING BTREE,
  KEY `player` (`player`),
  KEY `deck` (`deck`),
  CONSTRAINT `entries_ibfk_2` FOREIGN KEY (`player`) REFERENCES `players` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entries_ibfk_3` FOREIGN KEY (`deck`) REFERENCES `decks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entries_ibfk_4` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `format` varchar(40) NOT NULL,
  `host` varchar(40) DEFAULT NULL,
  `kvalue` tinyint(3) unsigned NOT NULL DEFAULT 16,
  `metaurl` varchar(240) DEFAULT NULL,
  `name` varchar(80) NOT NULL DEFAULT '',
  `number` tinyint(3) unsigned DEFAULT NULL,
  `season` tinyint(3) unsigned DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  `threadurl` varchar(240) DEFAULT NULL,
  `reporturl` varchar(240) DEFAULT NULL,
  `finalized` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `prereg_allowed` int(11) DEFAULT 0,
  `prereg_cap` int(11) NOT NULL DEFAULT 0,
  `pkonly` tinyint(4) DEFAULT 0,
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
) ENGINE=InnoDB AUTO_INCREMENT=6870 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formats`
--

DROP TABLE IF EXISTS `formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formats` (
  `name` varchar(40) NOT NULL,
  `description` mediumtext DEFAULT NULL,
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
  `limitless` tinyint(3) DEFAULT NULL,
  `eternal` tinyint(3) NOT NULL DEFAULT 0,
  `standard` tinyint(4) NOT NULL DEFAULT 0,
  `modern` tinyint(4) NOT NULL DEFAULT 0,
  `allow_commons` tinyint(3) NOT NULL DEFAULT 0,
  `allow_uncommons` tinyint(3) NOT NULL DEFAULT 0,
  `allow_rares` tinyint(3) NOT NULL DEFAULT 0,
  `allow_mythics` tinyint(3) NOT NULL DEFAULT 0,
  `allow_timeshifted` tinyint(3) NOT NULL DEFAULT 0,
  `priority` tinyint(3) unsigned DEFAULT 1,
  `min_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `max_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `min_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `max_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT 0,
  `is_meta_format` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `playera` varchar(40) NOT NULL,
  `playerb` varchar(40) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=261139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playerbans`
--

DROP TABLE IF EXISTS `playerbans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playerbans` (
  `series` varchar(40) NOT NULL DEFAULT 'All',
  `player` varchar(40) NOT NULL,
  `date` date NOT NULL,
  `reason` mediumtext NOT NULL,
  KEY `PBIndex` (`series`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `email_privacy` tinyint(3) NOT NULL DEFAULT 0,
  `pkmember` tinyint(4) NOT NULL DEFAULT 0,
  `host` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `password` varchar(80) DEFAULT NULL,
  `rememberme` tinyint(4) NOT NULL DEFAULT 0,
  `ipaddress` int(10) unsigned DEFAULT NULL,
  `timezone` decimal(10,0) NOT NULL DEFAULT -5,
  `super` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mtgo_confirmed` tinyint(1) DEFAULT NULL,
  `mtgo_challenge` varchar(5) DEFAULT NULL,
  `theme` varchar(45) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=38018 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratings` (
  `event` varchar(80) NOT NULL,
  `player` varchar(40) NOT NULL,
  `rating` smallint(5) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `updated` datetime NOT NULL,
  `wins` bigint(20) unsigned NOT NULL,
  `losses` bigint(20) unsigned NOT NULL,
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restricted`
--

DROP TABLE IF EXISTS `restricted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restricted` (
  `card_name` varchar(40) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restrictedtotribe`
--

DROP TABLE IF EXISTS `restrictedtotribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restrictedtotribe` (
  `card_name` varchar(40) NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `season_points`
--

DROP TABLE IF EXISTS `season_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `season_points` (
  `series` varchar(40) DEFAULT NULL,
  `season` int(11) DEFAULT NULL,
  `event` varchar(80) DEFAULT NULL,
  `player` varchar(40) DEFAULT NULL,
  `adjustment` int(11) DEFAULT NULL,
  `reason` varchar(140) DEFAULT NULL,
  KEY `series` (`series`),
  KEY `event` (`event`),
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series` (
  `name` varchar(40) NOT NULL,
  `isactive` tinyint(1) DEFAULT 0,
  `logo` mediumblob DEFAULT NULL,
  `imgtype` varchar(40) DEFAULT NULL,
  `imgsize` bigint(20) unsigned DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `normalstart` time DEFAULT NULL,
  `prereg_default` int(11) DEFAULT 0,
  `pkonly_default` tinyint(4) NOT NULL DEFAULT 0,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_organizers` (
  `player` varchar(40) DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  KEY `player` (`player`),
  KEY `series` (`series`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series_seasons`
--

DROP TABLE IF EXISTS `series_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_seasons` (
  `series` varchar(40) NOT NULL DEFAULT '',
  `season` int(11) NOT NULL DEFAULT 0,
  `first_pts` int(11) DEFAULT NULL,
  `second_pts` int(11) DEFAULT NULL,
  `semi_pts` int(11) DEFAULT NULL,
  `quarter_pts` int(11) DEFAULT NULL,
  `participation_pts` int(11) DEFAULT NULL,
  `rounds_pts` int(11) DEFAULT NULL,
  `decklist_pts` int(11) DEFAULT NULL,
  `win_pts` int(11) DEFAULT NULL,
  `loss_pts` int(11) DEFAULT NULL,
  `bye_pts` int(11) DEFAULT NULL,
  `must_decklist` int(11) DEFAULT NULL,
  `cutoff_ord` int(11) DEFAULT NULL,
  `format` varchar(40) DEFAULT NULL,
  `master_link` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`series`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series_stewards`
--

DROP TABLE IF EXISTS `series_stewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_stewards` (
  `player` varchar(40) DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  KEY `player` (`player`),
  KEY `series` (`series`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setlegality`
--

DROP TABLE IF EXISTS `setlegality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setlegality` (
  `format` varchar(40) NOT NULL,
  `cardset` varchar(40) NOT NULL,
  PRIMARY KEY (`format`,`cardset`),
  KEY `cardset` (`cardset`),
  CONSTRAINT `setlegality_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `setlegality_ibfk_2` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `standings`
--

DROP TABLE IF EXISTS `standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `standings` (
  `player` varchar(40) DEFAULT NULL,
  `event` varchar(80) DEFAULT NULL,
  `active` tinyint(3) DEFAULT 0,
  `matches_played` tinyint(3) DEFAULT 0,
  `games_won` tinyint(3) DEFAULT 0,
  `games_played` tinyint(3) DEFAULT 0,
  `byes` tinyint(3) DEFAULT 0,
  `OP_Match` decimal(4,3) DEFAULT 0.000,
  `PL_Game` decimal(4,3) DEFAULT 0.000,
  `OP_Game` decimal(4,3) DEFAULT 0.000,
  `score` tinyint(3) DEFAULT 0,
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
) ENGINE=InnoDB AUTO_INCREMENT=107013 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stewards`
--

DROP TABLE IF EXISTS `stewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stewards` (
  `event` varchar(80) DEFAULT NULL,
  `player` varchar(40) NOT NULL,
  KEY `event` (`event`),
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subevents`
--

DROP TABLE IF EXISTS `subevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subevents` (
  `parent` varchar(80) DEFAULT NULL,
  `rounds` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `timing` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `type` enum('Swiss','Swiss (Blossom)','Single Elimination','League','Round Robin','League Match') NOT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  CONSTRAINT `subevents_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17378 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subformats`
--

DROP TABLE IF EXISTS `subformats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subformats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentformat` varchar(40) NOT NULL,
  `childformat` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_idx` (`parentformat`,`childformat`),
  KEY `childformat` (`childformat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subtype_bans`
--

DROP TABLE IF EXISTS `subtype_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trophies` (
  `event` varchar(80) NOT NULL DEFAULT '',
  `image` mediumblob DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`event`),
  CONSTRAINT `trophies_ibfk_1` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'gatherli_gatherling'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2024-08-30 15:04:02
/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.5.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.5.2-MariaDB

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
('Aggro','',2),
('Aggro-Combo','',1),
('Aggro-Control','',1),
('Combo','',2),
('Combo-Control','',1),
('Control','',2),
('Midrange','',1),
('Ramp','',1),
('Unclassified','',0);
/*!40000 ALTER TABLE `archetypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES
(53);
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

-- Dump completed on 2024-08-30 15:04:06
/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.5.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherli_gatherling
-- ------------------------------------------------------
-- Server version	11.5.2-MariaDB

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
('Modern','Regular DCI-sanctioned Modern format.','Private','Modern Times',0,0,0,0,0,0,0,0,NULL,0,0,1,1,1,1,1,1,1,60,500,0,15,0),
('Penny Dreadful','','Private','Penny Dreadful Thursdays',0,0,0,0,0,0,0,0,NULL,1,0,0,1,1,1,1,1,1,60,300,0,15,0),
('Standard','The Standard format is continually one of the most popular formats in the constructed deck tournament scene. It is the format most commonly found at Friday Night Magic tournaments, played weekly at many hobby shops. Standard used to be referred to alternatively as \"Type 2\". While the name, \"Type 2\" has been dropped officially, it is still commonplace that the standard format be referred to this way. This format consists of the most recent \"Core Set\" release and the two most recent \"Block\" releases, with one exception. \"Rotation\" occurs every fall when the first set of the new \"Block\" releases and becomes Standard Legal. From the time the new \"Core Set\" is released in early summer, until rotation occurs, 2 core sets are legal. ','Private','Friday Night Standard',0,0,0,0,0,0,0,0,NULL,0,1,0,1,1,1,1,0,1,60,1500,0,15,0);
/*!40000 ALTER TABLE `formats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2024-09-04 18:26:55
