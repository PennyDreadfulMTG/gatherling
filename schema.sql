-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2017 at 04:41 PM
-- Server version: 5.7.18-log
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gatherling`
--

-- --------------------------------------------------------

--
-- Table structure for table `archetypes`
--

CREATE TABLE `archetypes` (
  `name` varchar(40) NOT NULL,
  `description` text,
  `priority` tinyint(3) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `archetypes`
--

INSERT INTO `archetypes` (`name`, `description`, `priority`) VALUES
('Aggro', NULL, 2),
('Aggro-Combo', NULL, 1),
('Aggro-Control', NULL, 1),
('Combo', NULL, 2),
('Combo-Control', NULL, 1),
('Control', NULL, 2),
('Unclassified', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE `bans` (
  `card` bigint(20) UNSIGNED NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `card_name` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `cost` varchar(40) DEFAULT NULL,
  `convertedcost` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `id` bigint(20) UNSIGNED NOT NULL,
  `isw` tinyint(1) DEFAULT '0',
  `isr` tinyint(1) DEFAULT '0',
  `isg` tinyint(1) DEFAULT '0',
  `isu` tinyint(1) DEFAULT '0',
  `isb` tinyint(1) DEFAULT '0',
  `name` varchar(40) NOT NULL,
  `cardset` varchar(40) NOT NULL,
  `type` varchar(40) NOT NULL,
  `isp` tinyint(1) DEFAULT '0',
  `rarity` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cardsets`
--

CREATE TABLE `cardsets` (
  `released` date NOT NULL,
  `name` varchar(40) NOT NULL,
  `type` enum('Core','Block','Extra') DEFAULT 'Block',
  `code` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `db_version`
--

CREATE TABLE `db_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `db_version`
--

INSERT INTO `db_version` (`version`) VALUES
(23);

-- --------------------------------------------------------

--
-- Table structure for table `deckcontents`
--

CREATE TABLE `deckcontents` (
  `card` bigint(20) UNSIGNED NOT NULL,
  `deck` bigint(20) UNSIGNED NOT NULL,
  `qty` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `issideboard` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deckerrors`
--

CREATE TABLE `deckerrors` (
  `id` int(11) NOT NULL,
  `deck` bigint(20) NOT NULL,
  `error` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `decks`
--

CREATE TABLE `decks` (
  `archetype` varchar(40) DEFAULT NULL,
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `notes` text,
  `deck_hash` varchar(40) DEFAULT NULL,
  `sideboard_hash` varchar(40) DEFAULT NULL,
  `whole_hash` varchar(40) DEFAULT NULL,
  `deck_contents_cache` text,
  `playername` varchar(40) NOT NULL,
  `deck_colors` varchar(6) DEFAULT NULL,
  `format` varchar(40) DEFAULT NULL,
  `tribe` varchar(40) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `decktypes`
--

CREATE TABLE `decktypes` (
  `name` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE `entries` (
  `event` varchar(80) NOT NULL,
  `player` varchar(40) NOT NULL,
  `medal` enum('1st','2nd','t4','t8','dot') NOT NULL DEFAULT 'dot',
  `deck` bigint(20) UNSIGNED DEFAULT NULL,
  `ignored` tinyint(1) DEFAULT NULL,
  `notes` text,
  `registered_at` datetime DEFAULT NULL,
  `drop_round` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `start` datetime NOT NULL,
  `format` varchar(40) NOT NULL,
  `host` varchar(40) DEFAULT NULL,
  `kvalue` tinyint(3) UNSIGNED NOT NULL DEFAULT '16',
  `metaurl` varchar(240) DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `number` tinyint(3) UNSIGNED DEFAULT NULL,
  `season` tinyint(3) UNSIGNED DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL,
  `threadurl` varchar(240) DEFAULT NULL,
  `reporturl` varchar(240) DEFAULT NULL,
  `finalized` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `cohost` varchar(40) DEFAULT NULL,
  `prereg_allowed` int(11) DEFAULT '0',
  `pkonly` tinyint(3) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `current_round` int(11) DEFAULT '0',
  `player_reportable` tinyint(1) NOT NULL DEFAULT '0',
  `player_editdecks` tinyint(1) NOT NULL DEFAULT '1',
  `prereg_cap` int(11) DEFAULT '0',
  `player_reported_draws` tinyint(1) NOT NULL,
  `private_decks` tinyint(3) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `formats`
--

CREATE TABLE `formats` (
  `name` varchar(40) NOT NULL,
  `description` text,
  `priority` tinyint(3) UNSIGNED DEFAULT '1',
  `type` varchar(45) DEFAULT NULL,
  `series_name` varchar(40) DEFAULT NULL,
  `singleton` tinyint(3) DEFAULT NULL,
  `commander` tinyint(3) DEFAULT NULL,
  `planechase` tinyint(3) DEFAULT NULL,
  `vanguard` tinyint(3) DEFAULT NULL,
  `prismatic` tinyint(3) DEFAULT NULL,
  `allow_commons` tinyint(3) DEFAULT NULL,
  `allow_uncommons` tinyint(3) DEFAULT NULL,
  `allow_rares` tinyint(3) DEFAULT NULL,
  `allow_mythics` tinyint(3) DEFAULT NULL,
  `allow_timeshifted` tinyint(3) DEFAULT NULL,
  `min_main_cards_allowed` int(11) DEFAULT NULL,
  `max_main_cards_allowed` int(11) DEFAULT NULL,
  `min_side_cards_allowed` int(11) DEFAULT NULL,
  `max_side_cards_allowed` int(11) DEFAULT NULL,
  `tribal` tinyint(3) NOT NULL DEFAULT '0',
  `pure` tinyint(3) NOT NULL DEFAULT '0',
  `underdog` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `playera` varchar(40) NOT NULL,
  `playerb` varchar(40) NOT NULL,
  `round` tinyint(3) UNSIGNED NOT NULL,
  `subevent` bigint(20) UNSIGNED NOT NULL,
  `result` varchar(5) NOT NULL,
  `playera_wins` int(11) NOT NULL DEFAULT '0',
  `playera_losses` int(11) NOT NULL DEFAULT '0',
  `playera_draws` int(11) NOT NULL DEFAULT '0',
  `playerb_wins` int(11) NOT NULL DEFAULT '0',
  `playerb_losses` int(11) NOT NULL DEFAULT '0',
  `playerb_draws` int(11) NOT NULL DEFAULT '0',
  `verification` varchar(40) NOT NULL DEFAULT 'unverified'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `playerbans`
--

CREATE TABLE `playerbans` (
  `series` varchar(40) NOT NULL DEFAULT 'All',
  `player` varchar(40) NOT NULL,
  `date` date NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `name` varchar(40) NOT NULL,
  `host` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `password` varchar(80) DEFAULT NULL,
  `super` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `mtgo_confirmed` tinyint(1) DEFAULT NULL,
  `mtgo_challenge` varchar(5) DEFAULT NULL,
  `rememberme` int(11) DEFAULT NULL,
  `ipaddress` int(11) DEFAULT NULL,
  `pkmember` int(11) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `email_privacy` tinyint(3) NOT NULL DEFAULT '0',
  `timezone` decimal(10,0) NOT NULL DEFAULT '-5'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `event` varchar(80) DEFAULT NULL,
  `player` varchar(40) NOT NULL,
  `rating` smallint(5) UNSIGNED NOT NULL,
  `format` varchar(40) NOT NULL,
  `updated` datetime NOT NULL,
  `wins` bigint(20) UNSIGNED NOT NULL,
  `losses` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `restricted`
--

CREATE TABLE `restricted` (
  `card` bigint(20) UNSIGNED NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `card_name` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `restrictedtotribe`
--

CREATE TABLE `restrictedtotribe` (
  `card_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `card` bigint(20) UNSIGNED NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `season_points`
--

CREATE TABLE `season_points` (
  `series` varchar(40) DEFAULT NULL,
  `season` int(11) DEFAULT NULL,
  `event` varchar(40) DEFAULT NULL,
  `player` varchar(40) DEFAULT NULL,
  `adjustment` int(11) DEFAULT NULL,
  `reason` varchar(140) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series`
--

CREATE TABLE `series` (
  `name` varchar(40) NOT NULL,
  `isactive` tinyint(1) DEFAULT '0',
  `logo` mediumblob,
  `imgtype` varchar(40) DEFAULT NULL,
  `imgsize` bigint(20) UNSIGNED DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `normalstart` time DEFAULT NULL,
  `prereg_default` int(11) DEFAULT '0',
  `pkonly_default` tinyint(1) DEFAULT NULL,
  `mtgo_room` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series_organizers`
--

CREATE TABLE `series_organizers` (
  `player` varchar(40) DEFAULT NULL,
  `series` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `series_seasons`
--

CREATE TABLE `series_seasons` (
  `series` varchar(40) NOT NULL,
  `season` int(11) NOT NULL,
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
  `master_link` varchar(140) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `setlegality`
--

CREATE TABLE `setlegality` (
  `format` varchar(40) NOT NULL,
  `cardset` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `standings`
--

CREATE TABLE `standings` (
  `player` varchar(40) DEFAULT NULL,
  `event` varchar(40) DEFAULT NULL,
  `active` tinyint(3) DEFAULT '0',
  `matches_played` tinyint(3) DEFAULT '0',
  `games_won` tinyint(3) DEFAULT '0',
  `games_played` tinyint(3) DEFAULT '0',
  `byes` tinyint(3) DEFAULT '0',
  `OP_Match` decimal(4,3) DEFAULT '0.000',
  `PL_Game` decimal(4,3) DEFAULT '0.000',
  `OP_Game` decimal(4,3) DEFAULT '0.000',
  `score` tinyint(3) DEFAULT '0',
  `id` int(11) NOT NULL,
  `seed` tinyint(3) NOT NULL,
  `matched` tinyint(1) NOT NULL,
  `matches_won` tinyint(3) DEFAULT '0',
  `draws` tinyint(3) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stewards`
--

CREATE TABLE `stewards` (
  `event` varchar(80) DEFAULT NULL,
  `player` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `subevents`
--

CREATE TABLE `subevents` (
  `parent` varchar(80) DEFAULT NULL,
  `rounds` tinyint(3) UNSIGNED NOT NULL DEFAULT '3',
  `timing` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `type` enum('Swiss','Single Elimination','League','Round Robin') DEFAULT NULL,
  `id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `subtype_bans`
--

CREATE TABLE `subtype_bans` (
  `name` varchar(40) NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tribes`
--

CREATE TABLE `tribes` (
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tribe_bans`
--

CREATE TABLE `tribe_bans` (
  `name` varchar(40) NOT NULL,
  `format` varchar(40) NOT NULL,
  `allowed` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trophies`
--

CREATE TABLE `trophies` (
  `event` varchar(80) NOT NULL,
  `image` mediumblob,
  `type` varchar(40) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archetypes`
--
ALTER TABLE `archetypes`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`card`,`format`),
  ADD KEY `format` (`format`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`name`,`cardset`),
  ADD KEY `cardset` (`cardset`);

--
-- Indexes for table `cardsets`
--
ALTER TABLE `cardsets`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `deckcontents`
--
ALTER TABLE `deckcontents`
  ADD PRIMARY KEY (`card`,`deck`,`issideboard`),
  ADD KEY `deck` (`deck`);

--
-- Indexes for table `deckerrors`
--
ALTER TABLE `deckerrors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `decks`
--
ALTER TABLE `decks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `archetype` (`archetype`);

--
-- Indexes for table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`event`,`player`),
  ADD KEY `player` (`player`),
  ADD KEY `deck` (`deck`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`name`),
  ADD KEY `format` (`format`),
  ADD KEY `host` (`host`),
  ADD KEY `series` (`series`),
  ADD KEY `cohost` (`cohost`);

--
-- Indexes for table `formats`
--
ALTER TABLE `formats`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playera` (`playera`),
  ADD KEY `playerb` (`playerb`),
  ADD KEY `subevent` (`subevent`);

--
-- Indexes for table `playerbans`
--
ALTER TABLE `playerbans`
  ADD KEY `PBIndex` (`series`,`player`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD KEY `player` (`player`);

--
-- Indexes for table `restricted`
--
ALTER TABLE `restricted`
  ADD PRIMARY KEY (`card`,`format`),
  ADD KEY `format` (`format`);

--
-- Indexes for table `restrictedtotribe`
--
ALTER TABLE `restrictedtotribe`
  ADD PRIMARY KEY (`card`,`format`),
  ADD KEY `format` (`format`);

--
-- Indexes for table `season_points`
--
ALTER TABLE `season_points`
  ADD KEY `series` (`series`),
  ADD KEY `event` (`event`),
  ADD KEY `player` (`player`);

--
-- Indexes for table `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `series_organizers`
--
ALTER TABLE `series_organizers`
  ADD KEY `player` (`player`),
  ADD KEY `series` (`series`);

--
-- Indexes for table `series_seasons`
--
ALTER TABLE `series_seasons`
  ADD PRIMARY KEY (`series`,`season`);

--
-- Indexes for table `setlegality`
--
ALTER TABLE `setlegality`
  ADD PRIMARY KEY (`format`,`cardset`),
  ADD KEY `cardset` (`cardset`);

--
-- Indexes for table `standings`
--
ALTER TABLE `standings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player` (`player`),
  ADD KEY `event` (`event`);

--
-- Indexes for table `stewards`
--
ALTER TABLE `stewards`
  ADD KEY `event` (`event`),
  ADD KEY `player` (`player`);

--
-- Indexes for table `subevents`
--
ALTER TABLE `subevents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`);

--
-- Indexes for table `tribes`
--
ALTER TABLE `tribes`
  ADD KEY `Tribe` (`name`);

--
-- Indexes for table `tribe_bans`
--
ALTER TABLE `tribe_bans`
  ADD KEY `TribeBansIndex` (`format`,`name`);

--
-- Indexes for table `trophies`
--
ALTER TABLE `trophies`
  ADD PRIMARY KEY (`event`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4603;

--
-- AUTO_INCREMENT for table `deckerrors`
--
ALTER TABLE `deckerrors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `decks`
--
ALTER TABLE `decks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10559;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37427;

--
-- AUTO_INCREMENT for table `standings`
--
ALTER TABLE `standings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=266;

--
-- AUTO_INCREMENT for table `subevents`
--
ALTER TABLE `subevents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1946;

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
-- Constraints for table `restricted`
--
ALTER TABLE `restricted`
  ADD CONSTRAINT `restricted_ibfk_1` FOREIGN KEY (`card`) REFERENCES `cards` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `restricted_ibfk_2` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE;

--
-- Constraints for table `season_points`
--
ALTER TABLE `season_points`
  ADD CONSTRAINT `season_points_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`),
  ADD CONSTRAINT `season_points_ibfk_2` FOREIGN KEY (`event`) REFERENCES `events` (`name`),
  ADD CONSTRAINT `season_points_ibfk_3` FOREIGN KEY (`player`) REFERENCES `players` (`name`);

--
-- Constraints for table `series_organizers`
--
ALTER TABLE `series_organizers`
  ADD CONSTRAINT `series_organizers_ibfk_1` FOREIGN KEY (`player`) REFERENCES `players` (`name`),
  ADD CONSTRAINT `series_organizers_ibfk_2` FOREIGN KEY (`series`) REFERENCES `series` (`name`);

--
-- Constraints for table `series_seasons`
--
ALTER TABLE `series_seasons`
  ADD CONSTRAINT `series_seasons_ibfk_1` FOREIGN KEY (`series`) REFERENCES `series` (`name`);

--
-- Constraints for table `setlegality`
--
ALTER TABLE `setlegality`
  ADD CONSTRAINT `setlegality_ibfk_1` FOREIGN KEY (`format`) REFERENCES `formats` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `setlegality_ibfk_2` FOREIGN KEY (`cardset`) REFERENCES `cardsets` (`name`) ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
