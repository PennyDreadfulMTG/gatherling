-- phpMyAdmin SQL Dump
-- version 3.5.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 12, 2013 at 05:09 PM
-- Server version: 5.1.69-cll
-- PHP Version: 5.3.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gatherli_gatherling`
--

-- --------------------------------------------------------

--
-- Table structure for table `archetypes`
--

CREATE TABLE IF NOT EXISTS `archetypes` (
  `name` varchar(40) NOT NULL,
  `description` text,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `card_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE IF NOT EXISTS `cards` (
  `cost` varchar(40) DEFAULT NULL,
  `convertedcost` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `isw` tinyint(1) DEFAULT '0',
  `isr` tinyint(1) DEFAULT '0',
  `isg` tinyint(1) DEFAULT '0',
  `isu` tinyint(1) DEFAULT '0',
  `isb` tinyint(1) DEFAULT '0',
  `isp` tinyint(1) DEFAULT '0',
  `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cardset` varchar(40) NOT NULL,
  `type` varchar(40) NOT NULL,
  `rarity` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cardset` (`cardset`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cardsets`
--

CREATE TABLE IF NOT EXISTS `cardsets` (
  `released` date NOT NULL,
  `name` varchar(40) NOT NULL,
  `type` enum('Core','Block','Extra') DEFAULT 'Block',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `db_version`
--

CREATE TABLE IF NOT EXISTS `db_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deckcontents`
--

CREATE TABLE IF NOT EXISTS `deckcontents` (
  `card` bigint(20) unsigned NOT NULL,
  `deck` bigint(20) unsigned NOT NULL,
  `qty` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `issideboard` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`card`,`deck`,`issideboard`),
  KEY `deck` (`deck`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deckerrors`
--

CREATE TABLE IF NOT EXISTS `deckerrors` (
  `deck` bigint(20) unsigned NOT NULL,
  `error` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `decks`
--

CREATE TABLE IF NOT EXISTS `decks` (
  `archetype` varchar(40) DEFAULT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `playername` varchar(40) NOT NULL,
  `deck_colors` varchar(6) DEFAULT NULL,
  `format` varchar(40) DEFAULT NULL,
  `tribe` varchar(40) DEFAULT NULL,
  `notes` text,
  `deck_hash` varchar(40) DEFAULT NULL,
  `sideboard_hash` varchar(40) DEFAULT NULL,
  `whole_hash` varchar(40) DEFAULT NULL,
  `deck_contents_cache` text,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archetype` (`archetype`),
  KEY `FK_decks_players` (`playername`),
  KEY `FK_decks_formats` (`format`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `decktypes`
--

CREATE TABLE IF NOT EXISTS `decktypes` (
  `name` varchar(40) COLLATE latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE IF NOT EXISTS `entries` (
  `event` varchar(80) NOT NULL DEFAULT '',
  `player` varchar(40) NOT NULL,
  `medal` enum('1st','2nd','t4','t8','dot') NOT NULL DEFAULT 'dot',
  `deck` bigint(20) unsigned DEFAULT NULL,
  `ignored` tinyint(1) DEFAULT NULL,
  `drop_round` smallint(5) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `registered_at` datetime NOT NULL,
  PRIMARY KEY (`event`,`player`),
  KEY `player` (`player`),
  KEY `deck` (`deck`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `start` datetime NOT NULL,
  `format` varchar(40) NOT NULL,
  `host` varchar(40) DEFAULT NULL,
  `kvalue` tinyint(3) unsigned NOT NULL DEFAULT '16',
  `metaurl` varchar(240) DEFAULT NULL,
  `name` varchar(80) NOT NULL DEFAULT '',
  `number` tinyint(3) unsigned DEFAULT NULL,
  `season` tinyint(3) unsigned DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  `threadurl` varchar(240) DEFAULT NULL,
  `reporturl` varchar(240) DEFAULT NULL,
  `finalized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `prereg_allowed` int(11) DEFAULT '0',
  `prereg_cap` int(11) NOT NULL DEFAULT '0',
  `pkonly` tinyint(4) DEFAULT '0',
  `cohost` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `current_round` tinyint(3) NOT NULL,
  `player_reportable` smallint(6) NOT NULL DEFAULT '1',
  `player_reported_draws` tinyint(1) NOT NULL,
  `private_decks` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`name`),
  KEY `format` (`format`),
  KEY `host` (`host`),
  KEY `series` (`series`),
  KEY `cohost` (`cohost`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `formats`
--

CREATE TABLE IF NOT EXISTS `formats` (
  `name` varchar(40) NOT NULL,
  `description` text,
  `type` varchar(40) NOT NULL,
  `series_name` varchar(40) NOT NULL,
  `singleton` tinyint(3) NOT NULL DEFAULT '0',
  `commander` tinyint(3) NOT NULL DEFAULT '0',
  `planechase` tinyint(3) NOT NULL DEFAULT '0',
  `vanguard` tinyint(3) NOT NULL DEFAULT '0',
  `prismatic` tinyint(3) NOT NULL DEFAULT '0',
  `tribal` tinyint(3) NOT NULL DEFAULT '0',
  `pure` tinyint(3) NOT NULL DEFAULT '0',
  `underdog` tinyint(3) NOT NULL DEFAULT '0',
  `allow_commons` tinyint(3) NOT NULL DEFAULT '0',
  `allow_uncommons` tinyint(3) NOT NULL DEFAULT '0',
  `allow_rares` tinyint(3) NOT NULL DEFAULT '0',
  `allow_mythics` tinyint(3) NOT NULL DEFAULT '0',
  `allow_timeshifted` tinyint(3) NOT NULL DEFAULT '0',
  `priority` tinyint(3) unsigned DEFAULT '1',
  `min_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT '0',
  `max_main_cards_allowed` int(10) unsigned NOT NULL DEFAULT '0',
  `min_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT '0',
  `max_side_cards_allowed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `playera` varchar(40) NOT NULL,
  `playerb` varchar(40) DEFAULT NULL,
  `round` tinyint(3) unsigned NOT NULL,
  `subevent` bigint(20) unsigned NOT NULL,
  `result` enum('A','B','D','BYE','P') NOT NULL DEFAULT 'P',
  `playera_wins` int(11) NOT NULL DEFAULT '0',
  `playera_losses` int(11) NOT NULL DEFAULT '0',
  `playera_draws` int(11) NOT NULL DEFAULT '0',
  `playerb_wins` int(11) NOT NULL DEFAULT '0',
  `playerb_losses` int(11) NOT NULL DEFAULT '0',
  `playerb_draws` int(11) NOT NULL DEFAULT '0',
  `verification` enum('unverified','verified','failed') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `playera` (`playera`),
  KEY `playerb` (`playerb`),
  KEY `subevent` (`subevent`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `playerbans`
--

CREATE TABLE IF NOT EXISTS `playerbans` (
  `series` varchar(40) CHARACTER SET latin1 NOT NULL DEFAULT 'All',
  `player` varchar(40) CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL,
  `reason` text CHARACTER SET latin1 NOT NULL,
  KEY `PBIndex` (`series`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `name` varchar(40) CHARACTER SET latin1 NOT NULL,
  `email` varchar(40) CHARACTER SET latin1 DEFAULT NULL,
  `email_privacy` tinyint(3) NOT NULL,
  `pkmember` tinyint(4) NOT NULL DEFAULT '0',
  `host` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `password` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `rememberme` tinyint(4) NOT NULL DEFAULT '0',
  `ipaddress` int(10) unsigned DEFAULT NULL,
  `timezone` decimal(10,0) NOT NULL DEFAULT '-5',
  `super` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mtgo_confirmed` tinyint(1) DEFAULT NULL,
  `mtgo_challenge` varchar(5) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE IF NOT EXISTS `ratings` (
  `event` varchar(80) NOT NULL,
  `player` varchar(40) NOT NULL,
  `rating` smallint(5) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `updated` datetime NOT NULL,
  `wins` bigint(20) unsigned NOT NULL,
  `losses` bigint(20) unsigned NOT NULL,
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `restricted`
--

CREATE TABLE IF NOT EXISTS `restricted` (
  `card_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `restrictedtotribe`
--

CREATE TABLE IF NOT EXISTS `restrictedtotribe` (
  `card_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `card` bigint(20) unsigned NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`card`,`format`),
  KEY `format` (`format`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `season_points`
--

CREATE TABLE IF NOT EXISTS `season_points` (
  `series` varchar(40) DEFAULT NULL,
  `season` int(11) DEFAULT NULL,
  `event` varchar(40) DEFAULT NULL,
  `player` varchar(40) DEFAULT NULL,
  `adjustment` int(11) DEFAULT NULL,
  `reason` varchar(140) DEFAULT NULL,
  KEY `series` (`series`),
  KEY `event` (`event`),
  KEY `player` (`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series`
--

CREATE TABLE IF NOT EXISTS `series` (
  `name` varchar(40) NOT NULL,
  `isactive` tinyint(1) DEFAULT '0',
  `logo` mediumblob,
  `imgtype` varchar(40) DEFAULT NULL,
  `imgsize` bigint(20) unsigned DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `normalstart` time DEFAULT NULL,
  `prereg_default` int(11) DEFAULT '0',
  `pkonly_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series_organizers`
--

CREATE TABLE IF NOT EXISTS `series_organizers` (
  `player` varchar(40) DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  KEY `player` (`player`),
  KEY `series` (`series`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series_seasons`
--

CREATE TABLE IF NOT EXISTS `series_seasons` (
  `series` varchar(40) NOT NULL DEFAULT '',
  `season` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `setlegality`
--

CREATE TABLE IF NOT EXISTS `setlegality` (
  `format` varchar(40) NOT NULL,
  `cardset` varchar(40) NOT NULL,
  PRIMARY KEY (`format`,`cardset`),
  KEY `cardset` (`cardset`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `standings`
--

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
  `matches_won` tinyint(3) NOT NULL DEFAULT '0',
  `draws` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  KEY `event` (`event`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `subevents`
--

CREATE TABLE IF NOT EXISTS `subevents` (
  `parent` varchar(80) DEFAULT NULL,
  `rounds` tinyint(3) unsigned NOT NULL DEFAULT '3',
  `timing` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `type` enum('Swiss','Single Elimination','League','Round Robin') DEFAULT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `subtype_bans`
--

CREATE TABLE IF NOT EXISTS `subtype_bans` (
  `name` varchar(40) CHARACTER SET latin1 NOT NULL,
  `format` varchar(40) CHARACTER SET latin1 NOT NULL,
  `allowed` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tribes`
--

CREATE TABLE IF NOT EXISTS `tribes` (
  `name` varchar(40) NOT NULL,
  KEY `Tribe` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tribe_bans`
--

CREATE TABLE IF NOT EXISTS `tribe_bans` (
  `name` varchar(40) CHARACTER SET latin1 NOT NULL,
  `format` varchar(40) CHARACTER SET latin1 NOT NULL,
  `allowed` smallint(5) unsigned NOT NULL,
  KEY `TribeBansIndex` (`format`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `trophies`
--

CREATE TABLE IF NOT EXISTS `trophies` (
  `event` varchar(80) NOT NULL DEFAULT '',
  `image` mediumblob,
  `type` varchar(40) DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bans`
--
ALTER TABLE `bans`
  ADD CONSTRAINT `bans_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bans_ibfk_2` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `deckcontents`
--
ALTER TABLE `deckcontents`
  ADD CONSTRAINT `deckcontents_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`),
  ADD CONSTRAINT `deckcontents_ibfk_2` FOREIGN KEY (`deck`) REFERENCES `decks` (`id`);

--
-- Constraints for table `decks`
--
ALTER TABLE `decks`
  ADD CONSTRAINT `FK_decks_formats` FOREIGN KEY (`format`) REFERENCES `formats` (`name`),
  ADD CONSTRAINT `FK_decks_players` FOREIGN KEY (`playername`) REFERENCES `players` (`name`);

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_2` FOREIGN KEY (`player`) REFERENCES `players` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `entries_ibfk_3` FOREIGN KEY (`deck`) REFERENCES `decks` (`id`),
  ADD CONSTRAINT `entries_ibfk_4` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`host`) REFERENCES `players` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`series`) REFERENCES `series` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_4` FOREIGN KEY (`cohost`) REFERENCES `players` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`playera`) REFERENCES `players` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`playerb`) REFERENCES `players` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`subevent`) REFERENCES `subevents` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `setlegality`
--
ALTER TABLE `setlegality`
  ADD CONSTRAINT `setlegality_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `setlegality_ibfk_2` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `standings`
--
ALTER TABLE `standings`
  ADD CONSTRAINT `standings_ibfk_1` FOREIGN KEY (`player`) REFERENCES `players` (`name`),
  ADD CONSTRAINT `standings_ibfk_2` FOREIGN KEY (`event`) REFERENCES `events` (`name`);

--
-- Constraints for table `subevents`
--
ALTER TABLE `subevents`
  ADD CONSTRAINT `subevents_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `events` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `trophies`
--
ALTER TABLE `trophies`
  ADD CONSTRAINT `trophies_ibfk_1` FOREIGN KEY (`event`) REFERENCES `events` (`name`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
