/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.5.2-MariaDB, for osx10.19 (arm64)
--
-- Host: localhost    Database: gatherling_test
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
-- Dumping data for table `bans`
--

LOCK TABLES `bans` WRITE;
/*!40000 ALTER TABLE `bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bans` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=204116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards`
--

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` VALUES
(184919,'{2}{W}{W}',4,1,0,0,0,0,0,'Ajani Goldmane','Magic 2010','Planeswalker - Ajani','mythic',NULL,0,1),
(184920,'{2}{W}{W}',4,1,0,0,0,0,0,'Angel\'s Mercy','Magic 2010','Instant','common',NULL,0,1),
(184921,'{3}{W}',4,1,0,0,0,0,0,'Armored Ascension','Magic 2010','Enchantment - Aura','uncommon',NULL,0,1),
(184922,'{3}{W}{W}',5,1,0,0,0,0,0,'Baneslayer Angel','Magic 2010','Creature - Angel','mythic',NULL,0,1),
(184923,'{1}{W}',2,1,0,0,0,0,0,'Blinding Mage','Magic 2010','Creature - Human Wizard','common',NULL,0,1),
(184924,'{4}{W}{W}',6,1,0,0,0,0,0,'Captain of the Watch','Magic 2010','Creature - Human Soldier','rare',NULL,0,1),
(184925,'{1}{W}',2,1,0,0,0,0,0,'Celestial Purge','Magic 2010','Instant','uncommon',NULL,0,1),
(184926,'{3}{W}',4,1,0,0,0,0,0,'Divine Verdict','Magic 2010','Instant','common',NULL,0,1),
(184927,'{W}',1,1,0,0,0,0,0,'Elite Vanguard','Magic 2010','Creature - Human Soldier','uncommon',NULL,0,1),
(184928,'{2}{W}',3,1,0,0,0,0,0,'Excommunicate','Magic 2010','Sorcery','common',NULL,0,1),
(184929,'{1}{W}',2,1,0,0,0,0,0,'Glorious Charge','Magic 2010','Instant','common',NULL,0,1),
(184930,'{2}{W}',3,1,0,0,0,0,0,'Griffin Sentinel','Magic 2010','Creature - Griffin','common',NULL,0,1),
(184931,'{2}{W}{W}',4,1,0,0,0,0,0,'Guardian Seraph','Magic 2010','Creature - Angel','rare',NULL,0,1),
(184932,'{W}',1,1,0,0,0,0,0,'Harm\'s Way','Magic 2010','Instant','uncommon',NULL,0,1),
(184933,'{W}',1,1,0,0,0,0,0,'Holy Strength','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(184934,'{1}{W}',2,1,0,0,0,0,0,'Honor of the Pure','Magic 2010','Enchantment','rare',NULL,0,1),
(184935,'{3}{W}',4,1,0,0,0,0,0,'Indestructibility','Magic 2010','Enchantment - Aura','rare',NULL,0,1),
(184936,'{W}',1,1,0,0,0,0,0,'Lifelink','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(184937,'{3}{W}{W}',5,1,0,0,0,0,0,'Lightwielder Paladin','Magic 2010','Creature - Human Knight','rare',NULL,0,1),
(184938,'{1}{W}{W}',3,1,0,0,0,0,0,'Mesa Enchantress','Magic 2010','Creature - Human Druid','rare',NULL,0,1),
(184939,'{4}{W}{W}',6,1,0,0,0,0,0,'Open the Vaults','Magic 2010','Sorcery','rare',NULL,0,1),
(184940,'{1}{W}',2,1,0,0,0,0,0,'Pacifism','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(184941,'{2}{W}',3,1,0,0,0,0,0,'Palace Guard','Magic 2010','Creature - Human Soldier','common',NULL,0,1),
(184942,'{3}{W}{W}{W}',6,1,0,0,0,0,0,'Planar Cleansing','Magic 2010','Sorcery','rare',NULL,0,1),
(184943,'{3}{W}',4,1,0,0,0,0,0,'Razorfoot Griffin','Magic 2010','Creature - Griffin','common',NULL,0,1),
(184944,'{2}{W}{W}',4,1,0,0,0,0,0,'Rhox Pikemaster','Magic 2010','Creature - Rhino Soldier','uncommon',NULL,0,1),
(184945,'{W}',1,1,0,0,0,0,0,'Righteousness','Magic 2010','Instant','uncommon',NULL,0,1),
(184946,'{2}{W}',3,1,0,0,0,0,0,'Safe Passage','Magic 2010','Instant','common',NULL,0,1),
(184947,'{3}{W}{W}',5,1,0,0,0,0,0,'Serra Angel','Magic 2010','Creature - Angel','uncommon',NULL,0,1),
(184948,'{4}{W}',5,1,0,0,0,0,0,'Siege Mastodon','Magic 2010','Creature - Elephant','common',NULL,0,1),
(184949,'{W}',1,1,0,0,0,0,0,'Silence','Magic 2010','Instant','rare',NULL,0,1),
(184950,'{1}{W}',2,1,0,0,0,0,0,'Silvercoat Lion','Magic 2010','Creature - Cat','common',NULL,0,1),
(184951,'{2}{W}',3,1,0,0,0,0,0,'Solemn Offering','Magic 2010','Sorcery','common',NULL,0,1),
(184952,'{W}',1,1,0,0,0,0,0,'Soul Warden','Magic 2010','Creature - Human Cleric','common',NULL,0,1),
(184953,'{1}{W}',2,1,0,0,0,0,0,'Stormfront Pegasus','Magic 2010','Creature - Pegasus','common',NULL,0,1),
(184954,'{2}{W}',3,1,0,0,0,0,0,'Tempest of Light','Magic 2010','Instant','uncommon',NULL,0,1),
(184955,'{2}{W}',3,1,0,0,0,0,0,'Undead Slayer','Magic 2010','Creature - Human Cleric','uncommon',NULL,0,1),
(184956,'{W}{W}',2,1,0,0,0,0,0,'Veteran Armorsmith','Magic 2010','Creature - Human Soldier','common',NULL,0,1),
(184957,'{2}{W}',3,1,0,0,0,0,0,'Veteran Swordsmith','Magic 2010','Creature - Human Soldier','common',NULL,0,1),
(184958,'{3}{W}',4,1,0,0,0,0,0,'Wall of Faith','Magic 2010','Creature - Wall','common',NULL,0,1),
(184959,'{W}{W}',2,1,0,0,0,0,0,'White Knight','Magic 2010','Creature - Human Knight','uncommon',NULL,0,1),
(184960,'{3}{U}{U}',5,0,0,0,1,0,0,'Air Elemental','Magic 2010','Creature - Elemental','uncommon',NULL,0,1),
(184961,'{1}{U}',2,0,0,0,1,0,0,'Alluring Siren','Magic 2010','Creature - Siren','uncommon',NULL,0,1),
(184962,'{1}{U}{U}',3,0,0,0,1,0,0,'Cancel','Magic 2010','Instant','common',NULL,0,1),
(184963,'{3}{U}',4,0,0,0,1,0,0,'Clone','Magic 2010','Creature - Shapeshifter','rare',NULL,0,1),
(184964,'{1}{U}',2,0,0,0,1,0,0,'Convincing Mirage','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(184965,'{1}{U}',2,0,0,0,1,0,0,'Coral Merfolk','Magic 2010','Creature - Merfolk','common',NULL,0,1),
(184966,'{3}{U}',4,0,0,0,1,0,0,'Disorient','Magic 2010','Instant','common',NULL,0,1),
(184967,'{2}{U}',3,0,0,0,1,0,0,'Divination','Magic 2010','Sorcery','common',NULL,0,1),
(184968,'{3}{U}{U}',5,0,0,0,1,0,0,'Djinn of Wishes','Magic 2010','Creature - Djinn','rare',NULL,0,1),
(184969,'{1}{U}',2,0,0,0,1,0,0,'Essence Scatter','Magic 2010','Instant','common',NULL,0,1),
(184970,'{2}{U}',3,0,0,0,1,0,0,'Fabricate','Magic 2010','Sorcery','uncommon',NULL,0,1),
(184971,'{1}{U}',2,0,0,0,1,0,0,'Flashfreeze','Magic 2010','Instant','uncommon',NULL,0,1),
(184972,'{5}{U}',6,0,0,0,1,0,0,'Hive Mind','Magic 2010','Enchantment','rare',NULL,0,1),
(184973,'{2}{U}',3,0,0,0,1,0,0,'Horned Turtle','Magic 2010','Creature - Turtle','common',NULL,0,1),
(184974,'{1}{U}',2,0,0,0,1,0,0,'Ice Cage','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(184975,'{1}{U}{U}',3,0,0,0,1,0,0,'Illusionary Servant','Magic 2010','Creature - Illusion','common',NULL,0,1),
(184976,'{1}{U}{U}',3,0,0,0,1,0,0,'Jace Beleren','Magic 2010','Planeswalker - Jace','mythic',NULL,0,1),
(184977,'{U}',1,0,0,0,1,0,0,'Jump','Magic 2010','Instant','common',NULL,0,1),
(184978,'{2}{U}{U}',4,0,0,0,1,0,0,'Levitation','Magic 2010','Enchantment','uncommon',NULL,0,1),
(184979,'{1}{U}',2,0,0,0,1,0,0,'Merfolk Looter','Magic 2010','Creature - Merfolk Rogue','common',NULL,0,1),
(184980,'{1}{U}{U}',3,0,0,0,1,0,0,'Merfolk Sovereign','Magic 2010','Creature - Merfolk Noble','rare',NULL,0,1),
(184981,'{3}{U}{U}',5,0,0,0,1,0,0,'Mind Control','Magic 2010','Enchantment - Aura','uncommon',NULL,0,1),
(184982,'{X}{U}{U}',2,0,0,0,1,0,0,'Mind Spring','Magic 2010','Sorcery','rare',NULL,0,1),
(184983,'{1}{U}',2,0,0,0,1,0,0,'Negate','Magic 2010','Instant','common',NULL,0,1),
(184984,'{1}{U}{U}',3,0,0,0,1,0,0,'Phantom Warrior','Magic 2010','Creature - Illusion Warrior','uncommon',NULL,0,1),
(184985,'{3}{U}',4,0,0,0,1,0,0,'Polymorph','Magic 2010','Sorcery','rare',NULL,0,1),
(184986,'{U}',1,0,0,0,1,0,0,'Ponder','Magic 2010','Sorcery','common',NULL,0,1),
(184987,'{1}{U}',2,0,0,0,1,0,0,'Sage Owl','Magic 2010','Creature - Bird','common',NULL,0,1),
(184988,'{4}{U}',5,0,0,0,1,0,0,'Serpent of the Endless Sea','Magic 2010','Creature - Serpent','common',NULL,0,1),
(184989,'{2}{U}{U}',4,0,0,0,1,0,0,'Sleep','Magic 2010','Sorcery','uncommon',NULL,0,1),
(184990,'{3}{U}',4,0,0,0,1,0,0,'Snapping Drake','Magic 2010','Creature - Drake','common',NULL,0,1),
(184991,'{5}{U}{U}',7,0,0,0,1,0,0,'Sphinx Ambassador','Magic 2010','Creature - Sphinx','mythic',NULL,0,1),
(184992,'{U}',1,0,0,0,1,0,0,'Telepathy','Magic 2010','Enchantment','uncommon',NULL,0,1),
(184993,'{3}{U}{U}',5,0,0,0,1,0,0,'Time Warp','Magic 2010','Sorcery','mythic',NULL,0,1),
(184994,'{U}',1,0,0,0,1,0,0,'Tome Scour','Magic 2010','Sorcery','common',NULL,0,1),
(184995,'{3}{U}{U}',5,0,0,0,1,0,0,'Traumatize','Magic 2010','Sorcery','rare',NULL,0,1),
(184996,'{U}{U}',2,0,0,0,1,0,0,'Twincast','Magic 2010','Instant','rare',NULL,0,1),
(184997,'{U}',1,0,0,0,1,0,0,'Unsummon','Magic 2010','Instant','common',NULL,0,1),
(184998,'{1}{U}{U}',3,0,0,0,1,0,0,'Wall of Frost','Magic 2010','Creature - Wall','uncommon',NULL,0,1),
(184999,'{2}{U}',3,0,0,0,1,0,0,'Wind Drake','Magic 2010','Creature - Drake','common',NULL,0,1),
(185000,'{U}',1,0,0,0,1,0,0,'Zephyr Sprite','Magic 2010','Creature - Faerie','common',NULL,0,1),
(185001,'{B}',1,0,0,0,0,1,0,'Acolyte of Xathrid','Magic 2010','Creature - Human Cleric','common',NULL,0,1),
(185002,'{2}{B}',3,0,0,0,0,1,0,'Assassinate','Magic 2010','Sorcery','common',NULL,0,1),
(185003,'{B}{B}',2,0,0,0,0,1,0,'Black Knight','Magic 2010','Creature - Human Knight','uncommon',NULL,0,1),
(185004,'{3}{B}',4,0,0,0,0,1,0,'Bog Wraith','Magic 2010','Creature - Wraith','uncommon',NULL,0,1),
(185005,'{1}{B}{B}',3,0,0,0,0,1,0,'Cemetery Reaper','Magic 2010','Creature - Zombie','rare',NULL,0,1),
(185006,'{1}{B}',2,0,0,0,0,1,0,'Child of Night','Magic 2010','Creature - Vampire','common',NULL,0,1),
(185007,'{X}{1}{B}',2,0,0,0,0,1,0,'Consume Spirit','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185008,'{B}',1,0,0,0,0,1,0,'Deathmark','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185009,'{2}{B}{B}',4,0,0,0,0,1,0,'Diabolic Tutor','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185010,'{B}',1,0,0,0,0,1,0,'Disentomb','Magic 2010','Sorcery','common',NULL,0,1),
(185011,'{1}{B}',2,0,0,0,0,1,0,'Doom Blade','Magic 2010','Instant','common',NULL,0,1),
(185012,'{1}{B}{B}',3,0,0,0,0,1,0,'Dread Warlock','Magic 2010','Creature - Human Wizard Warlock','common',NULL,0,1),
(185013,'{1}{B}',2,0,0,0,0,1,0,'Drudge Skeletons','Magic 2010','Creature - Skeleton','common',NULL,0,1),
(185014,'{B}',1,0,0,0,0,1,0,'Duress','Magic 2010','Sorcery','common',NULL,0,1),
(185015,'{3}{B}',4,0,0,0,0,1,0,'Gravedigger','Magic 2010','Creature - Zombie','common',NULL,0,1),
(185016,'{3}{B}{B}',5,0,0,0,0,1,0,'Haunting Echoes','Magic 2010','Sorcery','rare',NULL,0,1),
(185017,'{2}{B}{B}',4,0,0,0,0,1,0,'Howling Banshee','Magic 2010','Creature - Spirit','uncommon',NULL,0,1),
(185018,'{1}{B}{B}',3,0,0,0,0,1,0,'Hypnotic Specter','Magic 2010','Creature - Specter','rare',NULL,0,1),
(185019,'{2}{B}',3,0,0,0,0,1,0,'Kelinore Bat','Magic 2010','Creature - Bat','common',NULL,0,1),
(185020,'{3}{B}{B}',5,0,0,0,0,1,0,'Liliana Vess','Magic 2010','Planeswalker - Liliana','mythic',NULL,0,1),
(185021,'{2}{B}',3,0,0,0,0,1,0,'Looming Shade','Magic 2010','Creature - Shade','common',NULL,0,1),
(185022,'{2}{B}',3,0,0,0,0,1,0,'Megrim','Magic 2010','Enchantment','uncommon',NULL,0,1),
(185023,'{2}{B}',3,0,0,0,0,1,0,'Mind Rot','Magic 2010','Sorcery','common',NULL,0,1),
(185024,'{X}{B}{B}',2,0,0,0,0,1,0,'Mind Shatter','Magic 2010','Sorcery','rare',NULL,0,1),
(185025,'{5}{B}',6,0,0,0,0,1,0,'Nightmare','Magic 2010','Creature - Nightmare Horse','rare',NULL,0,1),
(185026,'{1}{B}{B}',3,0,0,0,0,1,0,'Relentless Rats','Magic 2010','Creature - Rat','uncommon',NULL,0,1),
(185027,'{4}{B}',5,0,0,0,0,1,0,'Rise from the Grave','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185028,'{1}{B}{B}',3,0,0,0,0,1,0,'Royal Assassin','Magic 2010','Creature - Human Assassin','rare',NULL,0,1),
(185029,'{3}{B}{B}',5,0,0,0,0,1,0,'Sanguine Bond','Magic 2010','Enchantment','rare',NULL,0,1),
(185030,'{B}{B}',2,0,0,0,0,1,0,'Sign in Blood','Magic 2010','Sorcery','common',NULL,0,1),
(185031,'{2}{B}',3,0,0,0,0,1,0,'Soul Bleed','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185032,'{3}{B}',4,0,0,0,0,1,0,'Tendrils of Corruption','Magic 2010','Instant','common',NULL,0,1),
(185033,'{B}{B}{B}',3,0,0,0,0,1,0,'Underworld Dreams','Magic 2010','Enchantment','rare',NULL,0,1),
(185034,'{B}',1,0,0,0,0,1,0,'Unholy Strength','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185035,'{2}{B}',3,0,0,0,0,1,0,'Vampire Aristocrat','Magic 2010','Creature - Vampire Rogue Noble','common',NULL,0,1),
(185036,'{1}{B}{B}{B}',4,0,0,0,0,1,0,'Vampire Nocturnus','Magic 2010','Creature - Vampire','mythic',NULL,0,1),
(185037,'{2}{B}',3,0,0,0,0,1,0,'Wall of Bone','Magic 2010','Creature - Skeleton Wall','uncommon',NULL,0,1),
(185038,'{2}{B}',3,0,0,0,0,1,0,'Warpath Ghoul','Magic 2010','Creature - Zombie','common',NULL,0,1),
(185039,'{B}',1,0,0,0,0,1,0,'Weakness','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185040,'{3}{B}{B}{B}',6,0,0,0,0,1,0,'Xathrid Demon','Magic 2010','Creature - Demon','mythic',NULL,0,1),
(185041,'{4}{B}',5,0,0,0,0,1,0,'Zombie Goliath','Magic 2010','Creature - Zombie Giant','common',NULL,0,1),
(185042,'{2}{R}',3,0,1,0,0,0,0,'Act of Treason','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185043,'{R}{R}{R}',3,0,1,0,0,0,0,'Ball Lightning','Magic 2010','Creature - Elemental','rare',NULL,0,1),
(185044,'{4}{R}',5,0,1,0,0,0,0,'Berserkers of Blood Ridge','Magic 2010','Creature - Human Berserker','common',NULL,0,1),
(185045,'{6}{R}{R}',8,0,1,0,0,0,0,'Bogardan Hellkite','Magic 2010','Creature - Dragon','mythic',NULL,0,1),
(185046,'{R}',1,0,1,0,0,0,0,'Burning Inquiry','Magic 2010','Sorcery','common',NULL,0,1),
(185047,'{R}',1,0,1,0,0,0,0,'Burst of Speed','Magic 2010','Sorcery','common',NULL,0,1),
(185048,'{3}{R}',4,0,1,0,0,0,0,'Canyon Minotaur','Magic 2010','Creature - Minotaur Warrior','common',NULL,0,1),
(185049,'{4}{R}{R}',6,0,1,0,0,0,0,'Capricious Efreet','Magic 2010','Creature - Efreet','rare',NULL,0,1),
(185050,'{3}{R}{R}',5,0,1,0,0,0,0,'Chandra Nalaar','Magic 2010','Planeswalker - Chandra','mythic',NULL,0,1),
(185051,'{2}{R}{R}',4,0,1,0,0,0,0,'Dragon Whelp','Magic 2010','Creature - Dragon','uncommon',NULL,0,1),
(185052,'{X}{R}',1,0,1,0,0,0,0,'Earthquake','Magic 2010','Sorcery','rare',NULL,0,1),
(185053,'{1}{R}{R}',3,0,1,0,0,0,0,'Fiery Hellhound','Magic 2010','Creature - Elemental Dog','common',NULL,0,1),
(185054,'{X}{R}',1,0,1,0,0,0,0,'Fireball','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185055,'{R}',1,0,1,0,0,0,0,'Firebreathing','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185056,'{1}{R}{R}',3,0,1,0,0,0,0,'Goblin Artillery','Magic 2010','Creature - Goblin Warrior','uncommon',NULL,0,1),
(185057,'{1}{R}{R}',3,0,1,0,0,0,0,'Goblin Chieftain','Magic 2010','Creature - Goblin','rare',NULL,0,1),
(185058,'{1}{R}',2,0,1,0,0,0,0,'Goblin Piker','Magic 2010','Creature - Goblin Warrior','common',NULL,0,1),
(185059,'{1}{R}',2,0,1,0,0,0,0,'Ignite Disorder','Magic 2010','Instant','uncommon',NULL,0,1),
(185060,'{4}{R}{R}',6,0,1,0,0,0,0,'Inferno Elemental','Magic 2010','Creature - Elemental','uncommon',NULL,0,1),
(185061,'{R}',1,0,1,0,0,0,0,'Jackal Familiar','Magic 2010','Creature - Jackal','common',NULL,0,1),
(185062,'{R}',1,0,1,0,0,0,0,'Kindled Fury','Magic 2010','Instant','common',NULL,0,1),
(185063,'{4}{R}',5,0,1,0,0,0,0,'Lava Axe','Magic 2010','Sorcery','common',NULL,0,1),
(185064,'{R}',1,0,1,0,0,0,0,'Lightning Bolt','Magic 2010','Instant','common',NULL,0,1),
(185065,'{3}{R}',4,0,1,0,0,0,0,'Lightning Elemental','Magic 2010','Creature - Elemental','common',NULL,0,1),
(185066,'{3}{R}{R}',5,0,1,0,0,0,0,'Magma Phoenix','Magic 2010','Creature - Phoenix','rare',NULL,0,1),
(185067,'{3}{R}',4,0,1,0,0,0,0,'Manabarbs','Magic 2010','Enchantment','rare',NULL,0,1),
(185068,'{2}{R}',3,0,1,0,0,0,0,'Panic Attack','Magic 2010','Sorcery','common',NULL,0,1),
(185069,'{2}{R}',3,0,1,0,0,0,0,'Prodigal Pyromancer','Magic 2010','Creature - Human Wizard','uncommon',NULL,0,1),
(185070,'{1}{R}',2,0,1,0,0,0,0,'Pyroclasm','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185071,'{R}',1,0,1,0,0,0,0,'Raging Goblin','Magic 2010','Creature - Goblin Berserker','common',NULL,0,1),
(185072,'{2}{R}',3,0,1,0,0,0,0,'Seismic Strike','Magic 2010','Instant','common',NULL,0,1),
(185073,'{1}{R}',2,0,1,0,0,0,0,'Shatter','Magic 2010','Instant','common',NULL,0,1),
(185074,'{4}{R}{R}',6,0,1,0,0,0,0,'Shivan Dragon','Magic 2010','Creature - Dragon','rare',NULL,0,1),
(185075,'{3}{R}{R}',5,0,1,0,0,0,0,'Siege-Gang Commander','Magic 2010','Creature - Goblin','rare',NULL,0,1),
(185076,'{1}{R}',2,0,1,0,0,0,0,'Sparkmage Apprentice','Magic 2010','Creature - Human Wizard','common',NULL,0,1),
(185077,'{2}{R}{R}',4,0,1,0,0,0,0,'Stone Giant','Magic 2010','Creature - Giant','uncommon',NULL,0,1),
(185078,'{2}{R}',3,0,1,0,0,0,0,'Trumpet Blast','Magic 2010','Instant','common',NULL,0,1),
(185079,'{2}{R}',3,0,1,0,0,0,0,'Viashino Spearhunter','Magic 2010','Creature - Lizard Warrior','common',NULL,0,1),
(185080,'{1}{R}{R}',3,0,1,0,0,0,0,'Wall of Fire','Magic 2010','Creature - Wall','uncommon',NULL,0,1),
(185081,'{5}{R}{R}{R}',8,0,1,0,0,0,0,'Warp World','Magic 2010','Sorcery','rare',NULL,0,1),
(185082,'{4}{R}',5,0,1,0,0,0,0,'Yawning Fissure','Magic 2010','Sorcery','common',NULL,0,1),
(185083,'{3}{G}{G}',5,0,0,1,0,0,0,'Acidic Slime','Magic 2010','Creature - Ooze','uncommon',NULL,0,1),
(185084,'{3}{G}{G}',5,0,0,1,0,0,0,'Ant Queen','Magic 2010','Creature - Insect','rare',NULL,0,1),
(185085,'{2}{G}',3,0,0,1,0,0,0,'Awakener Druid','Magic 2010','Creature - Human Druid','uncommon',NULL,0,1),
(185086,'{G}',1,0,0,1,0,0,0,'Birds of Paradise','Magic 2010','Creature - Bird','rare',NULL,0,1),
(185087,'{2}{G}',3,0,0,1,0,0,0,'Borderland Ranger','Magic 2010','Creature - Human Scout Ranger','common',NULL,0,1),
(185088,'{4}{G}',5,0,0,1,0,0,0,'Bountiful Harvest','Magic 2010','Sorcery','common',NULL,0,1),
(185089,'{4}{G}',5,0,0,1,0,0,0,'Bramble Creeper','Magic 2010','Creature - Elemental','common',NULL,0,1),
(185090,'{2}{G}',3,0,0,1,0,0,0,'Centaur Courser','Magic 2010','Creature - Centaur Warrior','common',NULL,0,1),
(185091,'{4}{G}{G}',6,0,0,1,0,0,0,'Craw Wurm','Magic 2010','Creature - Wurm','common',NULL,0,1),
(185092,'{2}{G}{G}',4,0,0,1,0,0,0,'Cudgel Troll','Magic 2010','Creature - Troll','uncommon',NULL,0,1),
(185093,'{1}{G}',2,0,0,1,0,0,0,'Deadly Recluse','Magic 2010','Creature - Spider','common',NULL,0,1),
(185094,'{1}{G}{G}',3,0,0,1,0,0,0,'Elvish Archdruid','Magic 2010','Creature - Elf Druid','rare',NULL,0,1),
(185095,'{3}{G}',4,0,0,1,0,0,0,'Elvish Piper','Magic 2010','Creature - Elf Shaman','rare',NULL,0,1),
(185096,'{1}{G}',2,0,0,1,0,0,0,'Elvish Visionary','Magic 2010','Creature - Elf Shaman','common',NULL,0,1),
(185097,'{3}{G}',4,0,0,1,0,0,0,'Emerald Oryx','Magic 2010','Creature - Antelope','common',NULL,0,1),
(185098,'{6}{G}',7,0,0,1,0,0,0,'Enormous Baloth','Magic 2010','Creature - Beast','uncommon',NULL,0,1),
(185099,'{3}{G}',4,0,0,1,0,0,0,'Entangling Vines','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185100,'{G}',1,0,0,1,0,0,0,'Fog','Magic 2010','Instant','common',NULL,0,1),
(185101,'{2}{G}{G}',4,0,0,1,0,0,0,'Garruk Wildspeaker','Magic 2010','Planeswalker - Garruk','mythic',NULL,0,1),
(185102,'{G}',1,0,0,1,0,0,0,'Giant Growth','Magic 2010','Instant','common',NULL,0,1),
(185103,'{3}{G}',4,0,0,1,0,0,0,'Giant Spider','Magic 2010','Creature - Spider','common',NULL,0,1),
(185104,'{1}{G}{G}',3,0,0,1,0,0,0,'Great Sable Stag','Magic 2010','Creature - Elk','rare',NULL,0,1),
(185105,'{6}{G}',7,0,0,1,0,0,0,'Howl of the Night Pack','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185106,'{5}{G}{G}',7,0,0,1,0,0,0,'Kalonian Behemoth','Magic 2010','Creature - Beast','rare',NULL,0,1),
(185107,'{G}',1,0,0,1,0,0,0,'Llanowar Elves','Magic 2010','Creature - Elf Druid','common',NULL,0,1),
(185108,'{4}{G}{G}',6,0,0,1,0,0,0,'Lurking Predators','Magic 2010','Enchantment','rare',NULL,0,1),
(185109,'{2}{G}{G}',4,0,0,1,0,0,0,'Master of the Wild Hunt','Magic 2010','Creature - Human Shaman','mythic',NULL,0,1),
(185110,'{3}{G}',4,0,0,1,0,0,0,'Might of Oaks','Magic 2010','Instant','rare',NULL,0,1),
(185111,'{3}{G}',4,0,0,1,0,0,0,'Mist Leopard','Magic 2010','Creature - Cat','common',NULL,0,1),
(185112,'{G}',1,0,0,1,0,0,0,'Mold Adder','Magic 2010','Creature - Fungus Snake','uncommon',NULL,0,1),
(185113,'{1}{G}',2,0,0,1,0,0,0,'Naturalize','Magic 2010','Instant','common',NULL,0,1),
(185114,'{1}{G}',2,0,0,1,0,0,0,'Nature\'s Spiral','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185115,'{2}{G}',3,0,0,1,0,0,0,'Oakenform','Magic 2010','Enchantment - Aura','common',NULL,0,1),
(185116,'{2}{G}{G}{G}',5,0,0,1,0,0,0,'Overrun','Magic 2010','Sorcery','uncommon',NULL,0,1),
(185117,'{3}{G}',4,0,0,1,0,0,0,'Prized Unicorn','Magic 2010','Creature - Unicorn','uncommon',NULL,0,1),
(185118,'{X}{G}',1,0,0,1,0,0,0,'Protean Hydra','Magic 2010','Creature - Hydra','mythic',NULL,0,1),
(185119,'{1}{G}',2,0,0,1,0,0,0,'Rampant Growth','Magic 2010','Sorcery','common',NULL,0,1),
(185120,'{1}{G}',2,0,0,1,0,0,0,'Regenerate','Magic 2010','Instant','common',NULL,0,1),
(185121,'{1}{G}',2,0,0,1,0,0,0,'Runeclaw Bear','Magic 2010','Creature - Bear','common',NULL,0,1),
(185122,'{4}{G}',5,0,0,1,0,0,0,'Stampeding Rhino','Magic 2010','Creature - Rhino','common',NULL,0,1),
(185123,'{X}{G}',1,0,0,1,0,0,0,'Windstorm','Magic 2010','Instant','uncommon',NULL,0,1),
(185124,'{2}',2,0,0,0,0,0,0,'Angel\'s Feather','Magic 2010','Artifact','uncommon',NULL,0,1),
(185125,'{5}',5,0,0,0,0,0,0,'Coat of Arms','Magic 2010','Artifact','rare',NULL,0,1),
(185126,'{11}',11,0,0,0,0,0,0,'Darksteel Colossus','Magic 2010','Artifact Creature - Golem','mythic',NULL,0,1),
(185127,'{2}',2,0,0,0,0,0,0,'Demon\'s Horn','Magic 2010','Artifact','uncommon',NULL,0,1),
(185128,'{2}',2,0,0,0,0,0,0,'Dragon\'s Claw','Magic 2010','Artifact','uncommon',NULL,0,1),
(185129,'{2}',2,0,0,0,0,0,0,'Gorgon Flail','Magic 2010','Artifact - Equipment','uncommon',NULL,0,1),
(185130,'{2}',2,0,0,0,0,0,0,'Howling Mine','Magic 2010','Artifact','rare',NULL,0,1),
(185131,'{2}',2,0,0,0,0,0,0,'Kraken\'s Eye','Magic 2010','Artifact','uncommon',NULL,0,1),
(185132,'{3}',3,0,0,0,0,0,0,'Magebane Armor','Magic 2010','Artifact - Equipment','rare',NULL,0,1),
(185133,'{5}',5,0,0,0,0,0,0,'Mirror of Fate','Magic 2010','Artifact','rare',NULL,0,1),
(185134,'{0}',0,0,0,0,0,0,0,'Ornithopter','Magic 2010','Artifact Creature - Thopter','uncommon',NULL,0,1),
(185135,'{1}',1,0,0,0,0,0,0,'Pithing Needle','Magic 2010','Artifact','rare',NULL,0,1),
(185136,'{7}',7,0,0,0,0,0,0,'Platinum Angel','Magic 2010','Artifact Creature - Angel','mythic',NULL,0,1),
(185137,'{4}',4,0,0,0,0,0,0,'Rod of Ruin','Magic 2010','Artifact','uncommon',NULL,0,1),
(185138,'{0}',0,0,0,0,0,0,0,'Spellbook','Magic 2010','Artifact','uncommon',NULL,0,1),
(185139,'{3}',3,0,0,0,0,0,0,'Whispersilk Cloak','Magic 2010','Artifact - Equipment','uncommon',NULL,0,1),
(185140,'{2}',2,0,0,0,0,0,0,'Wurm\'s Tooth','Magic 2010','Artifact','uncommon',NULL,0,1),
(185141,'',0,0,0,0,0,0,0,'Dragonskull Summit','Magic 2010','Land','rare',NULL,0,1),
(185142,'',0,0,0,0,0,0,0,'Drowned Catacomb','Magic 2010','Land','rare',NULL,0,1),
(185143,'',0,0,0,0,0,0,0,'Gargoyle Castle','Magic 2010','Land','rare',NULL,0,1),
(185144,'',0,0,0,0,0,0,0,'Glacial Fortress','Magic 2010','Land','rare',NULL,0,1),
(185145,'',0,0,0,0,0,0,0,'Rootbound Crag','Magic 2010','Land','rare',NULL,0,1),
(185146,'',0,0,0,0,0,0,0,'Sunpetal Grove','Magic 2010','Land','rare',NULL,0,1),
(185147,'',0,0,0,0,0,0,0,'Terramorphic Expanse','Magic 2010','Land','common',NULL,0,1),
(185148,'',0,0,0,0,0,0,0,'Plains','Magic 2010','Land - Plains','common',NULL,0,1),
(185152,'',0,0,0,0,0,0,0,'Island','Magic 2010','Land - Island','common',NULL,0,1),
(185156,'',0,0,0,0,0,0,0,'Swamp','Magic 2010','Land - Swamp','common',NULL,0,1),
(185160,'',0,0,0,0,0,0,0,'Mountain','Magic 2010','Land - Mountain','common',NULL,0,1),
(185164,'',0,0,0,0,0,0,0,'Forest','Magic 2010','Land - Forest','common',NULL,0,1),
(185168,'{2}{W}',3,1,0,0,0,0,0,'Acclaimed Contender','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185169,'{1}{W}',2,1,0,0,0,0,0,'All That Glitters','Throne of Eldraine','Enchantment - Aura','uncommon',NULL,0,1),
(185170,'{3}{W}',4,1,0,0,0,0,0,'Archon of Absolution','Throne of Eldraine','Creature - Archon','uncommon',NULL,0,1),
(185171,'{3}{W}',4,1,0,0,0,0,0,'Ardenvale Paladin','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185172,'{1}{W}',3,1,0,0,0,0,0,'Ardenvale Tactician/Dizzying Swoop','Throne of Eldraine','Instant - Adventure','common',NULL,0,1),
(185174,'{3}{W}',4,1,0,0,0,0,0,'Bartered Cow','Throne of Eldraine','Creature - Ox','common',NULL,0,1),
(185175,'{W}',1,1,0,0,0,0,0,'Beloved Princess','Throne of Eldraine','Creature - Human Noble','common',NULL,0,1),
(185176,'{1}{W}',2,1,0,0,0,0,0,'Charming Prince','Throne of Eldraine','Creature - Human Noble','rare',NULL,0,1),
(185177,'{4}{W}{W}',6,1,0,0,0,0,0,'The Circle of Loyalty','Throne of Eldraine','Artifact','mythic',NULL,0,1),
(185178,'{W}',1,1,0,0,0,0,0,'Deafening Silence','Throne of Eldraine','Enchantment','uncommon',NULL,0,1),
(185179,'{1}{W}',1,1,0,0,0,0,0,'Faerie Guidemother/Gift of the Fae','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185181,'{1}{W}',2,1,0,0,0,0,0,'Flutterfox','Throne of Eldraine','Creature - Fox','common',NULL,0,1),
(185182,'{2}{W}',3,1,0,0,0,0,0,'Fortifying Provisions','Throne of Eldraine','Enchantment','common',NULL,0,1),
(185183,'{2}{W}',1,1,0,0,0,0,0,'Giant Killer/Chop Down','Throne of Eldraine','Instant - Adventure','rare',NULL,0,1),
(185185,'{1}{W}',2,1,0,0,0,0,0,'Glass Casket','Throne of Eldraine','Artifact','uncommon',NULL,0,1),
(185186,'{2}{W}',3,1,0,0,0,0,0,'Happily Ever After','Throne of Eldraine','Enchantment','rare',NULL,0,1),
(185187,'{4}{W}{W}',6,1,0,0,0,0,0,'Harmonious Archon','Throne of Eldraine','Creature - Archon','mythic',NULL,0,1),
(185188,'{1}{W}',2,1,0,0,0,0,0,'Hushbringer','Throne of Eldraine','Creature - Faerie','rare',NULL,0,1),
(185189,'{2}{W}',3,1,0,0,0,0,0,'Knight of the Keep','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185190,'{W}{W}{W}',3,1,0,0,0,0,0,'Linden, the Steadfast Queen','Throne of Eldraine','Creature - Human Noble','rare',NULL,0,1),
(185191,'{2}{W}',5,1,0,0,0,0,0,'Lonesome Unicorn/Rider in Need','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185193,'{2}{W}',3,1,0,0,0,0,0,'Mysterious Pathlighter','Throne of Eldraine','Creature - Faerie','uncommon',NULL,0,1),
(185194,'{W}',1,1,0,0,0,0,0,'Outflank','Throne of Eldraine','Instant','common',NULL,0,1),
(185195,'{4}{W}',5,1,0,0,0,0,0,'Prized Griffin','Throne of Eldraine','Creature - Griffin','common',NULL,0,1),
(185196,'{2}{W}',3,1,0,0,0,0,0,'Rally for the Throne','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185197,'{3}{W}{W}',7,1,0,0,0,0,0,'Realm-Cloaked Giant/Cast Off','Throne of Eldraine','Sorcery - Adventure','mythic',NULL,0,1),
(185199,'{W}',1,1,0,0,0,0,0,'Righteousness','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185200,'{W}',2,1,0,0,0,0,0,'Shepherd of the Flock/Usher to Safety','Throne of Eldraine','Instant - Adventure','uncommon',NULL,0,1),
(185202,'{1}{W}',2,1,0,0,0,0,0,'Shining Armor','Throne of Eldraine','Artifact - Equipment','common',NULL,0,1),
(185203,'{3}{W}',4,1,0,0,0,0,0,'Silverflame Ritual','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185204,'{2}{W}',2,1,0,0,0,0,0,'Silverflame Squire/On Alert','Throne of Eldraine','Instant - Adventure','common',NULL,0,1),
(185206,'{3}{W}{W}',5,1,0,0,0,0,0,'Syr Alin, the Lion\'s Claw','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185207,'{1}{W}',2,1,0,0,0,0,0,'Trapped in the Tower','Throne of Eldraine','Enchantment - Aura','common',NULL,0,1),
(185208,'{2}{W}{W}',4,1,0,0,0,0,0,'True Love\'s Kiss','Throne of Eldraine','Instant','common',NULL,0,1),
(185209,'{W}',1,1,0,0,0,0,0,'Venerable Knight','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185210,'{1}{W}',2,1,0,0,0,0,0,'Worthy Knight','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185211,'{1}{W}',2,1,0,0,0,0,0,'Youthful Knight','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185212,'{2}{U}',3,0,0,0,1,0,0,'Animating Faerie/Bring to Life','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185214,'{1}{U}',3,0,0,0,1,0,0,'Brazen Borrower/Petty Theft','Throne of Eldraine','Instant - Adventure','mythic',NULL,0,1),
(185216,'{1}{U}{U}',3,0,0,0,1,0,0,'Charmed Sleep','Throne of Eldraine','Enchantment - Aura','common',NULL,0,1),
(185217,'{1}{U}',2,0,0,0,1,0,0,'Corridor Monitor','Throne of Eldraine','Artifact Creature - Construct','common',NULL,0,1),
(185218,'{1}{U}{U}',3,0,0,0,1,0,0,'Didn\'t Say Please','Throne of Eldraine','Instant','common',NULL,0,1),
(185219,'{2}{U}',3,0,0,0,1,0,0,'Emry, Lurker of the Loch','Throne of Eldraine','Creature - Merfolk Wizard','rare',NULL,0,1),
(185220,'{3}{U}',2,0,0,0,1,0,0,'Fae of Wishes/Granted','Throne of Eldraine','Sorcery - Adventure','rare',NULL,0,1),
(185222,'{1}{U}',2,0,0,0,1,0,0,'Faerie Vandal','Throne of Eldraine','Creature - Faerie Rogue','uncommon',NULL,0,1),
(185223,'{1}{U}',2,0,0,0,1,0,0,'Folio of Fancies','Throne of Eldraine','Artifact','rare',NULL,0,1),
(185224,'{1}{U}',2,0,0,0,1,0,0,'Frogify','Throne of Eldraine','Enchantment - Aura','uncommon',NULL,0,1),
(185225,'{X}{U}{U}{U}',3,0,0,0,1,0,0,'Gadwick, the Wizened','Throne of Eldraine','Creature - Human Wizard','rare',NULL,0,1),
(185226,'{2}{U}',2,0,0,0,1,0,0,'Hypnotic Sprite/Mesmeric Glare','Throne of Eldraine','Instant - Adventure','uncommon',NULL,0,1),
(185228,'{5}{U}{U}',7,0,0,0,1,0,0,'Into the Story','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185229,'{6}{U}{U}{U}',9,0,0,0,1,0,0,'The Magic Mirror','Throne of Eldraine','Artifact','mythic',NULL,0,1),
(185230,'{U}',1,0,0,0,1,0,0,'Mantle of Tides','Throne of Eldraine','Artifact - Equipment','common',NULL,0,1),
(185231,'{U}',1,0,0,0,1,0,0,'Merfolk Secretkeeper/Venture Deeper','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185233,'{2}{U}',3,0,0,0,1,0,0,'Midnight Clock','Throne of Eldraine','Artifact','rare',NULL,0,1),
(185234,'{1}{U}{U}',3,0,0,0,1,0,0,'Mirrormade','Throne of Eldraine','Enchantment','rare',NULL,0,1),
(185235,'{3}{U}',4,0,0,0,1,0,0,'Mistford River Turtle','Throne of Eldraine','Creature - Turtle','common',NULL,0,1),
(185236,'{5}{U}',6,0,0,0,1,0,0,'Moonlit Scavengers','Throne of Eldraine','Creature - Merfolk Rogue','common',NULL,0,1),
(185237,'{2}{U}',3,0,0,0,1,0,0,'Mystical Dispute','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185238,'{U}',1,0,0,0,1,0,0,'Opt','Throne of Eldraine','Instant','common',NULL,0,1),
(185239,'{U}',1,0,0,0,1,0,0,'Overwhelmed Apprentice','Throne of Eldraine','Creature - Human Wizard','uncommon',NULL,0,1),
(185240,'{1}{U}',3,0,0,0,1,0,0,'Queen of Ice/Rage of Winter','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185242,'{1}{U}',2,0,0,0,1,0,0,'Run Away Together','Throne of Eldraine','Instant','common',NULL,0,1),
(185243,'{4}{U}',5,0,0,0,1,0,0,'Sage of the Falls','Throne of Eldraine','Creature - Merfolk Wizard','uncommon',NULL,0,1),
(185244,'{U}',1,0,0,0,1,0,0,'So Tiny','Throne of Eldraine','Enchantment - Aura','common',NULL,0,1),
(185245,'{4}{U}',5,0,0,0,1,0,0,'Steelgaze Griffin','Throne of Eldraine','Creature - Griffin','common',NULL,0,1),
(185246,'{X}{U}{U}',2,0,0,0,1,0,0,'Stolen by the Fae','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185247,'{3}{U}{U}',5,0,0,0,1,0,0,'Syr Elenora, the Discerning','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185248,'{2}{U}',3,0,0,0,1,0,0,'Tome Raider','Throne of Eldraine','Creature - Faerie','common',NULL,0,1),
(185249,'{3}{U}',4,0,0,0,1,0,0,'Turn into a Pumpkin','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185250,'{4}{U}',5,0,0,0,1,0,0,'Unexplained Vision','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185251,'{1}{U}',2,0,0,0,1,0,0,'Vantress Gargoyle','Throne of Eldraine','Artifact Creature - Gargoyle','rare',NULL,0,1),
(185252,'{3}{U}',4,0,0,0,1,0,0,'Vantress Paladin','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185253,'{1}{U}',2,0,0,0,1,0,0,'Wishful Merfolk','Throne of Eldraine','Creature - Merfolk','common',NULL,0,1),
(185254,'{U}',1,0,0,0,1,0,0,'Witching Well','Throne of Eldraine','Artifact','common',NULL,0,1),
(185255,'{B}{B}{B}',3,0,0,0,0,1,0,'Ayara, First of Locthwain','Throne of Eldraine','Creature - Elf Noble','rare',NULL,0,1),
(185256,'{2}{B}{B}',4,0,0,0,0,1,0,'Bake into a Pie','Throne of Eldraine','Instant','common',NULL,0,1),
(185257,'{4}{B}',5,0,0,0,0,1,0,'Barrow Witches','Throne of Eldraine','Creature - Human Warlock','common',NULL,0,1),
(185258,'{2}{B}',3,0,0,0,0,1,0,'Belle of the Brawl','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185259,'{1}{B}',2,0,0,0,0,1,0,'Blacklance Paragon','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185260,'{3}{B}{B}',5,0,0,0,0,1,0,'Bog Naughty','Throne of Eldraine','Creature - Faerie','uncommon',NULL,0,1),
(185261,'{B}',1,0,0,0,0,1,0,'Cauldron Familiar','Throne of Eldraine','Creature - Cat','uncommon',NULL,0,1),
(185262,'{B}',1,0,0,0,0,1,0,'A-Cauldron Familiar','Throne of Eldraine','Creature - Cat','uncommon',NULL,0,0),
(185263,'{10}{B}{B}',12,0,0,0,0,1,0,'The Cauldron of Eternity','Throne of Eldraine','Artifact','mythic',NULL,0,1),
(185264,'{4}{B}',5,0,0,0,0,1,0,'Cauldron\'s Gift','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185265,'{3}{B}{B}',5,0,0,0,0,1,0,'Clackbridge Troll','Throne of Eldraine','Creature - Troll','rare',NULL,0,1),
(185266,'{1}{B}',2,0,0,0,0,1,0,'Epic Downfall','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185267,'{B}',1,0,0,0,0,1,0,'Eye Collector','Throne of Eldraine','Creature - Faerie','common',NULL,0,1),
(185268,'{4}{B}',5,0,0,0,0,1,0,'Festive Funeral','Throne of Eldraine','Instant','common',NULL,0,1),
(185269,'{2}{B}',3,0,0,0,0,1,0,'Foreboding Fruit','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185270,'{1}{B}',2,0,0,0,0,1,0,'Forever Young','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185271,'{2}{B}',1,0,0,0,0,1,0,'Foulmire Knight/Profane Insight','Throne of Eldraine','Instant - Adventure','uncommon',NULL,0,1),
(185273,'{1}{B}',2,0,0,0,0,1,0,'Giant\'s Skewer','Throne of Eldraine','Artifact - Equipment','common',NULL,0,1),
(185274,'{B}',1,0,0,0,0,1,0,'Lash of Thorns','Throne of Eldraine','Instant','common',NULL,0,1),
(185275,'{3}{B}',4,0,0,0,0,1,0,'Locthwain Paladin','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185276,'{1}{B}{B}',3,0,0,0,0,1,0,'Lost Legion','Throne of Eldraine','Creature - Spirit Knight','common',NULL,0,1),
(185277,'{1}{B}',2,0,0,0,0,1,0,'Malevolent Noble','Throne of Eldraine','Creature - Human Noble','common',NULL,0,1),
(185278,'{2}{B}',3,0,0,0,0,1,0,'Memory Theft','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185279,'{1}{B}{B}',3,0,0,0,0,1,0,'Murderous Rider/Swift End','Throne of Eldraine','Instant - Adventure','rare',NULL,0,1),
(185281,'{1}{B}{B}',3,0,0,0,0,1,0,'Oathsworn Knight','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185282,'{1}{B}',2,0,0,0,0,1,0,'Order of Midnight/Alter Fate','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185284,'{1}{B}',2,0,0,0,0,1,0,'Piper of the Swarm','Throne of Eldraine','Creature - Human Warlock','rare',NULL,0,1),
(185285,'{2}{B}{B}',4,0,0,0,0,1,0,'Rankle, Master of Pranks','Throne of Eldraine','Creature - Faerie Rogue','mythic',NULL,0,1),
(185286,'{3}{B}',7,0,0,0,0,1,0,'Reaper of Night/Harvest Fear','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185288,'{1}{B}',2,0,0,0,0,1,0,'Reave Soul','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185289,'{3}{B}',4,0,0,0,0,1,0,'Revenge of Ravens','Throne of Eldraine','Enchantment','uncommon',NULL,0,1),
(185290,'{B}',2,0,0,0,0,1,0,'Smitten Swordmaster/Curry Favor','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185292,'{B}',1,0,0,0,0,1,0,'Specter\'s Shriek','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185293,'{3}{B}{B}',5,0,0,0,0,1,0,'Syr Konrad, the Grim','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185294,'{2}{B}',3,0,0,0,0,1,0,'Tempting Witch','Throne of Eldraine','Creature - Human Warlock','common',NULL,0,1),
(185295,'{3}{B}',4,0,0,0,0,1,0,'Wicked Guardian','Throne of Eldraine','Creature - Human Noble','common',NULL,0,1),
(185296,'{1}{B}',2,0,0,0,0,1,0,'Wishclaw Talisman','Throne of Eldraine','Artifact','rare',NULL,0,1),
(185297,'{1}{B}{B}',3,0,0,0,0,1,0,'Witch\'s Vengeance','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185298,'{R}',1,0,1,0,0,0,0,'Barge In','Throne of Eldraine','Instant','common',NULL,0,1),
(185299,'{1}{R}',2,0,1,0,0,0,0,'Bloodhaze Wolverine','Throne of Eldraine','Creature - Wolverine','common',NULL,0,1),
(185300,'{2}{R}',3,0,1,0,0,0,0,'Blow Your House Down','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185301,'{1}{R}',3,0,1,0,0,0,0,'Bonecrusher Giant/Stomp','Throne of Eldraine','Instant - Adventure','rare',NULL,0,1),
(185303,'{2}{R}',3,0,1,0,0,0,0,'Brimstone Trebuchet','Throne of Eldraine','Artifact Creature - Wall','common',NULL,0,1),
(185304,'{4}{R}',5,0,1,0,0,0,0,'Burning-Yard Trainer','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185305,'{R}',1,0,1,0,0,0,0,'Claim the Firstborn','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185306,'{1}{R}',2,0,1,0,0,0,0,'Crystal Slipper','Throne of Eldraine','Artifact - Equipment','common',NULL,0,1),
(185307,'{4}{R}{R}',6,0,1,0,0,0,0,'Embercleave','Throne of Eldraine','Artifact - Equipment','mythic',NULL,0,1),
(185308,'{3}{R}',4,0,1,0,0,0,0,'Embereth Paladin','Throne of Eldraine','Creature - Human Knight','common',NULL,0,1),
(185309,'{R}',2,0,1,0,0,0,0,'Embereth Shieldbreaker/Battle Display','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185311,'{2}{R}',3,0,1,0,0,0,0,'Ferocity of the Wilds','Throne of Eldraine','Enchantment','uncommon',NULL,0,1),
(185312,'{R}',1,0,1,0,0,0,0,'Fervent Champion','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185313,'{3}{R}',4,0,1,0,0,0,0,'Fires of Invention','Throne of Eldraine','Enchantment','rare',NULL,0,1),
(185314,'{4}{R}',5,0,1,0,0,0,0,'A-Fires of Invention','Throne of Eldraine','Enchantment','rare',NULL,0,0),
(185315,'{1}{R}',2,0,1,0,0,0,0,'Fling','Throne of Eldraine','Instant','common',NULL,0,1),
(185316,'{1}{R}{R}{R}',4,0,1,0,0,0,0,'Irencrag Feat','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185317,'{2}{R}',3,0,1,0,0,0,0,'Irencrag Pyromancer','Throne of Eldraine','Creature - Human Wizard','rare',NULL,0,1),
(185318,'{1}{R}',2,0,1,0,0,0,0,'Joust','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185319,'{3}{R}',4,0,1,0,0,0,0,'Mad Ratter','Throne of Eldraine','Creature - Goblin','uncommon',NULL,0,1),
(185320,'{R}',3,0,1,0,0,0,0,'Merchant of the Vale/Haggle','Throne of Eldraine','Instant - Adventure','common',NULL,0,1),
(185322,'{3}{R}',4,0,1,0,0,0,0,'Ogre Errant','Throne of Eldraine','Creature - Ogre Knight','common',NULL,0,1),
(185323,'{2}{R}{R}',4,0,1,0,0,0,0,'Opportunistic Dragon','Throne of Eldraine','Creature - Dragon','rare',NULL,0,1),
(185324,'{2}{R}',3,0,1,0,0,0,0,'Raging Redcap','Throne of Eldraine','Creature - Goblin Knight','common',NULL,0,1),
(185325,'{R}',1,0,1,0,0,0,0,'Redcap Melee','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185326,'{2}{R}',3,0,1,0,0,0,0,'Redcap Raiders','Throne of Eldraine','Creature - Goblin Warrior','common',NULL,0,1),
(185327,'{R}',2,0,1,0,0,0,0,'Rimrock Knight/Boulder Rush','Throne of Eldraine','Instant - Adventure','common',NULL,0,1),
(185329,'{1}{R}',2,0,1,0,0,0,0,'Robber of the Rich','Throne of Eldraine','Creature - Human Archer Rogue','mythic',NULL,0,1),
(185330,'{1}{R}',2,0,1,0,0,0,0,'Scorching Dragonfire','Throne of Eldraine','Instant','common',NULL,0,1),
(185331,'{4}{R}',5,0,1,0,0,0,0,'Searing Barrage','Throne of Eldraine','Instant','common',NULL,0,1),
(185332,'{1}{R}',2,0,1,0,0,0,0,'Seven Dwarves','Throne of Eldraine','Creature - Dwarf','common',NULL,0,1),
(185333,'{3}{R}',4,0,1,0,0,0,0,'Skullknocker Ogre','Throne of Eldraine','Creature - Ogre','uncommon',NULL,0,1),
(185334,'{2}{R}',3,0,1,0,0,0,0,'Slaying Fire','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185335,'{6}{R}',7,0,1,0,0,0,0,'Sundering Stroke','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185336,'{3}{R}{R}',5,0,1,0,0,0,0,'Syr Carah, the Bold','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185337,'{1}{R}',2,0,1,0,0,0,0,'Thrill of Possibility','Throne of Eldraine','Instant','common',NULL,0,1),
(185338,'{1}{R}{R}{R}',4,0,1,0,0,0,0,'Torbran, Thane of Red Fell','Throne of Eldraine','Creature - Dwarf Noble','rare',NULL,0,1),
(185339,'{R}',1,0,1,0,0,0,0,'Weaselback Redcap','Throne of Eldraine','Creature - Goblin Knight','common',NULL,0,1),
(185340,'{2}{G}',7,0,0,1,0,0,0,'Beanstalk Giant/Fertile Footsteps','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185342,'{G}',2,0,0,1,0,0,0,'Curious Pair/Treats to Share','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185344,'{G}',1,0,0,1,0,0,0,'Edgewall Innkeeper','Throne of Eldraine','Creature - Human Peasant','uncommon',NULL,0,1),
(185345,'{2}{G}{G}{G}{G}',6,0,0,1,0,0,0,'Feasting Troll King','Throne of Eldraine','Creature - Troll Noble','rare',NULL,0,1),
(185346,'{1}{G}',2,0,0,1,0,0,0,'Fell the Pheasant','Throne of Eldraine','Instant','common',NULL,0,1),
(185347,'{2}{G}{G}',4,0,0,1,0,0,0,'Fierce Witchstalker','Throne of Eldraine','Creature - Wolf','common',NULL,0,1),
(185348,'{5}{G}{G}',1,0,0,1,0,0,0,'Flaxen Intruder/Welcome Home','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185350,'{1}{G}',4,0,0,1,0,0,0,'Garenbrig Carver/Shield\'s Might','Throne of Eldraine','Instant - Adventure','common',NULL,0,1),
(185352,'{4}{G}',5,0,0,1,0,0,0,'Garenbrig Paladin','Throne of Eldraine','Creature - Giant Knight','common',NULL,0,1),
(185353,'{1}{G}',2,0,0,1,0,0,0,'Garenbrig Squire','Throne of Eldraine','Creature - Human Soldier','common',NULL,0,1),
(185354,'{2}{G}',3,0,0,1,0,0,0,'Giant Opportunity','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185355,'{G}',1,0,0,1,0,0,0,'Gilded Goose','Throne of Eldraine','Creature - Bird','rare',NULL,0,1),
(185356,'{7}{G}{G}',9,0,0,1,0,0,0,'The Great Henge','Throne of Eldraine','Artifact','mythic',NULL,0,1),
(185357,'{1}{G}',2,0,0,1,0,0,0,'Insatiable Appetite','Throne of Eldraine','Instant','common',NULL,0,1),
(185358,'{3}{G}{G}',5,0,0,1,0,0,0,'Keeper of Fables','Throne of Eldraine','Creature - Cat','uncommon',NULL,0,1),
(185359,'{1}{G}',2,0,0,1,0,0,0,'Kenrith\'s Transformation','Throne of Eldraine','Enchantment - Aura','uncommon',NULL,0,1),
(185360,'{G}',3,0,0,1,0,0,0,'Lovestruck Beast/Heart\'s Desire','Throne of Eldraine','Sorcery - Adventure','rare',NULL,0,1),
(185362,'{1}{G}',2,0,0,1,0,0,0,'Maraleaf Rider','Throne of Eldraine','Creature - Elf Knight','common',NULL,0,1),
(185363,'{3}{G}',4,0,0,1,0,0,0,'Oakhame Adversary','Throne of Eldraine','Creature - Elf Warrior','uncommon',NULL,0,1),
(185364,'{3}{G}',4,0,0,1,0,0,0,'Once and Future','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185365,'{1}{G}',2,0,0,1,0,0,0,'Once Upon a Time','Throne of Eldraine','Instant','rare',NULL,0,1),
(185366,'{3}{G}',4,0,0,1,0,0,0,'Outmuscle','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185367,'{2}{G}{G}',4,0,0,1,0,0,0,'Questing Beast','Throne of Eldraine','Creature - Beast','mythic',NULL,0,1),
(185368,'{4}{G}',5,0,0,1,0,0,0,'Return of the Wildspeaker','Throne of Eldraine','Instant','rare',NULL,0,1),
(185369,'{1}{G}',2,0,0,1,0,0,0,'Return to Nature','Throne of Eldraine','Instant','common',NULL,0,1),
(185370,'{G}',3,0,0,1,0,0,0,'Rosethorn Acolyte/Seasonal Ritual','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185372,'{G}',1,0,0,1,0,0,0,'Rosethorn Halberd','Throne of Eldraine','Artifact - Equipment','common',NULL,0,1),
(185373,'{2}{G}',3,0,0,1,0,0,0,'Sporecap Spider','Throne of Eldraine','Creature - Spider','common',NULL,0,1),
(185374,'{G}{G}',2,0,0,1,0,0,0,'Syr Faren, the Hengehammer','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185375,'{3}{G}',4,0,0,1,0,0,0,'Tall as a Beanstalk','Throne of Eldraine','Enchantment - Aura','common',NULL,0,1),
(185376,'{1}{G}',2,0,0,1,0,0,0,'Trail of Crumbs','Throne of Eldraine','Enchantment','uncommon',NULL,0,1),
(185377,'{3}{G}',6,0,0,1,0,0,0,'Tuinvale Treefolk/Oaken Boon','Throne of Eldraine','Sorcery - Adventure','common',NULL,0,1),
(185379,'{2}{G}{G}',4,0,0,1,0,0,0,'Wicked Wolf','Throne of Eldraine','Creature - Wolf','rare',NULL,0,1),
(185380,'{1}{G}',2,0,0,1,0,0,0,'Wildborn Preserver','Throne of Eldraine','Creature - Elf Archer','rare',NULL,0,1),
(185381,'{G}',1,0,0,1,0,0,0,'Wildwood Tracker','Throne of Eldraine','Creature - Elf Warrior','common',NULL,0,1),
(185382,'{4}{G}{G}',6,0,0,1,0,0,0,'Wolf\'s Quarry','Throne of Eldraine','Sorcery','common',NULL,0,1),
(185383,'{G}{G}{G}',3,0,0,1,0,0,0,'Yorvo, Lord of Garenbrig','Throne of Eldraine','Creature - Giant Noble','rare',NULL,0,1),
(185384,'{X}{W}{U}',2,1,0,0,1,0,0,'Dance of the Manse','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185385,'{2}{W}{B}',4,1,0,0,0,1,0,'Doom Foretold','Throne of Eldraine','Enchantment','rare',NULL,0,1),
(185386,'{U}{B}',2,0,0,0,1,1,0,'Drown in the Loch','Throne of Eldraine','Instant','uncommon',NULL,0,1),
(185387,'{3}{R}{G}',5,0,1,1,0,0,0,'Escape to the Wilds','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185388,'{1}{G}{W}',3,1,0,1,0,0,0,'Faeburrow Elder','Throne of Eldraine','Creature - Treefolk Druid','rare',NULL,0,1),
(185389,'{4}{B}{G}',6,0,0,1,0,1,0,'Garruk, Cursed Huntsman','Throne of Eldraine','Planeswalker - Garruk','mythic',NULL,0,1),
(185390,'{1}{R}{G}',3,0,1,1,0,0,0,'Grumgully, the Generous','Throne of Eldraine','Creature - Goblin Shaman','uncommon',NULL,0,1),
(185391,'{U}{R}',2,0,1,0,1,0,0,'Improbable Alliance','Throne of Eldraine','Enchantment','uncommon',NULL,0,1),
(185392,'{R}{W}',2,1,1,0,0,0,0,'Inspiring Veteran','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185393,'{4}{U}{B}',6,0,0,0,1,1,0,'Lochmere Serpent','Throne of Eldraine','Creature - Serpent','rare',NULL,0,1),
(185394,'{G}{U}',2,0,0,1,1,0,0,'Maraleaf Pixie','Throne of Eldraine','Creature - Faerie','uncommon',NULL,0,1),
(185395,'{1}{G}{U}',3,0,0,1,1,0,0,'Oko, Thief of Crowns','Throne of Eldraine','Planeswalker - Oko','mythic',NULL,0,1),
(185396,'{1}{R}{W}{W}',4,1,1,0,0,0,0,'Outlaws\' Merriment','Throne of Eldraine','Enchantment','mythic',NULL,0,1),
(185397,'{1}{U}{R}',3,0,1,0,1,0,0,'The Royal Scions','Throne of Eldraine','Planeswalker - Will Rowan','mythic',NULL,0,1),
(185398,'{1}{B}{G}',3,0,0,1,0,1,0,'Savvy Hunter','Throne of Eldraine','Creature - Human Warrior','uncommon',NULL,0,1),
(185399,'{1}{W}{U}',3,1,0,0,1,0,0,'Shinechaser','Throne of Eldraine','Creature - Faerie','uncommon',NULL,0,1),
(185400,'{B}{R}',2,0,1,0,0,1,0,'Steelclaw Lance','Throne of Eldraine','Artifact - Equipment','uncommon',NULL,0,1),
(185401,'{B}{R}',2,0,1,0,0,1,0,'Stormfist Crusader','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185402,'{1}{G}{W}',3,1,0,1,0,0,0,'Wandermare','Throne of Eldraine','Creature - Horse','uncommon',NULL,0,1),
(185403,'{W}{B}',2,1,0,0,0,1,0,'Wintermoor Commander','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185404,'{W/U}{W/U}{W/U}{W/U}',4,1,0,0,1,0,0,'Arcanist\'s Owl','Throne of Eldraine','Artifact Creature - Bird','uncommon',NULL,0,1),
(185405,'{U/B}{U/B}{U/B}{U/B}',4,0,0,0,1,1,0,'Covetous Urge','Throne of Eldraine','Sorcery','uncommon',NULL,0,1),
(185406,'{B/G}{B/G}{B/G}{B/G}',4,0,0,1,0,1,0,'Deathless Knight','Throne of Eldraine','Creature - Skeleton Knight','uncommon',NULL,0,1),
(185407,'{B/R}{B/R}{B/R}{B/R}',4,0,1,0,0,1,0,'Elite Headhunter','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185408,'{R/W}{R/W}{R/W}{R/W}',4,1,1,0,0,0,0,'Fireborn Knight','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185409,'{U/R}{U/R}{U/R}{U/R}',4,0,1,0,1,0,0,'Loch Dragon','Throne of Eldraine','Creature - Dragon','uncommon',NULL,0,1),
(185410,'{G/W}{G/W}{G/W}{G/W}',4,1,0,1,0,0,0,'Oakhame Ranger/Bring Back','Throne of Eldraine','Sorcery - Adventure','uncommon',NULL,0,1),
(185412,'{R/G}{R/G}{R/G}{R/G}',4,0,1,1,0,0,0,'Rampart Smasher','Throne of Eldraine','Creature - Giant','uncommon',NULL,0,1),
(185413,'{W/B}{W/B}{W/B}{W/B}',4,1,0,0,0,1,0,'Resolute Rider','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185414,'{G/U}{G/U}{G/U}{G/U}',4,0,0,1,1,0,0,'Thunderous Snapper','Throne of Eldraine','Creature - Turtle Hydra','uncommon',NULL,0,1),
(185415,'{3}',3,0,0,0,0,0,0,'Clockwork Servant','Throne of Eldraine','Artifact Creature - Gnome','uncommon',NULL,0,1),
(185416,'{2}',2,0,0,0,0,0,0,'Crashing Drawbridge','Throne of Eldraine','Artifact Creature - Wall','common',NULL,0,1),
(185417,'{5}',5,0,0,0,0,0,0,'Enchanted Carriage','Throne of Eldraine','Artifact - Vehicle','uncommon',NULL,0,1),
(185418,'{1}',1,0,0,0,0,0,0,'Gingerbrute','Throne of Eldraine','Artifact Creature - Food Golem','common',NULL,0,1),
(185419,'{2}',2,0,0,0,0,0,0,'Golden Egg','Throne of Eldraine','Artifact - Food','common',NULL,0,1),
(185420,'{3}',3,0,0,0,0,0,0,'Henge Walker','Throne of Eldraine','Artifact Creature - Golem','common',NULL,0,1),
(185421,'{3}',3,0,0,0,0,0,0,'Heraldic Banner','Throne of Eldraine','Artifact','uncommon',NULL,0,1),
(185422,'{1}',1,0,0,0,0,0,0,'Inquisitive Puppet','Throne of Eldraine','Artifact Creature - Construct','uncommon',NULL,0,1),
(185423,'{2}',2,0,0,0,0,0,0,'Jousting Dummy','Throne of Eldraine','Artifact Creature - Scarecrow Knight','common',NULL,0,1),
(185424,'{1}',1,0,0,0,0,0,0,'Locthwain Gargoyle','Throne of Eldraine','Artifact Creature - Gargoyle','common',NULL,0,1),
(185425,'{2}',2,0,0,0,0,0,0,'Lucky Clover','Throne of Eldraine','Artifact','uncommon',NULL,0,1),
(185426,'{6}',6,0,0,0,0,0,0,'Prophet of the Peak','Throne of Eldraine','Artifact Creature - Cat','common',NULL,0,1),
(185427,'{7}',7,0,0,0,0,0,0,'Roving Keep','Throne of Eldraine','Artifact Creature - Wall','common',NULL,0,1),
(185428,'{1}',1,0,0,0,0,0,0,'Scalding Cauldron','Throne of Eldraine','Artifact','common',NULL,0,1),
(185429,'{3}',3,0,0,0,0,0,0,'Shambling Suit','Throne of Eldraine','Artifact Creature - Construct','uncommon',NULL,0,1),
(185430,'{4}',4,0,0,0,0,0,0,'Signpost Scarecrow','Throne of Eldraine','Artifact Creature - Scarecrow','common',NULL,0,1),
(185431,'{2}',2,0,0,0,0,0,0,'Sorcerer\'s Broom','Throne of Eldraine','Artifact Creature - Spirit','uncommon',NULL,0,1),
(185432,'{2}',2,0,0,0,0,0,0,'Sorcerous Spyglass','Throne of Eldraine','Artifact','rare',NULL,0,1),
(185433,'{3}',3,0,0,0,0,0,0,'Spinning Wheel','Throne of Eldraine','Artifact','uncommon',NULL,0,1),
(185434,'{X}',0,0,0,0,0,0,0,'Stonecoil Serpent','Throne of Eldraine','Artifact Creature - Snake','rare',NULL,0,1),
(185435,'{4}',4,0,0,0,0,0,0,'Weapon Rack','Throne of Eldraine','Artifact','common',NULL,0,1),
(185436,'{1}',1,0,0,0,0,0,0,'Witch\'s Oven','Throne of Eldraine','Artifact','uncommon',NULL,0,1),
(185437,'',0,0,0,0,0,0,0,'Castle Ardenvale','Throne of Eldraine','Land','rare',NULL,0,1),
(185438,'',0,0,0,0,0,0,0,'Castle Embereth','Throne of Eldraine','Land','rare',NULL,0,1),
(185439,'',0,0,0,0,0,0,0,'Castle Garenbrig','Throne of Eldraine','Land','rare',NULL,0,1),
(185440,'',0,0,0,0,0,0,0,'Castle Locthwain','Throne of Eldraine','Land','rare',NULL,0,1),
(185441,'',0,0,0,0,0,0,0,'Castle Vantress','Throne of Eldraine','Land','rare',NULL,0,1),
(185442,'',0,0,0,0,0,0,0,'Dwarven Mine','Throne of Eldraine','Land - Mountain','common',NULL,0,1),
(185443,'',0,0,0,0,0,0,0,'Fabled Passage','Throne of Eldraine','Land','rare',NULL,0,1),
(185444,'',0,0,0,0,0,0,0,'Gingerbread Cabin','Throne of Eldraine','Land - Forest','common',NULL,0,1),
(185445,'',0,0,0,0,0,0,0,'Idyllic Grange','Throne of Eldraine','Land - Plains','common',NULL,0,1),
(185446,'',0,0,0,0,0,0,0,'Mystic Sanctuary','Throne of Eldraine','Land - Island','common',NULL,0,1),
(185447,'',0,0,0,0,0,0,0,'Tournament Grounds','Throne of Eldraine','Land','uncommon',NULL,0,1),
(185448,'',0,0,0,0,0,0,0,'Witch\'s Cottage','Throne of Eldraine','Land - Swamp','common',NULL,0,1),
(185449,'',0,0,0,0,0,0,0,'Plains','Throne of Eldraine','Land - Plains','common',NULL,0,1),
(185453,'',0,0,0,0,0,0,0,'Island','Throne of Eldraine','Land - Island','common',NULL,0,1),
(185457,'',0,0,0,0,0,0,0,'Swamp','Throne of Eldraine','Land - Swamp','common',NULL,0,1),
(185461,'',0,0,0,0,0,0,0,'Mountain','Throne of Eldraine','Land - Mountain','common',NULL,0,1),
(185465,'',0,0,0,0,0,0,0,'Forest','Throne of Eldraine','Land - Forest','common',NULL,0,1),
(185532,'{4}{W}',5,1,0,0,0,0,0,'Kenrith, the Returned King','Throne of Eldraine','Creature - Human Noble','mythic',NULL,0,1),
(185533,'{3}{R}{R}',5,0,1,0,0,0,0,'Rowan, Fearless Sparkmage','Throne of Eldraine','Planeswalker - Rowan','mythic',NULL,0,1),
(185534,'{2}{W}',3,1,0,0,0,0,0,'Garrison Griffin','Throne of Eldraine','Creature - Griffin','common',NULL,0,1),
(185535,'{3}{R}',4,0,1,0,0,0,0,'Rowan\'s Battleguard','Throne of Eldraine','Creature - Human Knight','uncommon',NULL,0,1),
(185536,'{4}{R}',5,0,1,0,0,0,0,'Rowan\'s Stalwarts','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185537,'',0,0,0,0,0,0,0,'Wind-Scarred Crag','Throne of Eldraine','Land','common',NULL,0,1),
(185538,'{4}{G}{U}',6,0,0,1,1,0,0,'Oko, the Trickster','Throne of Eldraine','Planeswalker - Oko','mythic',NULL,0,1),
(185539,'{2}{U}',3,0,0,0,1,0,0,'Oko\'s Accomplices','Throne of Eldraine','Creature - Faerie','common',NULL,0,1),
(185540,'{1}{G}',2,0,0,1,0,0,0,'Bramblefort Fink','Throne of Eldraine','Creature - Ouphe','uncommon',NULL,0,1),
(185541,'{3}{G}{U}',5,0,0,1,1,0,0,'Oko\'s Hospitality','Throne of Eldraine','Instant','rare',NULL,0,1),
(185542,'',0,0,0,0,0,0,0,'Thornwood Falls','Throne of Eldraine','Land','common',NULL,0,1),
(185543,'{2}{W}',3,1,0,0,0,0,0,'Mace of the Valiant','Throne of Eldraine','Artifact - Equipment','rare',NULL,0,1),
(185544,'{5}{W}',6,1,0,0,0,0,0,'Silverwing Squadron','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185545,'{4}{U}',5,0,0,0,1,0,0,'Faerie Formation','Throne of Eldraine','Creature - Faerie','rare',NULL,0,1),
(185546,'{4}{U}{U}',6,0,0,0,1,0,0,'Shimmer Dragon','Throne of Eldraine','Creature - Dragon','rare',NULL,0,1),
(185547,'{6}{U}',7,0,0,0,1,0,0,'Workshop Elders','Throne of Eldraine','Creature - Human Artificer','rare',NULL,0,1),
(185548,'{3}{B}',4,0,0,0,0,1,0,'Chittering Witch','Throne of Eldraine','Creature - Human Warlock','rare',NULL,0,1),
(185549,'{4}{B}{B}',6,0,0,0,0,1,0,'Taste of Death','Throne of Eldraine','Sorcery','rare',NULL,0,1),
(185550,'{3}{R}',4,0,1,0,0,0,0,'Embereth Skyblazer','Throne of Eldraine','Creature - Human Knight','rare',NULL,0,1),
(185551,'{X}{G}{G}',2,0,0,1,0,0,0,'Steelbane Hydra','Throne of Eldraine','Creature - Turtle Hydra','rare',NULL,0,1),
(185552,'{5}{G}{G}',7,0,0,1,0,0,0,'Thorn Mammoth','Throne of Eldraine','Creature - Elephant','rare',NULL,0,1),
(185553,'{1}{W}{U}{B}',4,1,0,0,1,1,0,'Alela, Artful Provocateur','Throne of Eldraine','Creature - Faerie Warlock','mythic',NULL,0,1),
(185554,'{4}{W}{U}',6,1,0,0,1,0,0,'Banish into Fable','Throne of Eldraine','Instant','rare',NULL,0,1),
(185555,'{2}{G}{W}{U}',5,1,0,1,1,0,0,'Chulane, Teller of Tales','Throne of Eldraine','Creature - Human Druid','mythic',NULL,0,1),
(185556,'{2}{B}{G}',4,0,0,1,0,1,0,'Gluttonous Troll','Throne of Eldraine','Creature - Troll','rare',NULL,0,1),
(185557,'{1}{W}{B}',3,1,0,0,0,1,0,'Knights\' Charge','Throne of Eldraine','Enchantment','rare',NULL,0,1),
(185558,'{2}{B}{R}{G}',5,0,1,1,0,1,0,'Korvold, Fae-Cursed King','Throne of Eldraine','Creature - Dragon Noble','mythic',NULL,0,1),
(185559,'{3}{R}{W}{B}',6,1,1,0,0,1,0,'Syr Gwyn, Hero of Ashvale','Throne of Eldraine','Creature - Human Knight','mythic',NULL,0,1),
(185560,'{2}',2,0,0,0,0,0,0,'Arcane Signet','Throne of Eldraine','Artifact','common',NULL,0,1),
(185561,'{2}',2,0,0,0,0,0,0,'Tome of Legends','Throne of Eldraine','Artifact','rare',NULL,0,1),
(185562,'',0,0,0,0,0,0,0,'Command Tower','Throne of Eldraine','Land','common',NULL,0,1),
(201911,'{2}',2,0,0,0,0,0,0,'Case of the Shattered Pact','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(201912,'{4}{W}',5,1,0,0,0,0,0,'Absolving Lammasu','Murders at Karlov Manor','Creature - Lammasu','uncommon',NULL,0,1),
(201913,'{1}{W}',2,1,0,0,0,0,0,'Assemble the Players','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(201914,'{2}{W}{W}',4,1,0,0,0,0,0,'Aurelia\'s Vindicator','Murders at Karlov Manor','Creature - Angel','mythic',NULL,0,1),
(201915,'{1}{W}',2,1,0,0,0,0,0,'Auspicious Arrival','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201916,'{1}{W}',2,1,0,0,0,0,0,'Call a Surprise Witness','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(201917,'{2}{W}',3,1,0,0,0,0,0,'Case File Auditor','Murders at Karlov Manor','Creature - Human Detective','uncommon',NULL,0,1),
(201919,'{1}{W}',2,1,0,0,0,0,0,'Case of the Gateway Express','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(201920,'{1}{W}',2,1,0,0,0,0,0,'Case of the Pilfered Proof','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(201921,'{W}',1,1,0,0,0,0,0,'Case of the Uneaten Feast','Murders at Karlov Manor','Enchantment - Case','rare',NULL,0,1),
(201922,'{4}{W}{W}',6,1,0,0,0,0,0,'Defenestrated Phantom','Murders at Karlov Manor','Creature - Spirit','common',NULL,0,1),
(201923,'{2}{W}',3,1,0,0,0,0,0,'Delney, Streetwise Lookout','Murders at Karlov Manor','Creature - Human Scout','mythic',NULL,0,1),
(201924,'{1}{W}',2,1,0,0,0,0,0,'Doorkeeper Thrull','Murders at Karlov Manor','Creature - Thrull','rare',NULL,0,1),
(201925,'{2}{W}',3,1,0,0,0,0,0,'Due Diligence','Murders at Karlov Manor','Enchantment - Aura','common',NULL,0,1),
(201926,'{3}{W}{W}',5,1,0,0,0,0,0,'Essence of Antiquity','Murders at Karlov Manor','Artifact Creature - Golem','uncommon',NULL,0,1),
(201927,'{W}',1,1,0,0,0,0,0,'Forum Familiar','Murders at Karlov Manor','Creature - Cat','uncommon',NULL,0,1),
(201928,'{3}{W}',4,1,0,0,0,0,0,'Griffnaut Tracker','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(201929,'{4}{W}',5,1,0,0,0,0,0,'Haazda Vigilante','Murders at Karlov Manor','Creature - Giant Soldier','common',NULL,0,1),
(201930,'{2}{W}',3,1,0,0,0,0,0,'Inside Source','Murders at Karlov Manor','Creature - Human Citizen','common',NULL,0,1),
(201931,'{3}{W}',4,1,0,0,0,0,0,'Karlov Watchdog','Murders at Karlov Manor','Creature - Dog','uncommon',NULL,0,1),
(201932,'{W}',1,1,0,0,0,0,0,'Krovod Haunch','Murders at Karlov Manor','Artifact - Food Equipment','uncommon',NULL,0,1),
(201933,'{2}{W}',3,1,0,0,0,0,0,'Make Your Move','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201934,'{2}{W}',3,1,0,0,0,0,0,'Makeshift Binding','Murders at Karlov Manor','Enchantment','common',NULL,0,1),
(201935,'{1}{W}',2,1,0,0,0,0,0,'Marketwatch Phantom','Murders at Karlov Manor','Creature - Spirit Detective','common',NULL,0,1),
(201936,'{3}{W}',4,1,0,0,0,0,0,'Museum Nightwatch','Murders at Karlov Manor','Creature - Centaur Soldier','common',NULL,0,1),
(201937,'{1}{W}',2,1,0,0,0,0,0,'Neighborhood Guardian','Murders at Karlov Manor','Creature - Unicorn','uncommon',NULL,0,1),
(201938,'{2}{W}{W}',4,1,0,0,0,0,0,'No Witnesses','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(201939,'{1}{W}',2,1,0,0,0,0,0,'Not on My Watch','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(201940,'{W}',1,1,0,0,0,0,0,'Novice Inspector','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(201941,'{2}{W}{W}',4,1,0,0,0,0,0,'On the Job','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201942,'{1}{W}',2,1,0,0,0,0,0,'Perimeter Enforcer','Murders at Karlov Manor','Creature - Human Detective','uncommon',NULL,0,1),
(201943,'{1}{W}',2,1,0,0,0,0,0,'Sanctuary Wall','Murders at Karlov Manor','Artifact Creature - Wall','uncommon',NULL,0,1),
(201944,'{1}{W}',2,1,0,0,0,0,0,'Seasoned Consultant','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(201945,'{1}{W}',2,1,0,0,0,0,0,'Tenth District Hero','Murders at Karlov Manor','Creature - Human','rare',NULL,0,1),
(201946,'{1}{W}',2,1,0,0,0,0,0,'Unyielding Gatekeeper','Murders at Karlov Manor','Creature - Elephant Cleric','rare',NULL,0,1),
(201947,'{2}{W}',3,1,0,0,0,0,0,'Wojek Investigator','Murders at Karlov Manor','Creature - Angel Detective','rare',NULL,0,1),
(201949,'{W}',1,1,0,0,0,0,0,'Wrench','Murders at Karlov Manor','Artifact - Clue Equipment','uncommon',NULL,0,1),
(201950,'{4}{U}{U}',6,0,0,0,1,0,0,'Agency Outfitter','Murders at Karlov Manor','Creature - Sphinx Detective','uncommon',NULL,0,1),
(201951,'{U}',1,0,0,0,1,0,0,'Behind the Mask','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201952,'{4}{U}',5,0,0,0,1,0,0,'Benthic Criminologists','Murders at Karlov Manor','Creature - Merfolk Wizard','common',NULL,0,1),
(201953,'{1}{U}',2,0,0,0,1,0,0,'Bubble Smuggler','Murders at Karlov Manor','Creature - Octopus Fish','common',NULL,0,1),
(201954,'{1}{U}',2,0,0,0,1,0,0,'Burden of Proof','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(201955,'{U}',1,0,0,0,1,0,0,'Candlestick','Murders at Karlov Manor','Artifact - Clue Equipment','uncommon',NULL,0,1),
(201956,'{U}',1,0,0,0,1,0,0,'Case of the Filched Falcon','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(201957,'{2}{U}',3,0,0,0,1,0,0,'Case of the Ransacked Lab','Murders at Karlov Manor','Enchantment - Case','rare',NULL,0,1),
(201958,'{3}{U}',4,0,0,0,1,0,0,'Cold Case Cracker','Murders at Karlov Manor','Creature - Spirit Detective','common',NULL,0,1),
(201959,'{5}{U}{U}',7,0,0,0,1,0,0,'Conspiracy Unraveler','Murders at Karlov Manor','Creature - Sphinx Detective','mythic',NULL,0,1),
(201960,'{1}{U}{U}',3,0,0,0,1,0,0,'Coveted Falcon','Murders at Karlov Manor','Artifact Creature - Bird','rare',NULL,0,1),
(201961,'{2}{U}',3,0,0,0,1,0,0,'Crimestopper Sprite','Murders at Karlov Manor','Creature - Faerie Detective','common',NULL,0,1),
(201962,'{2}{U}',3,0,0,0,1,0,0,'Cryptic Coat','Murders at Karlov Manor','Artifact - Equipment','rare',NULL,0,1),
(201963,'{U}',1,0,0,0,1,0,0,'Curious Inquiry','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(201964,'{1}{U}',2,0,0,0,1,0,0,'Deduce','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201965,'{2}{U}',3,0,0,0,1,0,0,'Dramatic Accusation','Murders at Karlov Manor','Enchantment - Aura','common',NULL,0,1),
(201966,'{1}{U}',2,0,0,0,1,0,0,'Eliminate the Impossible','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(201967,'{1}{U}',2,0,0,0,1,0,0,'Exit Specialist','Murders at Karlov Manor','Creature - Human Detective','uncommon',NULL,0,1),
(201968,'{1}{U}',2,0,0,0,1,0,0,'Fae Flight','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(201969,'{2}{U}',3,0,0,0,1,0,0,'Forensic Gadgeteer','Murders at Karlov Manor','Creature - Vedalken Artificer Detective','rare',NULL,0,1),
(201970,'{2}{U}',3,0,0,0,1,0,0,'Forensic Researcher','Murders at Karlov Manor','Creature - Merfolk Detective','uncommon',NULL,0,1),
(201971,'{2}{U}',3,0,0,0,1,0,0,'Furtive Courier','Murders at Karlov Manor','Creature - Merfolk Advisor','uncommon',NULL,0,1),
(201972,'{5}{U}',6,0,0,0,1,0,0,'Hotshot Investigators','Murders at Karlov Manor','Creature - Vedalken Detective','common',NULL,0,1),
(201973,'{3}{U}{U}',5,0,0,0,1,0,0,'Intrude on the Mind','Murders at Karlov Manor','Instant','mythic',NULL,0,1),
(201974,'{1}{U}',2,0,0,0,1,0,0,'Jaded Analyst','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(201975,'{4}{U}',5,0,0,0,1,0,0,'Living Conundrum','Murders at Karlov Manor','Creature - Elemental','uncommon',NULL,0,1),
(201976,'{X}{U}{U}',2,0,0,0,1,0,0,'Lost in the Maze','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(201977,'{U}',1,0,0,0,1,0,0,'Mistway Spy','Murders at Karlov Manor','Creature - Merfolk Detective','uncommon',NULL,0,1),
(201978,'{3}{U}',4,0,0,0,1,0,0,'Out Cold','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201979,'{1}{U}',2,0,0,0,1,0,0,'Proft\'s Eidetic Memory','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(201980,'{2}{U}',3,0,0,0,1,0,0,'Projektor Inspector','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(201981,'{1}{U}',2,0,0,0,1,0,0,'Reasonable Doubt','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201982,'{1}{U}{U}{U}',4,0,0,0,1,0,0,'Reenact the Crime','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(201983,'{2}{U}',3,0,0,0,1,0,0,'Steamcore Scholar','Murders at Karlov Manor','Creature - Weird Detective','rare',NULL,0,1),
(201984,'{2}{U}{U}',4,0,0,0,1,0,0,'Sudden Setback','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(201986,'{3}{U}',4,0,0,0,1,0,0,'Surveillance Monitor','Murders at Karlov Manor','Creature - Vedalken Detective','uncommon',NULL,0,1),
(201988,'{1}{U}',2,0,0,0,1,0,0,'Unauthorized Exit','Murders at Karlov Manor','Instant','common',NULL,0,1),
(201989,'{4}{B}',5,0,0,0,0,1,0,'Agency Coroner','Murders at Karlov Manor','Creature - Ogre Cleric','common',NULL,0,1),
(201990,'{2}{B}',3,0,0,0,0,1,0,'Alley Assailant','Murders at Karlov Manor','Creature - Vampire Rogue','common',NULL,0,1),
(201991,'{3}{B}',4,0,0,0,0,1,0,'Barbed Servitor','Murders at Karlov Manor','Artifact Creature - Construct','rare',NULL,0,1),
(201992,'{5}{B}',6,0,0,0,0,1,0,'Basilica Stalker','Murders at Karlov Manor','Creature - Vampire Detective','common',NULL,0,1),
(201993,'{B}',1,0,0,0,0,1,0,'Case of the Gorgon\'s Kiss','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(201995,'{1}{B}',2,0,0,0,0,1,0,'Case of the Stashed Skeleton','Murders at Karlov Manor','Enchantment - Case','rare',NULL,0,1),
(201996,'{2}{B}',3,0,0,0,0,1,0,'Cerebral Confiscation','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(201997,'{2}{B}',3,0,0,0,0,1,0,'Clandestine Meddler','Murders at Karlov Manor','Creature - Vampire Rogue','uncommon',NULL,0,1),
(201999,'{3}{B}{B}',5,0,0,0,0,1,0,'Deadly Cover-Up','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202000,'{1}{B}',2,0,0,0,0,1,0,'Extract a Confession','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202001,'{B}',1,0,0,0,0,1,0,'Festerleech','Murders at Karlov Manor','Creature - Zombie Leech','uncommon',NULL,0,1),
(202002,'{1}{B}',2,0,0,0,0,1,0,'Homicide Investigator','Murders at Karlov Manor','Creature - Human Detective','rare',NULL,0,1),
(202003,'{2}{B}',3,0,0,0,0,1,0,'Hunted Bonebrute','Murders at Karlov Manor','Creature - Skeleton Beast','rare',NULL,0,1),
(202004,'{3}{B}',4,0,0,0,0,1,0,'Illicit Masquerade','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(202005,'{3}{B}{B}',5,0,0,0,0,1,0,'It Doesn\'t Add Up','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202006,'{B}',1,0,0,0,0,1,0,'Lead Pipe','Murders at Karlov Manor','Artifact - Clue Equipment','uncommon',NULL,0,1),
(202007,'{1}{B}',2,0,0,0,0,1,0,'Leering Onlooker','Murders at Karlov Manor','Creature - Vampire','uncommon',NULL,0,1),
(202008,'{1}{B}',2,0,0,0,0,1,0,'Long Goodbye','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202009,'{3}{B}',4,0,0,0,0,1,0,'Macabre Reconstruction','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202010,'{2}{B}{B}',4,0,0,0,0,1,0,'Massacre Girl, Known Killer','Murders at Karlov Manor','Creature - Human Assassin','mythic',NULL,0,1),
(202011,'{1}{B}{B}',3,0,0,0,0,1,0,'Murder','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202012,'{3}{B}',4,0,0,0,0,1,0,'Nightdrinker Moroii','Murders at Karlov Manor','Creature - Vampire','uncommon',NULL,0,1),
(202013,'{X}{B}{B}',2,0,0,0,0,1,0,'Outrageous Robbery','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202014,'{4}{B}{B}',6,0,0,0,0,1,0,'Persuasive Interrogators','Murders at Karlov Manor','Creature - Gorgon Detective','uncommon',NULL,0,1),
(202015,'{4}{B}',5,0,0,0,0,1,0,'Polygraph Orb','Murders at Karlov Manor','Artifact','uncommon',NULL,0,1),
(202016,'{1}{B}',2,0,0,0,0,1,0,'Presumed Dead','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202017,'{1}{B}',2,0,0,0,0,1,0,'Repeat Offender','Murders at Karlov Manor','Creature - Human Assassin','common',NULL,0,1),
(202018,'{3}{B}',4,0,0,0,0,1,0,'Rot Farm Mortipede','Murders at Karlov Manor','Creature - Insect','common',NULL,0,1),
(202019,'{X}{B}',1,0,0,0,0,1,0,'Slice from the Shadows','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202020,'{3}{B}',4,0,0,0,0,1,0,'Slimy Dualleech','Murders at Karlov Manor','Creature - Leech','uncommon',NULL,0,1),
(202021,'{B}',1,0,0,0,0,1,0,'Snarling Gorehound','Murders at Karlov Manor','Creature - Dog','common',NULL,0,1),
(202022,'{3}{B}',4,0,0,0,0,1,0,'Soul Enervation','Murders at Karlov Manor','Enchantment','uncommon',NULL,0,1),
(202023,'{B}',1,0,0,0,0,1,0,'Toxin Analysis','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202024,'{3}{B}{B}',5,0,0,0,0,1,0,'Undercity Eliminator','Murders at Karlov Manor','Creature - Gorgon Assassin','uncommon',NULL,0,1),
(202025,'{1}{B}',2,0,0,0,0,1,0,'Unscrupulous Agent','Murders at Karlov Manor','Creature - Elf Detective','common',NULL,0,1),
(202026,'{3}{B}{B}{B}',6,0,0,0,0,1,0,'Vein Ripper','Murders at Karlov Manor','Creature - Vampire Assassin','mythic',NULL,0,1),
(202027,'{3}{R}{R}',5,0,1,0,0,0,0,'Anzrag\'s Rampage','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202028,'{4}{R}{R}',6,0,1,0,0,0,0,'Bolrac-Clan Basher','Murders at Karlov Manor','Creature - Cyclops Warrior','uncommon',NULL,0,1),
(202029,'{1}{R}{R}',3,0,1,0,0,0,0,'Case of the Burning Masks','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(202030,'{2}{R}',3,0,1,0,0,0,0,'Case of the Crimson Pulse','Murders at Karlov Manor','Enchantment - Case','rare',NULL,0,1),
(202031,'{4}{R}',5,0,1,0,0,0,0,'Caught Red-Handed','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202032,'{2}{R}',3,0,1,0,0,0,0,'The Chase Is On','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202033,'{1}{R}',2,0,1,0,0,0,0,'Concealed Weapon','Murders at Karlov Manor','Artifact - Equipment','uncommon',NULL,0,1),
(202034,'{1}{R}',2,0,1,0,0,0,0,'Connecting the Dots','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(202035,'{R}',1,0,1,0,0,0,0,'Convenient Target','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(202036,'{4}{R}',5,0,1,0,0,0,0,'Cornered Crook','Murders at Karlov Manor','Creature - Lizard Warrior','uncommon',NULL,0,1),
(202037,'{2}{R}',3,0,1,0,0,0,0,'Crime Novelist','Murders at Karlov Manor','Creature - Goblin Bard','uncommon',NULL,0,1),
(202038,'{1}{R}',2,0,1,0,0,0,0,'Demand Answers','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202039,'{R}{R}',2,0,1,0,0,0,0,'Expedited Inheritance','Murders at Karlov Manor','Enchantment','mythic',NULL,0,1),
(202040,'{1}{R}',2,0,1,0,0,0,0,'Expose the Culprit','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202041,'{R}',1,0,1,0,0,0,0,'Felonious Rage','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202042,'{R}',1,0,1,0,0,0,0,'Frantic Scapegoat','Murders at Karlov Manor','Creature - Goat','uncommon',NULL,0,1),
(202043,'{1}{R}',2,0,1,0,0,0,0,'Fugitive Codebreaker','Murders at Karlov Manor','Creature - Goblin Rogue','rare',NULL,0,1),
(202044,'{1}{R}',2,0,1,0,0,0,0,'Galvanize','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202045,'{2}{R}',3,0,1,0,0,0,0,'Gearbane Orangutan','Murders at Karlov Manor','Creature - Ape','common',NULL,0,1),
(202046,'{R}',1,0,1,0,0,0,0,'Goblin Maskmaker','Murders at Karlov Manor','Creature - Goblin Citizen','common',NULL,0,1),
(202047,'{3}{R}',4,0,1,0,0,0,0,'Harried Dronesmith','Murders at Karlov Manor','Creature - Human Artificer','uncommon',NULL,0,1),
(202048,'{4}{R}{R}',6,0,1,0,0,0,0,'Incinerator of the Guilty','Murders at Karlov Manor','Creature - Dragon','mythic',NULL,0,1),
(202049,'{1}{R}',2,0,1,0,0,0,0,'Innocent Bystander','Murders at Karlov Manor','Creature - Goblin Citizen','common',NULL,0,1),
(202050,'{R}',1,0,1,0,0,0,0,'Knife','Murders at Karlov Manor','Artifact - Clue Equipment','uncommon',NULL,0,1),
(202051,'{2}{R}',3,0,1,0,0,0,0,'Krenko, Baron of Tin Street','Murders at Karlov Manor','Creature - Goblin','rare',NULL,0,1),
(202052,'{2}{R}{R}',4,0,1,0,0,0,0,'Krenko\'s Buzzcrusher','Murders at Karlov Manor','Artifact Creature - Insect Thopter','rare',NULL,0,1),
(202053,'{1}{R}{R}',3,0,1,0,0,0,0,'Lamplight Phoenix','Murders at Karlov Manor','Creature - Phoenix','rare',NULL,0,1),
(202054,'{4}{R}',5,0,1,0,0,0,0,'Offender at Large','Murders at Karlov Manor','Creature - Giant Rogue','common',NULL,0,1),
(202055,'{3}{R}',4,0,1,0,0,0,0,'Person of Interest','Murders at Karlov Manor','Creature - Human Rogue','common',NULL,0,1),
(202056,'{1}{R}',2,0,1,0,0,0,0,'Pyrotechnic Performer','Murders at Karlov Manor','Creature - Lizard Assassin','rare',NULL,0,1),
(202057,'{1}{R}',2,0,1,0,0,0,0,'Reckless Detective','Murders at Karlov Manor','Creature - Devil Detective','uncommon',NULL,0,1),
(202058,'{1}{R}',2,0,1,0,0,0,0,'Red Herring','Murders at Karlov Manor','Artifact Creature - Clue Fish','common',NULL,0,1),
(202059,'{4}{R}',5,0,1,0,0,0,0,'Rubblebelt Braggart','Murders at Karlov Manor','Creature - Lizard Warrior','common',NULL,0,1),
(202060,'{R}',1,0,1,0,0,0,0,'Shock','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202061,'{4}{R}',5,0,1,0,0,0,0,'Suspicious Detonation','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202062,'{X}{R}',1,0,1,0,0,0,0,'Torch the Witness','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202064,'{1}{R}',2,0,1,0,0,0,0,'Vengeful Tracker','Murders at Karlov Manor','Creature - Human Detective','uncommon',NULL,0,1),
(202065,'{1}{G}',2,0,0,1,0,0,0,'Aftermath Analyst','Murders at Karlov Manor','Creature - Elf Detective','uncommon',NULL,0,1),
(202066,'{2}{G}',3,0,0,1,0,0,0,'Airtight Alibi','Murders at Karlov Manor','Enchantment - Aura','common',NULL,0,1),
(202067,'{G}',1,0,0,1,0,0,0,'Analyze the Pollen','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202068,'{G}{G}{G}',3,0,0,1,0,0,0,'Archdruid\'s Charm','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202069,'{2}{G}',3,0,0,1,0,0,0,'Audience with Trostani','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202070,'{2}{G}{G}',4,0,0,1,0,0,0,'Axebane Ferox','Murders at Karlov Manor','Creature - Beast','rare',NULL,0,1),
(202071,'{3}{G}',4,0,0,1,0,0,0,'Bite Down on Crime','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202072,'{3}{G}',4,0,0,1,0,0,0,'Case of the Locked Hothouse','Murders at Karlov Manor','Enchantment - Case','rare',NULL,0,1),
(202073,'{2}{G}',3,0,0,1,0,0,0,'Case of the Trampled Garden','Murders at Karlov Manor','Enchantment - Case','uncommon',NULL,0,1),
(202074,'{3}{G}',4,0,0,1,0,0,0,'Chalk Outline','Murders at Karlov Manor','Enchantment','uncommon',NULL,0,1),
(202075,'{3}{G}{G}',5,0,0,1,0,0,0,'Culvert Ambusher','Murders at Karlov Manor','Creature - Wurm Horror','uncommon',NULL,0,1),
(202076,'{1}{G}',2,0,0,1,0,0,0,'Fanatical Strength','Murders at Karlov Manor','Instant','common',NULL,0,1),
(202077,'{1}{G}',2,0,0,1,0,0,0,'Flourishing Bloom-Kin','Murders at Karlov Manor','Creature - Plant Elemental','uncommon',NULL,0,1),
(202078,'{G}',1,0,0,1,0,0,0,'Get a Leg Up','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202079,'{5}{G}{G}',7,0,0,1,0,0,0,'Glint Weaver','Murders at Karlov Manor','Creature - Spider','uncommon',NULL,0,1),
(202080,'{3}{G}',4,0,0,1,0,0,0,'Greenbelt Radical','Murders at Karlov Manor','Creature - Centaur Citizen','uncommon',NULL,0,1),
(202081,'{G}',1,0,0,1,0,0,0,'Hard-Hitting Question','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202082,'{G}',1,0,0,1,0,0,0,'Hedge Whisperer','Murders at Karlov Manor','Creature - Elf Druid Detective','uncommon',NULL,0,1),
(202083,'{3}{G}',4,0,0,1,0,0,0,'Hide in Plain Sight','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202084,'{4}{G}',5,0,0,1,0,0,0,'A Killer Among Us','Murders at Karlov Manor','Enchantment','uncommon',NULL,0,1),
(202085,'{3}{G}',4,0,0,1,0,0,0,'Loxodon Eavesdropper','Murders at Karlov Manor','Creature - Elephant Detective','common',NULL,0,1),
(202086,'{1}{G}',2,0,0,1,0,0,0,'Nervous Gardener','Murders at Karlov Manor','Creature - Dryad','common',NULL,0,1),
(202087,'{G}',1,0,0,1,0,0,0,'Pick Your Poison','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202088,'{2}{G}',3,0,0,1,0,0,0,'Pompous Gadabout','Murders at Karlov Manor','Creature - Human Citizen','uncommon',NULL,0,1),
(202089,'{10}{G}',11,0,0,1,0,0,0,'The Pride of Hull Clade','Murders at Karlov Manor','Creature - Crocodile Elk Turtle','mythic',NULL,0,1),
(202090,'{G}',1,0,0,1,0,0,0,'Rope','Murders at Karlov Manor','Artifact - Clue Equipment','uncommon',NULL,0,1),
(202091,'{G}',1,0,0,1,0,0,0,'Rubblebelt Maverick','Murders at Karlov Manor','Creature - Human Detective','common',NULL,0,1),
(202092,'{2}{G}',3,0,0,1,0,0,0,'Sample Collector','Murders at Karlov Manor','Creature - Troll Detective','uncommon',NULL,0,1),
(202093,'{1}{G}',2,0,0,1,0,0,0,'Sharp-Eyed Rookie','Murders at Karlov Manor','Creature - Human Detective','rare',NULL,0,1),
(202094,'{2}{G}',3,0,0,1,0,0,0,'Slime Against Humanity','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202095,'{2}{G}',3,0,0,1,0,0,0,'They Went This Way','Murders at Karlov Manor','Sorcery','common',NULL,0,1),
(202096,'{4}{G}{G}',6,0,0,1,0,0,0,'Topiary Panther','Murders at Karlov Manor','Creature - Plant Cat','common',NULL,0,1),
(202097,'{1}{G}',2,0,0,1,0,0,0,'Tunnel Tipster','Murders at Karlov Manor','Creature - Mole Scout','common',NULL,0,1),
(202098,'{1}{G}{G}',3,0,0,1,0,0,0,'Undergrowth Recon','Murders at Karlov Manor','Enchantment','mythic',NULL,0,1),
(202099,'{4}{G}',5,0,0,1,0,0,0,'Vengeful Creeper','Murders at Karlov Manor','Creature - Plant Elemental','common',NULL,0,1),
(202100,'{1}{G}',2,0,0,1,0,0,0,'Vitu-Ghazi Inspector','Murders at Karlov Manor','Creature - Elf Detective','common',NULL,0,1),
(202101,'{2}{R}{W}',4,1,1,0,0,0,0,'Agrus Kos, Spirit of Justice','Murders at Karlov Manor','Creature - Spirit Detective','mythic',NULL,0,1),
(202102,'{1}{W}{U}',3,1,0,0,1,0,0,'Alquist Proft, Master Sleuth','Murders at Karlov Manor','Creature - Human Detective','mythic',NULL,0,1),
(202103,'{2}{R}{G}',4,0,1,1,0,0,0,'Anzrag, the Quake-Mole','Murders at Karlov Manor','Creature - Mole God','mythic',NULL,0,1),
(202104,'{B}{G}',2,0,0,1,0,1,0,'Assassin\'s Trophy','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202105,'{3}{R}{W}',5,1,1,0,0,0,0,'Aurelia, the Law Above','Murders at Karlov Manor','Creature - Angel','rare',NULL,0,1),
(202106,'{B}{R}',2,0,1,0,0,1,0,'Blood Spatter Analysis','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(202107,'{R}{G}',2,0,1,1,0,0,0,'Break Out','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202109,'{2}{G}{W}',4,1,0,1,0,0,0,'Buried in the Garden','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(202110,'{3}{U}{B}',5,0,0,0,1,1,0,'Coerced to Kill','Murders at Karlov Manor','Enchantment - Aura','uncommon',NULL,0,1),
(202111,'{3}{G}{W}',5,1,0,1,0,0,0,'Crowd-Control Warden','Murders at Karlov Manor','Creature - Centaur Soldier','common',NULL,0,1),
(202112,'{2}{U}{B}',4,0,0,0,1,1,0,'Curious Cadaver','Murders at Karlov Manor','Creature - Zombie Detective','uncommon',NULL,0,1),
(202113,'{1}{B}{R}',3,0,1,0,0,1,0,'Deadly Complication','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202114,'{2}{U}{R}',4,0,1,0,1,0,0,'Detective\'s Satchel','Murders at Karlov Manor','Artifact','uncommon',NULL,0,1),
(202115,'{R}{W}',2,1,1,0,0,0,0,'Dog Walker','Murders at Karlov Manor','Creature - Human Citizen','common',NULL,0,1),
(202116,'{X}{X}{X}{G}{U}',2,0,0,1,1,0,0,'Doppelgang','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202117,'{U}{B}',2,0,0,0,1,1,0,'Drag the Canal','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202118,'{1}{U}{B}',3,0,0,0,1,1,0,'Etrata, Deadly Fugitive','Murders at Karlov Manor','Creature - Vampire Assassin','mythic',NULL,0,1),
(202119,'{G}{U}',2,0,0,1,1,0,0,'Evidence Examiner','Murders at Karlov Manor','Creature - Merfolk Detective','uncommon',NULL,0,1),
(202120,'{1}{W}{W}{U}{U}',5,1,0,0,1,0,0,'Ezrim, Agency Chief','Murders at Karlov Manor','Creature - Archon Detective','rare',NULL,0,1),
(202121,'{1}{U}{B}',3,0,0,0,1,1,0,'Faerie Snoop','Murders at Karlov Manor','Creature - Faerie Detective','common',NULL,0,1),
(202122,'{2}{U}{R}',4,0,1,0,1,0,0,'Gadget Technician','Murders at Karlov Manor','Creature - Goblin Artificer','common',NULL,0,1),
(202123,'{U}{R}',2,0,1,0,1,0,0,'Gleaming Geardrake','Murders at Karlov Manor','Artifact Creature - Drake','uncommon',NULL,0,1),
(202124,'{2}{W}{U}',4,1,0,0,1,0,0,'Granite Witness','Murders at Karlov Manor','Artifact Creature - Gargoyle Detective','common',NULL,0,1),
(202125,'{2}{U}{R}',4,0,1,0,1,0,0,'Ill-Timed Explosion','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202126,'{B}{G}',2,0,0,1,0,1,0,'Insidious Roots','Murders at Karlov Manor','Enchantment','uncommon',NULL,0,1),
(202127,'{4}{B}{G}',6,0,0,1,0,1,0,'Izoni, Center of the Web','Murders at Karlov Manor','Creature - Elf Detective','rare',NULL,0,1),
(202128,'{3}{B}{R}',5,0,1,0,0,1,0,'Judith, Carnage Connoisseur','Murders at Karlov Manor','Creature - Human Shaman','rare',NULL,0,1),
(202129,'{2}{W}{B}',4,1,0,0,0,1,0,'Kaya, Spirits\' Justice','Murders at Karlov Manor','Planeswalker - Kaya','mythic',NULL,0,1),
(202130,'{G}{U}',4,0,0,1,1,0,0,'Kellan, Inquisitive Prodigy/Tail the Suspect','Murders at Karlov Manor','Sorcery - Adventure','rare',NULL,0,1),
(202132,'{B}{G}',2,0,0,1,0,1,0,'Kraul Whipcracker','Murders at Karlov Manor','Creature - Insect Assassin','uncommon',NULL,0,1),
(202134,'{5}{U}{R}',7,0,1,0,1,0,0,'Kylox, Visionary Inventor','Murders at Karlov Manor','Creature - Lizard Artificer','rare',NULL,0,1),
(202135,'{1}{U}{R}',3,0,1,0,1,0,0,'Kylox\'s Voltstrider','Murders at Karlov Manor','Artifact - Vehicle','mythic',NULL,0,1),
(202136,'{U}{B}',2,0,0,0,1,1,0,'Lazav, Wearer of Faces','Murders at Karlov Manor','Creature - Shapeshifter Detective','rare',NULL,0,1),
(202137,'{G/W}{G/U}{B/G}{R/G}',4,1,1,1,1,1,0,'Leyline of the Guildpact','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(202138,'{R}{W}',2,1,1,0,0,0,0,'Lightning Helix','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202139,'{3}{R}{W}',5,1,1,0,0,0,0,'Meddling Youths','Murders at Karlov Manor','Creature - Human Detective','uncommon',NULL,0,1),
(202140,'{W}{U}{B}{R}{G}',5,1,1,1,1,1,0,'Niv-Mizzet, Guildpact','Murders at Karlov Manor','Creature - Dragon Avatar','rare',NULL,0,1),
(202141,'{W}{U}',2,1,0,0,1,0,0,'No More Lies','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202142,'{W}{U}',2,1,0,0,1,0,0,'Officious Interrogation','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202143,'{1}{W}{U}',3,1,0,0,1,0,0,'Private Eye','Murders at Karlov Manor','Creature - Homunculus Detective','uncommon',NULL,0,1),
(202144,'{4}{B}{R}',6,0,1,0,0,1,0,'Rakdos, Patron of Chaos','Murders at Karlov Manor','Creature - Demon','mythic',NULL,0,1),
(202145,'{2}{B}{G}',4,0,0,1,0,1,0,'Rakish Scoundrel','Murders at Karlov Manor','Creature - Elf Rogue','common',NULL,0,1),
(202146,'{5}{G}{W}',7,1,0,1,0,0,0,'Relive the Past','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202147,'{X}{G}{U}',2,0,0,1,1,0,0,'Repulsive Mutation','Murders at Karlov Manor','Instant','uncommon',NULL,0,1),
(202148,'{5}{R}{G}',7,0,1,1,0,0,0,'Riftburst Hellion','Murders at Karlov Manor','Creature - Hellion','common',NULL,0,1),
(202149,'{B}{R}',2,0,1,0,0,1,0,'Rune-Brand Juggler','Murders at Karlov Manor','Creature - Human Shaman','uncommon',NULL,0,1),
(202150,'{1}{W}{B}',3,1,0,0,0,1,0,'Sanguine Savior','Murders at Karlov Manor','Creature - Vampire Cleric','common',NULL,0,1),
(202151,'{3}{B}{R}',5,0,1,0,0,1,0,'Shady Informant','Murders at Karlov Manor','Creature - Ogre Rogue','common',NULL,0,1),
(202152,'{W}{B}',2,1,0,0,0,1,0,'Soul Search','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202153,'{G}{W}',2,1,0,1,0,0,0,'Sumala Sentry','Murders at Karlov Manor','Creature - Elf Archer','uncommon',NULL,0,1),
(202154,'{1}{W}{B}',3,1,0,0,0,1,0,'Teysa, Opulent Oligarch','Murders at Karlov Manor','Creature - Human Advisor','rare',NULL,0,1),
(202155,'{2}{R}{G}',4,0,1,1,0,0,0,'Tin Street Gossip','Murders at Karlov Manor','Creature - Lizard Advisor','uncommon',NULL,0,1),
(202156,'{2}{G}{W}{W}',5,1,0,1,0,0,0,'Tolsimir, Midnight\'s Light','Murders at Karlov Manor','Creature - Elf Scout','rare',NULL,0,1),
(202157,'{1}{W}{B}',3,1,0,0,0,1,0,'Treacherous Greed','Murders at Karlov Manor','Instant','rare',NULL,0,1),
(202158,'{G}{G/W}{W}',3,1,0,1,0,0,0,'Trostani, Three Whispers','Murders at Karlov Manor','Creature - Dryad','mythic',NULL,0,1),
(202159,'{4}{G}{U}',6,0,0,1,1,0,0,'Undercover Crocodelf','Murders at Karlov Manor','Creature - Elf Crocodile Detective','common',NULL,0,1),
(202161,'{2}{B}{G}',4,0,0,1,0,1,0,'Urgent Necropsy','Murders at Karlov Manor','Instant','mythic',NULL,0,1),
(202162,'{2}{G}{U}',4,0,0,1,1,0,0,'Vannifar, Evolved Enigma','Murders at Karlov Manor','Creature - Elf Ooze Wizard','mythic',NULL,0,1),
(202163,'{1}{R}{W}',3,1,1,0,0,0,0,'Warleader\'s Call','Murders at Karlov Manor','Enchantment','rare',NULL,0,1),
(202164,'{2}{W}{B}',4,1,0,0,0,1,0,'Wispdrinker Vampire','Murders at Karlov Manor','Creature - Vampire Rogue','uncommon',NULL,0,1),
(202165,'{X}{R}{G}',2,0,1,1,0,0,0,'Worldsoul\'s Rage','Murders at Karlov Manor','Sorcery','rare',NULL,0,1),
(202166,'{2}{R}{G}',4,0,1,1,0,0,0,'Yarus, Roar of the Old Gods','Murders at Karlov Manor','Creature - Centaur Druid','rare',NULL,0,1),
(202167,'{4}{G/W}{G/W}',8,1,0,1,0,0,0,'Cease/Desist','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202169,'{4}{U/B}{U/B}',8,0,0,0,1,1,0,'Flotsam/Jetsam','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202171,'{4}{W/U}{W/U}',9,1,0,0,1,0,0,'Fuss/Bother','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202173,'{4}{R/G}{R/G}',7,0,1,1,0,0,0,'Hustle/Bustle','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202175,'{4}{B/R}{B/R}',8,0,1,0,0,1,0,'Push/Pull','Murders at Karlov Manor','Sorcery','uncommon',NULL,0,1),
(202177,'{2}',2,0,0,0,0,0,0,'Cryptex','Murders at Karlov Manor','Artifact','rare',NULL,0,1),
(202178,'{2}',2,0,0,0,0,0,0,'Gravestone Strider','Murders at Karlov Manor','Artifact Creature - Golem','common',NULL,0,1),
(202179,'{5}',5,0,0,0,0,0,0,'Lumbering Laundry','Murders at Karlov Manor','Artifact Creature - Golem','uncommon',NULL,0,1),
(202180,'{5}',5,0,0,0,0,0,0,'Magnetic Snuffler','Murders at Karlov Manor','Artifact Creature - Construct','uncommon',NULL,0,1),
(202181,'{3}',3,0,0,0,0,0,0,'Magnifying Glass','Murders at Karlov Manor','Artifact','common',NULL,0,1),
(202182,'{2}',2,0,0,0,0,0,0,'Sanitation Automaton','Murders at Karlov Manor','Artifact Creature - Construct','common',NULL,0,1),
(202183,'{1}',1,0,0,0,0,0,0,'Thinking Cap','Murders at Karlov Manor','Artifact - Equipment','common',NULL,0,1),
(202184,'',0,0,0,0,0,0,0,'Branch of Vitu-Ghazi','Murders at Karlov Manor','Land','uncommon',NULL,0,1),
(202185,'',0,0,0,0,0,0,0,'Commercial District','Murders at Karlov Manor','Land - Mountain Forest','rare',NULL,0,1),
(202186,'',0,0,0,0,0,0,0,'Elegant Parlor','Murders at Karlov Manor','Land - Mountain Plains','rare',NULL,0,1),
(202187,'',0,0,0,0,0,0,0,'Escape Tunnel','Murders at Karlov Manor','Land','common',NULL,0,1),
(202188,'',0,0,0,0,0,0,0,'Hedge Maze','Murders at Karlov Manor','Land - Forest Island','rare',NULL,0,1),
(202189,'',0,0,0,0,0,0,0,'Lush Portico','Murders at Karlov Manor','Land - Forest Plains','rare',NULL,0,1),
(202190,'',0,0,0,0,0,0,0,'Meticulous Archive','Murders at Karlov Manor','Land - Plains Island','rare',NULL,0,1),
(202191,'',0,0,0,0,0,0,0,'Public Thoroughfare','Murders at Karlov Manor','Land','common',NULL,0,1),
(202192,'',0,0,0,0,0,0,0,'Raucous Theater','Murders at Karlov Manor','Land - Swamp Mountain','rare',NULL,0,1),
(202193,'',0,0,0,0,0,0,0,'Scene of the Crime','Murders at Karlov Manor','Artifact Land - Clue','uncommon',NULL,0,1),
(202194,'',0,0,0,0,0,0,0,'Shadowy Backstreet','Murders at Karlov Manor','Land - Plains Swamp','rare',NULL,0,1),
(202195,'',0,0,0,0,0,0,0,'Thundering Falls','Murders at Karlov Manor','Land - Island Mountain','rare',NULL,0,1),
(202196,'',0,0,0,0,0,0,0,'Undercity Sewers','Murders at Karlov Manor','Land - Island Swamp','rare',NULL,0,1),
(202197,'',0,0,0,0,0,0,0,'Underground Mortuary','Murders at Karlov Manor','Land - Swamp Forest','rare',NULL,0,1),
(202198,'',0,0,0,0,0,0,0,'Plains','Murders at Karlov Manor','Land - Plains','common',NULL,0,1),
(202199,'',0,0,0,0,0,0,0,'Island','Murders at Karlov Manor','Land - Island','common',NULL,0,1),
(202200,'',0,0,0,0,0,0,0,'Swamp','Murders at Karlov Manor','Land - Swamp','common',NULL,0,1),
(202201,'',0,0,0,0,0,0,0,'Mountain','Murders at Karlov Manor','Land - Mountain','common',NULL,0,1),
(202202,'',0,0,0,0,0,0,0,'Forest','Murders at Karlov Manor','Land - Forest','common',NULL,0,1),
(202364,'{3}{U}{R}',5,0,1,0,1,0,0,'Melek, Reforged Researcher','Murders at Karlov Manor','Creature - Weird Detective','mythic',NULL,0,1),
(202365,'{1}{W}{B}',3,1,0,0,0,1,0,'Tomik, Wielder of Law','Murders at Karlov Manor','Creature - Human Advisor','mythic',NULL,0,1),
(202366,'{2}{R}{G}{W}',5,1,1,1,0,0,0,'Voja, Jaws of the Conclave','Murders at Karlov Manor','Creature - Wolf','mythic',NULL,0,1),
(202368,'{2}{W}',3,1,0,0,0,0,0,'Acrobatic Maneuver','Kaladesh','Instant','common',NULL,0,1),
(202369,'{1}{W}{W}',3,1,0,0,0,0,0,'Aerial Responder','Kaladesh','Creature - Dwarf Soldier','uncommon',NULL,0,1),
(202370,'{2}{W}{W}',4,1,0,0,0,0,0,'Aetherstorm Roc','Kaladesh','Creature - Bird','rare',NULL,0,1),
(202371,'{3}{W}{W}',5,1,0,0,0,0,0,'Angel of Invention','Kaladesh','Creature - Angel','mythic',NULL,0,1),
(202372,'{W}',1,1,0,0,0,0,0,'Authority of the Consuls','Kaladesh','Enchantment','rare',NULL,0,1),
(202373,'{1}{W}',2,1,0,0,0,0,0,'Aviary Mechanic','Kaladesh','Creature - Dwarf Artificer','common',NULL,0,1),
(202374,'{W}',1,1,0,0,0,0,0,'Built to Last','Kaladesh','Instant','common',NULL,0,1),
(202375,'{3}{W}',4,1,0,0,0,0,0,'Captured by the Consulate','Kaladesh','Enchantment - Aura','rare',NULL,0,1),
(202376,'{3}{W}{W}',5,1,0,0,0,0,0,'Cataclysmic Gearhulk','Kaladesh','Artifact Creature - Construct','mythic',NULL,0,1),
(202377,'{3}{W}',4,1,0,0,0,0,0,'Consulate Surveillance','Kaladesh','Enchantment','uncommon',NULL,0,1),
(202378,'{3}{W}',4,1,0,0,0,0,0,'Consul\'s Shieldguard','Kaladesh','Creature - Dwarf Soldier','uncommon',NULL,0,1),
(202379,'{1}{W}',2,1,0,0,0,0,0,'Eddytrail Hawk','Kaladesh','Creature - Bird','common',NULL,0,1),
(202380,'{2}{W}',3,1,0,0,0,0,0,'Fairgrounds Warden','Kaladesh','Creature - Dwarf Soldier','uncommon',NULL,0,1),
(202381,'{W}',1,1,0,0,0,0,0,'Fragmentize','Kaladesh','Sorcery','common',NULL,0,1),
(202382,'{3}{W}{W}',5,1,0,0,0,0,0,'Fumigate','Kaladesh','Sorcery','rare',NULL,0,1),
(202383,'{1}{W}',2,1,0,0,0,0,0,'Gearshift Ace','Kaladesh','Creature - Dwarf Pilot','uncommon',NULL,0,1),
(202384,'{2}{W}',3,1,0,0,0,0,0,'Glint-Sleeve Artisan','Kaladesh','Creature - Dwarf Artificer','common',NULL,0,1),
(202385,'{2}{W}',3,1,0,0,0,0,0,'Herald of the Fair','Kaladesh','Creature - Human','common',NULL,0,1),
(202386,'{1}{W}',2,1,0,0,0,0,0,'Impeccable Timing','Kaladesh','Instant','common',NULL,0,1),
(202387,'{2}{W}{W}',4,1,0,0,0,0,0,'Inspired Charge','Kaladesh','Instant','common',NULL,0,1),
(202388,'{2}{W}',3,1,0,0,0,0,0,'Master Trinketeer','Kaladesh','Creature - Dwarf Artificer','rare',NULL,0,1),
(202389,'{1}{W}',2,1,0,0,0,0,0,'Ninth Bridge Patrol','Kaladesh','Creature - Dwarf Soldier','common',NULL,0,1),
(202390,'{1}{W}',2,1,0,0,0,0,0,'Pressure Point','Kaladesh','Instant','common',NULL,0,1),
(202391,'{3}{W}',4,1,0,0,0,0,0,'Propeller Pioneer','Kaladesh','Creature - Human Artificer','common',NULL,0,1),
(202392,'{3}{W}',4,1,0,0,0,0,0,'Refurbish','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202393,'{2}{W}',3,1,0,0,0,0,0,'Revoke Privileges','Kaladesh','Enchantment - Aura','common',NULL,0,1),
(202394,'{1}{W}',2,1,0,0,0,0,0,'Servo Exhibition','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202395,'{4}{W}',5,1,0,0,0,0,0,'Skyswirl Harrier','Kaladesh','Creature - Bird','common',NULL,0,1),
(202396,'{2}{W}',3,1,0,0,0,0,0,'Skywhaler\'s Shot','Kaladesh','Instant','uncommon',NULL,0,1),
(202397,'{W}',1,1,0,0,0,0,0,'Tasseled Dromedary','Kaladesh','Creature - Camel','common',NULL,0,1),
(202398,'{3}{W}',4,1,0,0,0,0,0,'Thriving Ibex','Kaladesh','Creature - Goat','common',NULL,0,1),
(202399,'{W}',1,1,0,0,0,0,0,'Toolcraft Exemplar','Kaladesh','Creature - Dwarf Artificer','rare',NULL,0,1),
(202400,'{1}{W}',2,1,0,0,0,0,0,'Trusty Companion','Kaladesh','Creature - Hyena','uncommon',NULL,0,1),
(202401,'{2}{W}{W}',4,1,0,0,0,0,0,'Visionary Augmenter','Kaladesh','Creature - Dwarf Artificer','uncommon',NULL,0,1),
(202402,'{4}{W}{W}',6,1,0,0,0,0,0,'Wispweaver Angel','Kaladesh','Creature - Angel','uncommon',NULL,0,1),
(202403,'{1}{U}',2,0,0,0,1,0,0,'Aether Meltdown','Kaladesh','Enchantment - Aura','uncommon',NULL,0,1),
(202404,'{1}{U}',2,0,0,0,1,0,0,'Aether Theorist','Kaladesh','Creature - Vedalken Rogue','common',NULL,0,1),
(202405,'{2}{U}',3,0,0,0,1,0,0,'Aether Tradewinds','Kaladesh','Instant','common',NULL,0,1),
(202406,'{5}{U}{U}',7,0,0,0,1,0,0,'Aethersquall Ancient','Kaladesh','Creature - Leviathan','rare',NULL,0,1),
(202407,'{U}',1,0,0,0,1,0,0,'Ceremonious Rejection','Kaladesh','Instant','uncommon',NULL,0,1),
(202408,'{3}{U}{U}',5,0,0,0,1,0,0,'Confiscation Coup','Kaladesh','Sorcery','rare',NULL,0,1),
(202409,'{1}{U}',2,0,0,0,1,0,0,'Curio Vendor','Kaladesh','Creature - Vedalken','common',NULL,0,1),
(202410,'{1}{U}{U}',3,0,0,0,1,0,0,'Disappearing Act','Kaladesh','Instant','uncommon',NULL,0,1),
(202411,'{1}{U}',2,0,0,0,1,0,0,'Dramatic Reversal','Kaladesh','Instant','common',NULL,0,1),
(202412,'{1}{U}',2,0,0,0,1,0,0,'Era of Innovation','Kaladesh','Enchantment','uncommon',NULL,0,1),
(202413,'{3}{U}{U}',5,0,0,0,1,0,0,'Experimental Aviator','Kaladesh','Creature - Human Artificer','uncommon',NULL,0,1),
(202414,'{2}{U}{U}',4,0,0,0,1,0,0,'Failed Inspection','Kaladesh','Instant','common',NULL,0,1),
(202415,'{5}{U}{U}',7,0,0,0,1,0,0,'Gearseeker Serpent','Kaladesh','Creature - Serpent','common',NULL,0,1),
(202416,'{3}{U}',4,0,0,0,1,0,0,'Glimmer of Genius','Kaladesh','Instant','uncommon',NULL,0,1),
(202417,'{1}{U}',2,0,0,0,1,0,0,'Glint-Nest Crane','Kaladesh','Creature - Bird','uncommon',NULL,0,1),
(202418,'{4}{U}',5,0,0,0,1,0,0,'Hightide Hermit','Kaladesh','Creature - Crab','common',NULL,0,1),
(202419,'{2}{U}{U}',4,0,0,0,1,0,0,'Insidious Will','Kaladesh','Instant','rare',NULL,0,1),
(202420,'{2}{U}',3,0,0,0,1,0,0,'Janjeet Sentry','Kaladesh','Creature - Vedalken Soldier','uncommon',NULL,0,1),
(202421,'{2}{U}{U}',4,0,0,0,1,0,0,'Long-Finned Skywhale','Kaladesh','Creature - Whale','uncommon',NULL,0,1),
(202422,'{3}{U}',4,0,0,0,1,0,0,'Malfunction','Kaladesh','Enchantment - Aura','common',NULL,0,1),
(202423,'{3}{U}{U}',5,0,0,0,1,0,0,'Metallurgic Summonings','Kaladesh','Enchantment','mythic',NULL,0,1),
(202424,'{U}',1,0,0,0,1,0,0,'Minister of Inquiries','Kaladesh','Creature - Vedalken Advisor','uncommon',NULL,0,1),
(202425,'{3}{U}',4,0,0,0,1,0,0,'Nimble Innovator','Kaladesh','Creature - Vedalken Artificer','common',NULL,0,1),
(202426,'{3}{U}',4,0,0,0,1,0,0,'Padeem, Consul of Innovation','Kaladesh','Creature - Vedalken Artificer','rare',NULL,0,1),
(202427,'{3}{U}',4,0,0,0,1,0,0,'Paradoxical Outcome','Kaladesh','Instant','rare',NULL,0,1),
(202428,'{1}{U}',2,0,0,0,1,0,0,'Revolutionary Rebuff','Kaladesh','Instant','common',NULL,0,1),
(202429,'{4}{U}{U}',6,0,0,0,1,0,0,'Saheeli\'s Artistry','Kaladesh','Sorcery','rare',NULL,0,1),
(202430,'{U}',1,0,0,0,1,0,0,'Select for Inspection','Kaladesh','Instant','common',NULL,0,1),
(202431,'{4}{U}',5,0,0,0,1,0,0,'Shrewd Negotiation','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202432,'{3}{U}{U}',5,0,0,0,1,0,0,'Tezzeret\'s Ambition','Kaladesh','Sorcery','common',NULL,0,1),
(202433,'{U}',1,0,0,0,1,0,0,'Thriving Turtle','Kaladesh','Creature - Turtle','common',NULL,0,1),
(202434,'{4}{U}{U}',6,0,0,0,1,0,0,'Torrential Gearhulk','Kaladesh','Artifact Creature - Construct','mythic',NULL,0,1),
(202435,'{2}{U}',3,0,0,0,1,0,0,'Vedalken Blademaster','Kaladesh','Creature - Vedalken Soldier','common',NULL,0,1),
(202436,'{3}{U}',4,0,0,0,1,0,0,'Weldfast Wingsmith','Kaladesh','Creature - Human Artificer','common',NULL,0,1),
(202437,'{2}{U}',3,0,0,0,1,0,0,'Wind Drake','Kaladesh','Creature - Drake','common',NULL,0,1),
(202439,'{3}{B}',4,0,0,0,0,1,0,'Aetherborn Marauder','Kaladesh','Creature - Aetherborn Rogue','uncommon',NULL,0,1),
(202440,'{4}{B}',5,0,0,0,0,1,0,'Ambitious Aetherborn','Kaladesh','Creature - Aetherborn Artificer','common',NULL,0,1),
(202441,'{3}{B}{B}{B}',6,0,0,0,0,1,0,'Demon of Dark Schemes','Kaladesh','Creature - Demon','mythic',NULL,0,1),
(202442,'{1}{B}',2,0,0,0,0,1,0,'Dhund Operative','Kaladesh','Creature - Human Rogue','common',NULL,0,1),
(202443,'{2}{B}{B}',4,0,0,0,0,1,0,'Diabolic Tutor','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202444,'{1}{B}',2,0,0,0,0,1,0,'Die Young','Kaladesh','Sorcery','common',NULL,0,1),
(202445,'{5}{B}',6,0,0,0,0,1,0,'Dukhara Scavenger','Kaladesh','Creature - Crocodile','common',NULL,0,1),
(202446,'{4}{B}',5,0,0,0,0,1,0,'Eliminate the Competition','Kaladesh','Sorcery','rare',NULL,0,1),
(202447,'{1}{B}',2,0,0,0,0,1,0,'Embraal Bruiser','Kaladesh','Creature - Human Warrior','uncommon',NULL,0,1),
(202448,'{1}{B}{B}',3,0,0,0,0,1,0,'Essence Extraction','Kaladesh','Instant','uncommon',NULL,0,1),
(202449,'{2}{B}',3,0,0,0,0,1,0,'Fortuitous Find','Kaladesh','Sorcery','common',NULL,0,1),
(202450,'{2}{B}',3,0,0,0,0,1,0,'Foundry Screecher','Kaladesh','Creature - Bat','common',NULL,0,1),
(202451,'{1}{B}',2,0,0,0,0,1,0,'Fretwork Colony','Kaladesh','Creature - Insect','uncommon',NULL,0,1),
(202452,'{2}{B}{B}',4,0,0,0,0,1,0,'Gonti, Lord of Luxury','Kaladesh','Creature - Aetherborn Rogue','rare',NULL,0,1),
(202453,'{B}',1,0,0,0,0,1,0,'Harsh Scrutiny','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202454,'{2}{B}',3,0,0,0,0,1,0,'Lawless Broker','Kaladesh','Creature - Aetherborn Rogue','common',NULL,0,1),
(202455,'{2}{B}',3,0,0,0,0,1,0,'Live Fast','Kaladesh','Sorcery','common',NULL,0,1),
(202456,'{1}{B}{B}',3,0,0,0,0,1,0,'Lost Legacy','Kaladesh','Sorcery','rare',NULL,0,1),
(202457,'{2}{B}',3,0,0,0,0,1,0,'Make Obsolete','Kaladesh','Instant','uncommon',NULL,0,1),
(202458,'{4}{B}{B}',6,0,0,0,0,1,0,'Marionette Master','Kaladesh','Creature - Human Artificer','rare',NULL,0,1),
(202459,'{3}{B}',4,0,0,0,0,1,0,'Maulfist Squad','Kaladesh','Creature - Human Artificer','common',NULL,0,1),
(202460,'{2}{B}{B}',4,0,0,0,0,1,0,'Midnight Oil','Kaladesh','Enchantment','rare',NULL,0,1),
(202461,'{2}{B}',3,0,0,0,0,1,0,'Mind Rot','Kaladesh','Sorcery','common',NULL,0,1),
(202462,'{1}{B}{B}',3,0,0,0,0,1,0,'Morbid Curiosity','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202463,'{B}',1,0,0,0,0,1,0,'Night Market Lookout','Kaladesh','Creature - Human Rogue','common',NULL,0,1),
(202464,'{4}{B}{B}',6,0,0,0,0,1,0,'Noxious Gearhulk','Kaladesh','Artifact Creature - Construct','mythic',NULL,0,1),
(202465,'{3}{B}',4,0,0,0,0,1,0,'Ovalchase Daredevil','Kaladesh','Creature - Human Pilot','uncommon',NULL,0,1),
(202466,'{3}{B}',4,0,0,0,0,1,0,'Prakhata Club Security','Kaladesh','Creature - Aetherborn Warrior','common',NULL,0,1),
(202467,'{1}{B}',2,0,0,0,0,1,0,'Rush of Vitality','Kaladesh','Instant','common',NULL,0,1),
(202468,'{1}{B}',2,0,0,0,0,1,0,'Subtle Strike','Kaladesh','Instant','common',NULL,0,1),
(202469,'{1}{B}',2,0,0,0,0,1,0,'Syndicate Trafficker','Kaladesh','Creature - Aetherborn Rogue','rare',NULL,0,1),
(202470,'{1}{B}',2,0,0,0,0,1,0,'Thriving Rats','Kaladesh','Creature - Rat','common',NULL,0,1),
(202471,'{3}{B}{B}',5,0,0,0,0,1,0,'Tidy Conclusion','Kaladesh','Instant','common',NULL,0,1),
(202472,'{1}{B}',2,0,0,0,0,1,0,'Underhanded Designs','Kaladesh','Enchantment','uncommon',NULL,0,1),
(202473,'{2}{B}',3,0,0,0,0,1,0,'Weaponcraft Enthusiast','Kaladesh','Creature - Aetherborn Artificer','uncommon',NULL,0,1),
(202474,'{2}{R}',3,0,1,0,0,0,0,'Aethertorch Renegade','Kaladesh','Creature - Human Rogue','uncommon',NULL,0,1),
(202475,'{1}{R}{R}',3,0,1,0,0,0,0,'Brazen Scourge','Kaladesh','Creature - Gremlin','uncommon',NULL,0,1),
(202477,'{R}',1,0,1,0,0,0,0,'Built to Smash','Kaladesh','Instant','common',NULL,0,1),
(202478,'{1}{R}',2,0,1,0,0,0,0,'Cathartic Reunion','Kaladesh','Sorcery','common',NULL,0,1),
(202479,'{2}{R}{R}',4,0,1,0,0,0,0,'Chandra, Torch of Defiance','Kaladesh','Planeswalker - Chandra','mythic',NULL,0,1),
(202480,'{1}{R}',2,0,1,0,0,0,0,'Chandra\'s Pyrohelix','Kaladesh','Instant','common',NULL,0,1),
(202481,'{4}{R}{R}',6,0,1,0,0,0,0,'Combustible Gearhulk','Kaladesh','Artifact Creature - Construct','mythic',NULL,0,1),
(202482,'{3}{R}',4,0,1,0,0,0,0,'Demolish','Kaladesh','Sorcery','common',NULL,0,1),
(202483,'{2}{R}{R}',4,0,1,0,0,0,0,'Fateful Showdown','Kaladesh','Instant','rare',NULL,0,1),
(202484,'{3}{R}',4,0,1,0,0,0,0,'Furious Reprisal','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202485,'{1}{R}',2,0,1,0,0,0,0,'Giant Spectacle','Kaladesh','Enchantment - Aura','common',NULL,0,1),
(202486,'{1}{R}',2,0,1,0,0,0,0,'Harnessed Lightning','Kaladesh','Instant','uncommon',NULL,0,1),
(202487,'{1}{R}{R}',3,0,1,0,0,0,0,'Hijack','Kaladesh','Sorcery','common',NULL,0,1),
(202488,'{2}{R}{R}',4,0,1,0,0,0,0,'Incendiary Sabotage','Kaladesh','Instant','uncommon',NULL,0,1),
(202489,'{R}',1,0,1,0,0,0,0,'Inventor\'s Apprentice','Kaladesh','Creature - Human Artificer','uncommon',NULL,0,1),
(202490,'{2}{R}',3,0,1,0,0,0,0,'Lathnu Hellion','Kaladesh','Creature - Hellion','rare',NULL,0,1),
(202491,'{3}{R}',4,0,1,0,0,0,0,'Madcap Experiment','Kaladesh','Sorcery','rare',NULL,0,1),
(202492,'{3}{R}',4,0,1,0,0,0,0,'Maulfist Doorbuster','Kaladesh','Creature - Human Warrior','uncommon',NULL,0,1),
(202493,'{2}{R}',3,0,1,0,0,0,0,'Pia Nalaar','Kaladesh','Creature - Human Artificer','rare',NULL,0,1),
(202494,'{2}{R}',3,0,1,0,0,0,0,'Quicksmith Genius','Kaladesh','Creature - Human Artificer','uncommon',NULL,0,1),
(202495,'{1}{R}',2,0,1,0,0,0,0,'Reckless Fireweaver','Kaladesh','Creature - Human Artificer','common',NULL,0,1),
(202496,'{R}',1,0,1,0,0,0,0,'Renegade Tactics','Kaladesh','Sorcery','common',NULL,0,1),
(202497,'{R}',1,0,1,0,0,0,0,'Ruinous Gremlin','Kaladesh','Creature - Gremlin','common',NULL,0,1),
(202498,'{2}{R}',3,0,1,0,0,0,0,'Salivating Gremlins','Kaladesh','Creature - Gremlin','common',NULL,0,1),
(202499,'{2}{R}{R}',4,0,1,0,0,0,0,'Skyship Stalker','Kaladesh','Creature - Cat Dragon','rare',NULL,0,1),
(202500,'{R}',1,0,1,0,0,0,0,'Spark of Creativity','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202501,'{1}{R}',2,0,1,0,0,0,0,'Speedway Fanatic','Kaladesh','Creature - Human Pilot','uncommon',NULL,0,1),
(202502,'{2}{R}',3,0,1,0,0,0,0,'Spireside Infiltrator','Kaladesh','Creature - Human Rogue','common',NULL,0,1),
(202503,'{3}{R}',4,0,1,0,0,0,0,'Spontaneous Artist','Kaladesh','Creature - Human Rogue','common',NULL,0,1),
(202504,'{3}{R}',4,0,1,0,0,0,0,'Start Your Engines','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202505,'{3}{R}',4,0,1,0,0,0,0,'Territorial Gorger','Kaladesh','Creature - Gremlin','rare',NULL,0,1),
(202506,'{3}{R}',4,0,1,0,0,0,0,'Terror of the Fairgrounds','Kaladesh','Creature - Gremlin','common',NULL,0,1),
(202507,'{1}{R}',2,0,1,0,0,0,0,'Thriving Grubs','Kaladesh','Creature - Gremlin','common',NULL,0,1),
(202508,'{4}{R}',5,0,1,0,0,0,0,'Wayward Giant','Kaladesh','Creature - Giant','common',NULL,0,1),
(202509,'{2}{R}',3,0,1,0,0,0,0,'Welding Sparks','Kaladesh','Instant','common',NULL,0,1),
(202510,'{2}{G}',3,0,0,1,0,0,0,'Appetite for the Unnatural','Kaladesh','Instant','common',NULL,0,1),
(202511,'{3}{G}{G}',5,0,0,1,0,0,0,'Arborback Stomper','Kaladesh','Creature - Beast','uncommon',NULL,0,1),
(202513,'{2}{G}',3,0,0,1,0,0,0,'Architect of the Untamed','Kaladesh','Creature - Elf Artificer Druid','rare',NULL,0,1),
(202514,'{3}{G}',4,0,0,1,0,0,0,'Armorcraft Judge','Kaladesh','Creature - Elf Artificer','uncommon',NULL,0,1),
(202515,'{G}',1,0,0,1,0,0,0,'Attune with Aether','Kaladesh','Sorcery','common',NULL,0,1),
(202516,'{G}',1,0,0,1,0,0,0,'Blossoming Defense','Kaladesh','Instant','uncommon',NULL,0,1),
(202517,'{2}{G}{G}',4,0,0,1,0,0,0,'Bristling Hydra','Kaladesh','Creature - Hydra','rare',NULL,0,1),
(202518,'{1}{G}',2,0,0,1,0,0,0,'Commencement of Festivities','Kaladesh','Instant','common',NULL,0,1),
(202519,'{4}{G}{G}',6,0,0,1,0,0,0,'Cowl Prowler','Kaladesh','Creature - Wurm','common',NULL,0,1),
(202520,'{2}{G}{G}',4,0,0,1,0,0,0,'Creeping Mold','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202521,'{3}{G}{G}',5,0,0,1,0,0,0,'Cultivator of Blades','Kaladesh','Creature - Elf Artificer','rare',NULL,0,1),
(202522,'{3}{G}',4,0,0,1,0,0,0,'Dubious Challenge','Kaladesh','Sorcery','rare',NULL,0,1),
(202523,'{1}{G}',2,0,0,1,0,0,0,'Durable Handicraft','Kaladesh','Enchantment','uncommon',NULL,0,1),
(202524,'{4}{G}{G}',6,0,0,1,0,0,0,'Elegant Edgecrafters','Kaladesh','Creature - Elf Artificer','uncommon',NULL,0,1),
(202525,'{2}{G}',3,0,0,1,0,0,0,'Fairgrounds Trumpeter','Kaladesh','Creature - Elephant','uncommon',NULL,0,1),
(202526,'{2}{G}',3,0,0,1,0,0,0,'Ghirapur Guide','Kaladesh','Creature - Elf Scout','uncommon',NULL,0,1),
(202527,'{2}{G}',3,0,0,1,0,0,0,'Highspire Artisan','Kaladesh','Creature - Elf Artificer','common',NULL,0,1),
(202528,'{3}{G}',4,0,0,1,0,0,0,'Hunt the Weak','Kaladesh','Sorcery','common',NULL,0,1),
(202529,'{1}{G}',2,0,0,1,0,0,0,'Kujar Seedsculptor','Kaladesh','Creature - Elf Druid','common',NULL,0,1),
(202530,'{1}{G}',2,0,0,1,0,0,0,'Larger Than Life','Kaladesh','Sorcery','common',NULL,0,1),
(202531,'{1}{G}',2,0,0,1,0,0,0,'Longtusk Cub','Kaladesh','Creature - Cat','uncommon',NULL,0,1),
(202532,'{1}{G}',2,0,0,1,0,0,0,'Nature\'s Way','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202533,'{3}{G}{G}',5,0,0,1,0,0,0,'Nissa, Vital Force','Kaladesh','Planeswalker - Nissa','mythic',NULL,0,1),
(202534,'{G}',1,0,0,1,0,0,0,'Ornamental Courage','Kaladesh','Instant','common',NULL,0,1),
(202535,'{G}',1,0,0,1,0,0,0,'Oviya Pashiri, Sage Lifecrafter','Kaladesh','Creature - Human Artificer','rare',NULL,0,1),
(202536,'{2}{G}{G}',4,0,0,1,0,0,0,'Peema Outrider','Kaladesh','Creature - Elf Artificer','common',NULL,0,1),
(202537,'{3}{G}{G}',5,0,0,1,0,0,0,'Riparian Tiger','Kaladesh','Creature - Cat','common',NULL,0,1),
(202538,'{1}{G}',2,0,0,1,0,0,0,'Sage of Shaila\'s Claim','Kaladesh','Creature - Elf Druid','common',NULL,0,1),
(202539,'{1}{G}',2,0,0,1,0,0,0,'Servant of the Conduit','Kaladesh','Creature - Elf Druid','uncommon',NULL,0,1),
(202540,'{G}',1,0,0,1,0,0,0,'Take Down','Kaladesh','Sorcery','common',NULL,0,1),
(202541,'{2}{G}',3,0,0,1,0,0,0,'Thriving Rhino','Kaladesh','Creature - Rhino','common',NULL,0,1),
(202542,'{3}{G}{G}',5,0,0,1,0,0,0,'Verdurous Gearhulk','Kaladesh','Artifact Creature - Construct','mythic',NULL,0,1),
(202543,'{3}{G}',4,0,0,1,0,0,0,'Wild Wanderer','Kaladesh','Creature - Elf Druid','common',NULL,0,1),
(202544,'{X}{X}{G}',1,0,0,1,0,0,0,'Wildest Dreams','Kaladesh','Sorcery','rare',NULL,0,1),
(202545,'{G}',1,0,0,1,0,0,0,'Wily Bandar','Kaladesh','Creature - Cat Monkey','common',NULL,0,1),
(202546,'{3}{W}{U}',5,1,0,0,1,0,0,'Cloudblazer','Kaladesh','Creature - Human Scout','uncommon',NULL,0,1),
(202547,'{U}{B}',2,0,0,0,1,1,0,'Contraband Kingpin','Kaladesh','Creature - Aetherborn Rogue','uncommon',NULL,0,1),
(202548,'{1}{R}{W}',3,1,1,0,0,0,0,'Depala, Pilot Exemplar','Kaladesh','Creature - Dwarf Pilot','rare',NULL,0,1),
(202549,'{2}{W}{U}',4,1,0,0,1,0,0,'Dovin Baan','Kaladesh','Planeswalker - Dovin','mythic',NULL,0,1),
(202550,'{1}{G}{U}',3,0,0,1,1,0,0,'Empyreal Voyager','Kaladesh','Creature - Vedalken Scout','uncommon',NULL,0,1),
(202551,'{3}{G}{W}',5,1,0,1,0,0,0,'Engineered Might','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202552,'{2}{B}{G}',4,0,0,1,0,1,0,'Hazardous Conditions','Kaladesh','Sorcery','uncommon',NULL,0,1),
(202553,'{1}{W}{B}',3,1,0,0,0,1,0,'Kambal, Consul of Allocation','Kaladesh','Creature - Human Advisor','rare',NULL,0,1),
(202554,'{2}{G}{U}',4,0,0,1,1,0,0,'Rashmi, Eternities Crafter','Kaladesh','Creature - Elf Druid','mythic',NULL,0,1),
(202555,'{2}{W}{B}',4,1,0,0,0,1,0,'Restoration Gearsmith','Kaladesh','Creature - Human Artificer','uncommon',NULL,0,1),
(202556,'{1}{U}{R}',3,0,1,0,1,0,0,'Saheeli Rai','Kaladesh','Planeswalker - Saheeli','mythic',NULL,0,1),
(202557,'{1}{B}{R}',3,0,1,0,0,1,0,'Unlicensed Disintegration','Kaladesh','Instant','uncommon',NULL,0,1),
(202558,'{R}{W}',2,1,1,0,0,0,0,'Veteran Motorist','Kaladesh','Creature - Dwarf Pilot','uncommon',NULL,0,1),
(202559,'{R}{G}',2,0,1,1,0,0,0,'Voltaic Brawler','Kaladesh','Creature - Human Warrior','uncommon',NULL,0,1),
(202560,'{1}{U}{R}',3,0,1,0,1,0,0,'Whirler Virtuoso','Kaladesh','Creature - Vedalken Artificer','uncommon',NULL,0,1),
(202561,'{7}',7,0,0,0,0,0,0,'Accomplished Automaton','Kaladesh','Artifact Creature - Construct','common',NULL,0,1),
(202562,'{4}',4,0,0,0,0,0,0,'Aetherflux Reservoir','Kaladesh','Artifact','rare',NULL,0,1),
(202563,'{4}',4,0,0,0,0,0,0,'Aetherworks Marvel','Kaladesh','Artifact','mythic',NULL,0,1),
(202564,'{1}',1,0,0,0,0,0,0,'Animation Module','Kaladesh','Artifact','rare',NULL,0,1),
(202565,'{5}',5,0,0,0,0,0,0,'Aradara Express','Kaladesh','Artifact - Vehicle','common',NULL,0,1),
(202566,'{5}',5,0,0,0,0,0,0,'Ballista Charger','Kaladesh','Artifact - Vehicle','uncommon',NULL,0,1),
(202567,'{5}',5,0,0,0,0,0,0,'Bastion Mastodon','Kaladesh','Artifact Creature - Elephant','common',NULL,0,1),
(202568,'{4}',4,0,0,0,0,0,0,'Bomat Bazaar Barge','Kaladesh','Artifact - Vehicle','uncommon',NULL,0,1),
(202569,'{1}',1,0,0,0,0,0,0,'Bomat Courier','Kaladesh','Artifact Creature - Construct','rare',NULL,0,1),
(202570,'{3}',3,0,0,0,0,0,0,'Chief of the Foundry','Kaladesh','Artifact Creature - Construct','uncommon',NULL,0,1),
(202571,'{2}',2,0,0,0,0,0,0,'Cogworker\'s Puzzleknot','Kaladesh','Artifact','common',NULL,0,1),
(202572,'{2}',2,0,0,0,0,0,0,'Consulate Skygate','Kaladesh','Artifact Creature - Wall','common',NULL,0,1),
(202573,'{3}',3,0,0,0,0,0,0,'Cultivator\'s Caravan','Kaladesh','Artifact - Vehicle','rare',NULL,0,1),
(202574,'{3}',3,0,0,0,0,0,0,'Deadlock Trap','Kaladesh','Artifact','rare',NULL,0,1),
(202575,'{2}',2,0,0,0,0,0,0,'Decoction Module','Kaladesh','Artifact','uncommon',NULL,0,1),
(202576,'{6}',6,0,0,0,0,0,0,'Demolition Stomper','Kaladesh','Artifact - Vehicle','uncommon',NULL,0,1),
(202577,'{4}',4,0,0,0,0,0,0,'Dukhara Peafowl','Kaladesh','Artifact Creature - Bird','common',NULL,0,1),
(202578,'{3}',3,0,0,0,0,0,0,'Dynavolt Tower','Kaladesh','Artifact','rare',NULL,0,1),
(202579,'{2}',2,0,0,0,0,0,0,'Eager Construct','Kaladesh','Artifact Creature - Construct','common',NULL,0,1),
(202580,'{3}',3,0,0,0,0,0,0,'Electrostatic Pummeler','Kaladesh','Artifact Creature - Construct','rare',NULL,0,1),
(202581,'{3}',3,0,0,0,0,0,0,'Fabrication Module','Kaladesh','Artifact','uncommon',NULL,0,1),
(202582,'{3}',3,0,0,0,0,0,0,'Filigree Familiar','Kaladesh','Artifact Creature - Fox','uncommon',NULL,0,1),
(202583,'{2}',2,0,0,0,0,0,0,'Fireforger\'s Puzzleknot','Kaladesh','Artifact','common',NULL,0,1),
(202584,'{4}',4,0,0,0,0,0,0,'Fleetwheel Cruiser','Kaladesh','Artifact - Vehicle','rare',NULL,0,1),
(202585,'{3}',3,0,0,0,0,0,0,'Foundry Inspector','Kaladesh','Artifact Creature - Construct','uncommon',NULL,0,1),
(202586,'{4}',4,0,0,0,0,0,0,'Ghirapur Orrery','Kaladesh','Artifact','rare',NULL,0,1),
(202587,'{2}',2,0,0,0,0,0,0,'Glassblower\'s Puzzleknot','Kaladesh','Artifact','common',NULL,0,1),
(202588,'{1}',1,0,0,0,0,0,0,'Inventor\'s Goggles','Kaladesh','Artifact - Equipment','common',NULL,0,1),
(202589,'{4}',4,0,0,0,0,0,0,'Iron League Steed','Kaladesh','Artifact Creature - Construct','uncommon',NULL,0,1),
(202590,'{2}',2,0,0,0,0,0,0,'Key to the City','Kaladesh','Artifact','rare',NULL,0,1),
(202591,'{2}',2,0,0,0,0,0,0,'Metalspinner\'s Puzzleknot','Kaladesh','Artifact','common',NULL,0,1),
(202592,'{11}',11,0,0,0,0,0,0,'Metalwork Colossus','Kaladesh','Artifact Creature - Construct','rare',NULL,0,1),
(202593,'{5}',5,0,0,0,0,0,0,'Multiform Wonder','Kaladesh','Artifact Creature - Construct','rare',NULL,0,1),
(202594,'{2}',2,0,0,0,0,0,0,'Narnam Cobra','Kaladesh','Artifact Creature - Snake','common',NULL,0,1),
(202595,'{4}',4,0,0,0,0,0,0,'Ovalchase Dragster','Kaladesh','Artifact - Vehicle','uncommon',NULL,0,1),
(202596,'{4}',4,0,0,0,0,0,0,'Panharmonicon','Kaladesh','Artifact','rare',NULL,0,1),
(202597,'{2}',2,0,0,0,0,0,0,'Perpetual Timepiece','Kaladesh','Artifact','uncommon',NULL,0,1),
(202598,'{3}',3,0,0,0,0,0,0,'Prakhata Pillar-Bug','Kaladesh','Artifact Creature - Insect','common',NULL,0,1),
(202599,'{2}',2,0,0,0,0,0,0,'Prophetic Prism','Kaladesh','Artifact','common',NULL,0,1),
(202600,'{3}',3,0,0,0,0,0,0,'Renegade Freighter','Kaladesh','Artifact - Vehicle','common',NULL,0,1),
(202601,'{2}',2,0,0,0,0,0,0,'Scrapheap Scrounger','Kaladesh','Artifact Creature - Construct','rare',NULL,0,1),
(202602,'{5}',5,0,0,0,0,0,0,'Self-Assembler','Kaladesh','Artifact Creature - Assembly-Worker','common',NULL,0,1),
(202603,'{2}',2,0,0,0,0,0,0,'Sky Skiff','Kaladesh','Artifact - Vehicle','common',NULL,0,1),
(202604,'{5}',5,0,0,0,0,0,0,'Skysovereign, Consul Flagship','Kaladesh','Artifact - Vehicle','mythic',NULL,0,1),
(202605,'{2}',2,0,0,0,0,0,0,'Smuggler\'s Copter','Kaladesh','Artifact - Vehicle','rare',NULL,0,1),
(202606,'{4}',4,0,0,0,0,0,0,'Snare Thopter','Kaladesh','Artifact Creature - Thopter','uncommon',NULL,0,1),
(202607,'{2}',2,0,0,0,0,0,0,'Torch Gauntlet','Kaladesh','Artifact - Equipment','common',NULL,0,1),
(202608,'{3}',3,0,0,0,0,0,0,'Weldfast Monitor','Kaladesh','Artifact Creature - Lizard','common',NULL,0,1),
(202609,'{3}',3,0,0,0,0,0,0,'Whirlermaker','Kaladesh','Artifact','uncommon',NULL,0,1),
(202610,'{2}',2,0,0,0,0,0,0,'Woodweaver\'s Puzzleknot','Kaladesh','Artifact','common',NULL,0,1),
(202611,'{3}',3,0,0,0,0,0,0,'Workshop Assistant','Kaladesh','Artifact Creature - Construct','common',NULL,0,1),
(202612,'',0,0,0,0,0,0,0,'Aether Hub','Kaladesh','Land','uncommon',NULL,0,1),
(202613,'',0,0,0,0,0,0,0,'Blooming Marsh','Kaladesh','Land','rare',NULL,0,1),
(202614,'',0,0,0,0,0,0,0,'Botanical Sanctum','Kaladesh','Land','rare',NULL,0,1),
(202615,'',0,0,0,0,0,0,0,'Concealed Courtyard','Kaladesh','Land','rare',NULL,0,1),
(202616,'',0,0,0,0,0,0,0,'Inspiring Vantage','Kaladesh','Land','rare',NULL,0,1),
(202617,'',0,0,0,0,0,0,0,'Inventors\' Fair','Kaladesh','Land','rare',NULL,0,1),
(202618,'',0,0,0,0,0,0,0,'Sequestered Stash','Kaladesh','Land','uncommon',NULL,0,1),
(202619,'',0,0,0,0,0,0,0,'Spirebluff Canal','Kaladesh','Land','rare',NULL,0,1),
(202620,'',0,0,0,0,0,0,0,'Plains','Kaladesh','Land - Plains','common',NULL,0,1),
(202623,'',0,0,0,0,0,0,0,'Island','Kaladesh','Land - Island','common',NULL,0,1),
(202626,'',0,0,0,0,0,0,0,'Swamp','Kaladesh','Land - Swamp','common',NULL,0,1),
(202629,'',0,0,0,0,0,0,0,'Mountain','Kaladesh','Land - Mountain','common',NULL,0,1),
(202632,'',0,0,0,0,0,0,0,'Forest','Kaladesh','Land - Forest','common',NULL,0,1),
(202635,'{4}{R}{R}',6,0,1,0,0,0,0,'Chandra, Pyrogenius','Kaladesh','Planeswalker - Chandra','mythic',NULL,0,1),
(202636,'{3}{R}',4,0,1,0,0,0,0,'Flame Lash','Kaladesh','Instant','common',NULL,0,1),
(202637,'{4}{R}',5,0,1,0,0,0,0,'Liberating Combustion','Kaladesh','Sorcery','rare',NULL,0,1),
(202638,'{2}{R}',3,0,1,0,0,0,0,'Renegade Firebrand','Kaladesh','Creature - Human Warrior','uncommon',NULL,0,1),
(202639,'',0,0,0,0,0,0,0,'Stone Quarry','Kaladesh','Land','common',NULL,0,1),
(202640,'{4}{G}{G}',6,0,0,1,0,0,0,'Nissa, Nature\'s Artisan','Kaladesh','Planeswalker - Nissa','mythic',NULL,0,1),
(202641,'{3}{G}',4,0,0,1,0,0,0,'Guardian of the Great Conduit','Kaladesh','Creature - Elemental','uncommon',NULL,0,1),
(202642,'{1}{G}',2,0,0,1,0,0,0,'Terrain Elemental','Kaladesh','Creature - Elemental','common',NULL,0,1),
(202644,'{3}{G}',4,0,0,1,0,0,0,'Verdant Crescendo','Kaladesh','Sorcery','rare',NULL,0,1),
(202645,'',0,0,0,0,0,0,0,'Woodland Stream','Kaladesh','Land','common',NULL,0,1);
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cardsets`
--

LOCK TABLES `cardsets` WRITE;
/*!40000 ALTER TABLE `cardsets` DISABLE KEYS */;
INSERT INTO `cardsets` VALUES
('2016-09-30','Kaladesh','Block','KLD',0,0,NULL),
('2009-07-17','Magic 2010','Core','M10',0,1,NULL),
('2024-02-09','Murders at Karlov Manor','Block','MKM',0,0,NULL),
('2019-10-04','Throne of Eldraine','Block','ELD',0,1,NULL);
/*!40000 ALTER TABLE `cardsets` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES
(53);
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `deckcontents`
--

LOCK TABLES `deckcontents` WRITE;
/*!40000 ALTER TABLE `deckcontents` DISABLE KEYS */;
INSERT INTO `deckcontents` VALUES
(185026,131830,100,0),
(185026,131838,100,0),
(185026,131846,100,0),
(185026,131854,100,0),
(185026,131862,100,0),
(185026,131870,100,0),
(185026,131878,100,0),
(185026,131886,100,0),
(185026,131894,100,0),
(185148,131827,60,0),
(185148,131831,20,0),
(185148,131835,60,0),
(185148,131839,20,0),
(185148,131843,60,0),
(185148,131847,20,0),
(185148,131851,60,0),
(185148,131855,20,0),
(185148,131859,60,0),
(185148,131863,20,0),
(185148,131867,60,0),
(185148,131871,20,0),
(185148,131875,60,0),
(185148,131879,20,0),
(185148,131883,60,0),
(185148,131887,20,0),
(185148,131891,60,0),
(185148,131895,20,0),
(185152,131828,60,0),
(185152,131836,60,0),
(185152,131844,60,0),
(185152,131852,60,0),
(185152,131860,60,0),
(185152,131868,60,0),
(185152,131876,60,0),
(185152,131884,60,0),
(185152,131892,60,0),
(185156,131830,60,0),
(185156,131830,15,1),
(185156,131838,60,0),
(185156,131838,15,1),
(185156,131846,60,0),
(185156,131846,15,1),
(185156,131854,60,0),
(185156,131854,15,1),
(185156,131862,60,0),
(185156,131862,15,1),
(185156,131870,60,0),
(185156,131870,15,1),
(185156,131878,60,0),
(185156,131878,15,1),
(185156,131886,60,0),
(185156,131886,15,1),
(185156,131894,60,0),
(185156,131894,15,1),
(185160,131831,20,0),
(185160,131832,54,0),
(185160,131839,20,0),
(185160,131840,54,0),
(185160,131847,20,0),
(185160,131848,54,0),
(185160,131855,20,0),
(185160,131856,54,0),
(185160,131863,20,0),
(185160,131864,54,0),
(185160,131871,20,0),
(185160,131872,54,0),
(185160,131879,20,0),
(185160,131880,54,0),
(185160,131887,20,0),
(185160,131888,54,0),
(185160,131895,20,0),
(185160,131896,54,0),
(185164,131831,20,0),
(185164,131839,20,0),
(185164,131847,20,0),
(185164,131855,20,0),
(185164,131863,20,0),
(185164,131871,20,0),
(185164,131879,20,0),
(185164,131887,20,0),
(185164,131895,20,0),
(185332,131832,6,0),
(185332,131832,1,1),
(185332,131840,6,0),
(185332,131840,1,1),
(185332,131848,6,0),
(185332,131848,1,1),
(185332,131856,6,0),
(185332,131856,1,1),
(185332,131864,6,0),
(185332,131864,1,1),
(185332,131872,6,0),
(185332,131872,1,1),
(185332,131880,6,0),
(185332,131880,1,1),
(185332,131888,6,0),
(185332,131888,1,1),
(185332,131896,6,0),
(185332,131896,1,1);
/*!40000 ALTER TABLE `deckcontents` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=42258 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deckerrors`
--

LOCK TABLES `deckerrors` WRITE;
/*!40000 ALTER TABLE `deckerrors` DISABLE KEYS */;
/*!40000 ALTER TABLE `deckerrors` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=131899 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `decks`
--

LOCK TABLES `decks` WRITE;
/*!40000 ALTER TABLE `decks` DISABLE KEYS */;
INSERT INTO `decks` VALUES
('Unclassified',131827,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:27:26'),
('Unclassified',131828,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:27:26'),
('Unclassified',131830,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:27:26'),
('Unclassified',131831,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:27:26'),
('Unclassified',131832,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:27:26'),
('Unclassified',131835,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:31:44'),
('Unclassified',131836,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:31:44'),
('Unclassified',131838,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:31:44'),
('Unclassified',131839,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:31:44'),
('Unclassified',131840,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:31:44'),
('Unclassified',131843,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:34:29'),
('Unclassified',131844,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:34:29'),
('Unclassified',131846,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:34:29'),
('Unclassified',131847,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:34:29'),
('Unclassified',131848,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:34:29'),
('Unclassified',131851,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:35:43'),
('Unclassified',131852,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:35:43'),
('Unclassified',131854,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:35:43'),
('Unclassified',131855,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:35:43'),
('Unclassified',131856,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:35:43'),
('Unclassified',131859,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:36:01'),
('Unclassified',131860,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:36:01'),
('Unclassified',131862,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:36:01'),
('Unclassified',131863,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:36:01'),
('Unclassified',131864,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:36:01'),
('Unclassified',131867,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:36:16'),
('Unclassified',131868,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:36:16'),
('Unclassified',131870,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:36:16'),
('Unclassified',131871,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:36:16'),
('Unclassified',131872,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:36:16'),
('Unclassified',131875,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:36:50'),
('Unclassified',131876,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:36:50'),
('Unclassified',131878,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:36:50'),
('Unclassified',131879,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:36:50'),
('Unclassified',131880,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:36:50'),
('Unclassified',131883,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:38:25'),
('Unclassified',131884,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:38:25'),
('Unclassified',131886,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:38:25'),
('Unclassified',131887,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:38:25'),
('Unclassified',131888,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:38:25'),
('Unclassified',131891,' Unclassified','testplayer0','','Modern',NULL,NULL,'8d1aa16fa2d731b0b6b78a2e75789e689044c3cc','da39a3ee5e6b4b0d3255bfef95601890afd80709','500ccd5406f3a195f0c42d09fcb8de3df8b2d66c','Plains','2024-09-04 21:38:54'),
('Unclassified',131892,' Unclassified','testplayer1','','Modern',NULL,NULL,'383c58dbaba74a32e32a6c6e18e1f098ccdb1200','da39a3ee5e6b4b0d3255bfef95601890afd80709','f8b7364f4b8a92f0ba3a943f232857221df08064','Island','2024-09-04 21:38:54'),
('Unclassified',131894,'B Unclassified','testplayer3','b','Modern',NULL,NULL,'9a5f1e44eec55f0372bcbca53c0173d2f066eaaf','a6668166a9ec0259806d2779ac5a87918e5efc10','0ab662d7362d8e677a67b1cc8f73fb392c6381ea','Swamp|Relentless Rats|Swamp','2024-09-04 21:38:54'),
('Unclassified',131895,' Unclassified','testplayer4','','Modern',NULL,NULL,'6659a60b6d65288ec0a03b455c19bea4dfeaf757','da39a3ee5e6b4b0d3255bfef95601890afd80709','aca0b42e3a97e0f561e70499f9419e3866c91ff7','Mountain|Forest|Plains','2024-09-04 21:38:54'),
('Unclassified',131896,'R Unclassified','testplayer5','r','Modern',NULL,NULL,'5586e462697b4bbaf974add3cb92df27692d3af1','c7422701cf9af5204da0135a3888fe00d75374f4','dff178bee3250eaca6477f61f1f1ffb311175673','Mountain|Seven Dwarves|Seven Dwarves','2024-09-04 21:38:54');
/*!40000 ALTER TABLE `decks` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `decktypes`
--

LOCK TABLES `decktypes` WRITE;
/*!40000 ALTER TABLE `decktypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `decktypes` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `entries`
--

LOCK TABLES `entries` WRITE;
/*!40000 ALTER TABLE `entries` DISABLE KEYS */;
INSERT INTO `entries` VALUES
(6871,'testplayer0','dot',131827,NULL,0,NULL,'2024-09-04 21:27:26',0,127),
(6871,'testplayer1','dot',131828,NULL,0,NULL,'2024-09-04 21:27:26',0,127),
(6871,'testplayer3','dot',131830,NULL,0,NULL,'2024-09-04 21:27:26',0,127),
(6871,'testplayer4','dot',131831,NULL,0,NULL,'2024-09-04 21:27:26',0,127),
(6871,'testplayer5','dot',131832,NULL,0,NULL,'2024-09-04 21:27:26',0,127),
(6872,'testplayer0','dot',131835,NULL,0,NULL,'2024-09-04 21:31:44',0,127),
(6872,'testplayer1','dot',131836,NULL,0,NULL,'2024-09-04 21:31:44',0,127),
(6872,'testplayer3','dot',131838,NULL,0,NULL,'2024-09-04 21:31:44',0,127),
(6872,'testplayer4','dot',131839,NULL,0,NULL,'2024-09-04 21:31:44',0,127),
(6872,'testplayer5','dot',131840,NULL,0,NULL,'2024-09-04 21:31:44',0,127),
(6873,'testplayer0','dot',131843,NULL,0,NULL,'2024-09-04 21:34:29',0,127),
(6873,'testplayer1','dot',131844,NULL,0,NULL,'2024-09-04 21:34:29',0,127),
(6873,'testplayer3','dot',131846,NULL,0,NULL,'2024-09-04 21:34:29',0,127),
(6873,'testplayer4','dot',131847,NULL,0,NULL,'2024-09-04 21:34:29',0,127),
(6873,'testplayer5','dot',131848,NULL,0,NULL,'2024-09-04 21:34:29',0,127),
(6874,'testplayer0','dot',131851,NULL,0,NULL,'2024-09-04 21:35:43',0,127),
(6874,'testplayer1','dot',131852,NULL,0,NULL,'2024-09-04 21:35:43',0,127),
(6874,'testplayer3','dot',131854,NULL,0,NULL,'2024-09-04 21:35:43',0,127),
(6874,'testplayer4','dot',131855,NULL,0,NULL,'2024-09-04 21:35:43',0,127),
(6874,'testplayer5','dot',131856,NULL,0,NULL,'2024-09-04 21:35:43',0,127),
(6875,'testplayer0','dot',131859,NULL,0,NULL,'2024-09-04 21:36:01',0,127),
(6875,'testplayer1','dot',131860,NULL,0,NULL,'2024-09-04 21:36:01',0,127),
(6875,'testplayer3','dot',131862,NULL,0,NULL,'2024-09-04 21:36:01',0,127),
(6875,'testplayer4','dot',131863,NULL,0,NULL,'2024-09-04 21:36:01',0,127),
(6875,'testplayer5','dot',131864,NULL,0,NULL,'2024-09-04 21:36:01',0,127),
(6876,'testplayer0','dot',131867,NULL,0,NULL,'2024-09-04 21:36:16',0,127),
(6876,'testplayer1','dot',131868,NULL,0,NULL,'2024-09-04 21:36:16',0,127),
(6876,'testplayer3','dot',131870,NULL,0,NULL,'2024-09-04 21:36:16',0,127),
(6876,'testplayer4','dot',131871,NULL,0,NULL,'2024-09-04 21:36:16',0,127),
(6876,'testplayer5','dot',131872,NULL,0,NULL,'2024-09-04 21:36:16',0,127),
(6877,'testplayer0','dot',131875,NULL,0,NULL,'2024-09-04 21:36:50',0,127),
(6877,'testplayer1','dot',131876,NULL,0,NULL,'2024-09-04 21:36:50',0,127),
(6877,'testplayer3','dot',131878,NULL,0,NULL,'2024-09-04 21:36:50',0,127),
(6877,'testplayer4','dot',131879,NULL,0,NULL,'2024-09-04 21:36:50',0,127),
(6877,'testplayer5','dot',131880,NULL,0,NULL,'2024-09-04 21:36:50',0,127),
(6878,'testplayer0','dot',131883,NULL,0,NULL,'2024-09-04 21:38:25',0,127),
(6878,'testplayer1','dot',131884,NULL,0,NULL,'2024-09-04 21:38:25',0,127),
(6878,'testplayer3','dot',131886,NULL,0,NULL,'2024-09-04 21:38:25',0,127),
(6878,'testplayer4','dot',131887,NULL,0,NULL,'2024-09-04 21:38:25',0,127),
(6878,'testplayer5','dot',131888,NULL,0,NULL,'2024-09-04 21:38:25',0,127),
(6879,'testplayer0','dot',131891,NULL,0,NULL,'2024-09-04 21:38:54',0,127),
(6879,'testplayer1','dot',131892,NULL,0,NULL,'2024-09-04 21:38:54',0,127),
(6879,'testplayer3','dot',131894,NULL,0,NULL,'2024-09-04 21:38:54',0,127),
(6879,'testplayer4','dot',131895,NULL,0,NULL,'2024-09-04 21:38:54',0,127),
(6879,'testplayer5','dot',131896,NULL,0,NULL,'2024-09-04 21:38:54',0,127);
/*!40000 ALTER TABLE `entries` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=6880 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES
(6871,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.01',1,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6872,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.02',2,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6873,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.03',3,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6874,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.04',4,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6875,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.05',5,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6876,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.06',6,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6877,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.07',7,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6878,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.08',8,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1),
(6879,'2024-09-04 21:00:00','Modern',NULL,16,'','Test 1.09',9,1,'Test','','',0,1,0,0,NULL,1,2,1,0,1,1,1,0,0,1);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `formats`
--

LOCK TABLES `formats` WRITE;
/*!40000 ALTER TABLE `formats` DISABLE KEYS */;
INSERT INTO `formats` VALUES
('Modern','','System','System',0,0,0,0,0,0,0,0,NULL,0,0,1,1,1,1,1,1,1,60,2000,0,15,0),
('Penny Dreadful','','System','System',0,0,0,0,0,0,0,0,NULL,0,0,0,1,1,1,1,1,1,60,2000,0,15,0),
('Standard','','System','System',0,0,0,0,0,0,0,0,NULL,0,1,0,1,1,1,1,1,1,60,2000,0,15,0);
/*!40000 ALTER TABLE `formats` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=261193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
INSERT INTO `matches` VALUES
(261139,'testplayer5','testplayer4',1,17378,'A',2,0,0,0,2,0,'verified'),
(261140,'testplayer3','testplayer0',1,17378,'A',2,0,0,0,2,0,'verified'),
(261141,'testplayer1','testplayer1',1,17378,'BYE',0,0,0,0,0,0,'verified'),
(261142,'testplayer4','testplayer1',2,17378,'P',0,0,0,0,0,0,'unverified'),
(261143,'testplayer0','testplayer0',2,17378,'BYE',0,0,0,0,0,0,'verified'),
(261144,'testplayer5','testplayer3',2,17378,'P',0,0,0,0,0,0,'unverified'),
(261145,'testplayer0','testplayer1',1,17380,'A',2,0,0,0,2,0,'verified'),
(261146,'testplayer4','testplayer3',1,17380,'A',2,0,0,0,2,0,'verified'),
(261147,'testplayer5','testplayer5',1,17380,'BYE',0,0,0,0,0,0,'verified'),
(261148,'testplayer4','testplayer1',2,17380,'P',0,0,0,0,0,0,'unverified'),
(261149,'testplayer5','testplayer0',2,17380,'P',0,0,0,0,0,0,'unverified'),
(261150,'testplayer3','testplayer3',2,17380,'BYE',0,0,0,0,0,0,'verified'),
(261151,'testplayer4','testplayer3',1,17382,'A',2,0,0,0,2,0,'verified'),
(261152,'testplayer5','testplayer0',1,17382,'A',2,0,0,0,2,0,'verified'),
(261153,'testplayer1','testplayer1',1,17382,'BYE',0,0,0,0,0,0,'verified'),
(261154,'testplayer5','testplayer3',2,17382,'P',0,0,0,0,0,0,'unverified'),
(261155,'testplayer1','testplayer4',2,17382,'P',0,0,0,0,0,0,'unverified'),
(261156,'testplayer0','testplayer0',2,17382,'BYE',0,0,0,0,0,0,'verified'),
(261157,'testplayer0','testplayer1',1,17384,'A',2,0,0,0,2,0,'verified'),
(261158,'testplayer3','testplayer4',1,17384,'A',2,0,0,0,2,0,'verified'),
(261159,'testplayer5','testplayer5',1,17384,'BYE',0,0,0,0,0,0,'verified'),
(261160,'testplayer3','testplayer0',2,17384,'P',0,0,0,0,0,0,'unverified'),
(261161,'testplayer4','testplayer5',2,17384,'P',0,0,0,0,0,0,'unverified'),
(261162,'testplayer1','testplayer1',2,17384,'BYE',0,0,0,0,0,0,'verified'),
(261163,'testplayer0','testplayer5',1,17386,'A',2,0,0,0,2,0,'verified'),
(261164,'testplayer1','testplayer3',1,17386,'A',2,0,0,0,2,0,'verified'),
(261165,'testplayer4','testplayer4',1,17386,'BYE',0,0,0,0,0,0,'verified'),
(261166,'testplayer4','testplayer3',2,17386,'P',0,0,0,0,0,0,'unverified'),
(261167,'testplayer0','testplayer1',2,17386,'P',0,0,0,0,0,0,'unverified'),
(261168,'testplayer5','testplayer5',2,17386,'BYE',0,0,0,0,0,0,'verified'),
(261169,'testplayer3','testplayer5',1,17388,'A',2,0,0,0,2,0,'verified'),
(261170,'testplayer0','testplayer4',1,17388,'A',2,0,0,0,2,0,'verified'),
(261171,'testplayer1','testplayer1',1,17388,'BYE',0,0,0,0,0,0,'verified'),
(261172,'testplayer0','testplayer3',2,17388,'P',0,0,0,0,0,0,'unverified'),
(261173,'testplayer1','testplayer5',2,17388,'P',0,0,0,0,0,0,'unverified'),
(261174,'testplayer4','testplayer4',2,17388,'BYE',0,0,0,0,0,0,'verified'),
(261175,'testplayer1','testplayer4',1,17390,'A',2,0,0,0,2,0,'verified'),
(261176,'testplayer3','testplayer0',1,17390,'A',2,0,0,0,2,0,'verified'),
(261177,'testplayer5','testplayer5',1,17390,'BYE',0,0,0,0,0,0,'verified'),
(261178,'testplayer3','testplayer1',2,17390,'P',0,0,0,0,0,0,'unverified'),
(261179,'testplayer0','testplayer5',2,17390,'P',0,0,0,0,0,0,'unverified'),
(261180,'testplayer4','testplayer4',2,17390,'BYE',0,0,0,0,0,0,'verified'),
(261181,'testplayer4','testplayer3',1,17392,'A',2,0,0,0,2,0,'verified'),
(261182,'testplayer1','testplayer5',1,17392,'A',2,0,0,0,2,0,'verified'),
(261183,'testplayer0','testplayer0',1,17392,'BYE',0,0,0,0,0,0,'verified'),
(261184,'testplayer3','testplayer1',2,17392,'P',0,0,0,0,0,0,'unverified'),
(261185,'testplayer5','testplayer5',2,17392,'BYE',0,0,0,0,0,0,'verified'),
(261186,'testplayer4','testplayer0',2,17392,'P',0,0,0,0,0,0,'unverified'),
(261187,'testplayer3','testplayer1',1,17394,'A',2,0,0,0,2,0,'verified'),
(261188,'testplayer5','testplayer4',1,17394,'A',2,0,0,0,2,0,'verified'),
(261189,'testplayer0','testplayer0',1,17394,'BYE',0,0,0,0,0,0,'verified'),
(261190,'testplayer5','testplayer0',2,17394,'P',0,0,0,0,0,0,'unverified'),
(261191,'testplayer4','testplayer3',2,17394,'P',0,0,0,0,0,0,'unverified'),
(261192,'testplayer1','testplayer1',2,17394,'BYE',0,0,0,0,0,0,'verified');
/*!40000 ALTER TABLE `matches` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `playerbans`
--

LOCK TABLES `playerbans` WRITE;
/*!40000 ALTER TABLE `playerbans` DISABLE KEYS */;
/*!40000 ALTER TABLE `playerbans` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=38028 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES
(38018,'testplayer0',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38019,'testplayer1',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38020,'testplayer2',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38021,'testplayer3',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38022,'testplayer4',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38023,'testplayer5',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38024,'testplayer6',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38025,'testplayer7',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38026,'testplayer8',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(38027,'testplayer9',NULL,0,0,0,NULL,0,NULL,-5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `restricted`
--

LOCK TABLES `restricted` WRITE;
/*!40000 ALTER TABLE `restricted` DISABLE KEYS */;
/*!40000 ALTER TABLE `restricted` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `restrictedtotribe`
--

LOCK TABLES `restrictedtotribe` WRITE;
/*!40000 ALTER TABLE `restrictedtotribe` DISABLE KEYS */;
/*!40000 ALTER TABLE `restrictedtotribe` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `season_points`
--

LOCK TABLES `season_points` WRITE;
/*!40000 ALTER TABLE `season_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `season_points` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `series`
--

LOCK TABLES `series` WRITE;
/*!40000 ALTER TABLE `series` DISABLE KEYS */;
INSERT INTO `series` VALUES
('Test',1,NULL,NULL,NULL,'Friday','00:00:00',1,0,'',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `series` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `series_organizers`
--

LOCK TABLES `series_organizers` WRITE;
/*!40000 ALTER TABLE `series_organizers` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_organizers` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `series_seasons`
--

LOCK TABLES `series_seasons` WRITE;
/*!40000 ALTER TABLE `series_seasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_seasons` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `series_stewards`
--

LOCK TABLES `series_stewards` WRITE;
/*!40000 ALTER TABLE `series_stewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_stewards` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `setlegality`
--

LOCK TABLES `setlegality` WRITE;
/*!40000 ALTER TABLE `setlegality` DISABLE KEYS */;
/*!40000 ALTER TABLE `setlegality` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=107058 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `standings`
--

LOCK TABLES `standings` WRITE;
/*!40000 ALTER TABLE `standings` DISABLE KEYS */;
INSERT INTO `standings` VALUES
('testplayer0','Test 1.01',1,1,0,2,0,1.000,0.000,1.000,0,107013,127,1,0,0),
('testplayer1','Test 1.01',1,0,0,0,1,0.330,0.330,0.330,3,107014,127,1,1,0),
('testplayer3','Test 1.01',1,1,2,2,0,0.330,1.000,0.000,3,107015,127,1,1,0),
('testplayer4','Test 1.01',1,1,0,2,0,1.000,0.000,1.000,0,107016,127,1,0,0),
('testplayer5','Test 1.01',1,1,2,2,0,0.330,1.000,0.000,3,107017,127,1,1,0),
('testplayer0','Test 1.02',1,1,2,2,0,0.330,1.000,0.000,3,107018,127,1,1,0),
('testplayer1','Test 1.02',1,1,0,2,0,1.000,0.000,1.000,0,107019,127,1,0,0),
('testplayer3','Test 1.02',1,1,0,2,0,1.000,0.000,1.000,0,107020,127,1,0,0),
('testplayer4','Test 1.02',1,1,2,2,0,0.330,1.000,0.000,3,107021,127,1,1,0),
('testplayer5','Test 1.02',1,0,0,0,1,0.330,0.330,0.330,3,107022,127,1,1,0),
('testplayer0','Test 1.03',1,1,0,2,0,1.000,0.000,1.000,0,107023,127,1,0,0),
('testplayer1','Test 1.03',1,0,0,0,1,0.330,0.330,0.330,3,107024,127,1,1,0),
('testplayer3','Test 1.03',1,1,0,2,0,1.000,0.000,1.000,0,107025,127,1,0,0),
('testplayer4','Test 1.03',1,1,2,2,0,0.330,1.000,0.000,3,107026,127,1,1,0),
('testplayer5','Test 1.03',1,1,2,2,0,0.330,1.000,0.000,3,107027,127,1,1,0),
('testplayer0','Test 1.04',1,1,2,2,0,0.330,1.000,0.000,3,107028,127,1,1,0),
('testplayer1','Test 1.04',1,1,0,2,0,1.000,0.000,1.000,0,107029,127,1,0,0),
('testplayer3','Test 1.04',1,1,2,2,0,0.330,1.000,0.000,3,107030,127,1,1,0),
('testplayer4','Test 1.04',1,1,0,2,0,1.000,0.000,1.000,0,107031,127,1,0,0),
('testplayer5','Test 1.04',1,0,0,0,1,0.330,0.330,0.330,3,107032,127,1,1,0),
('testplayer0','Test 1.05',1,1,2,2,0,0.330,1.000,0.000,3,107033,127,1,1,0),
('testplayer1','Test 1.05',1,1,2,2,0,0.330,1.000,0.000,3,107034,127,1,1,0),
('testplayer3','Test 1.05',1,1,0,2,0,1.000,0.000,1.000,0,107035,127,1,0,0),
('testplayer4','Test 1.05',1,0,0,0,1,0.330,0.330,0.330,3,107036,127,1,1,0),
('testplayer5','Test 1.05',1,1,0,2,0,1.000,0.000,1.000,0,107037,127,1,0,0),
('testplayer0','Test 1.06',1,1,2,2,0,0.330,1.000,0.000,3,107038,127,1,1,0),
('testplayer1','Test 1.06',1,0,0,0,1,0.330,0.330,0.330,3,107039,127,1,1,0),
('testplayer3','Test 1.06',1,1,2,2,0,0.330,1.000,0.000,3,107040,127,1,1,0),
('testplayer4','Test 1.06',1,1,0,2,0,1.000,0.000,1.000,0,107041,127,1,0,0),
('testplayer5','Test 1.06',1,1,0,2,0,1.000,0.000,1.000,0,107042,127,1,0,0),
('testplayer0','Test 1.07',1,1,0,2,0,1.000,0.000,1.000,0,107043,127,1,0,0),
('testplayer1','Test 1.07',1,1,2,2,0,0.330,1.000,0.000,3,107044,127,1,1,0),
('testplayer3','Test 1.07',1,1,2,2,0,0.330,1.000,0.000,3,107045,127,1,1,0),
('testplayer4','Test 1.07',1,1,0,2,0,1.000,0.000,1.000,0,107046,127,1,0,0),
('testplayer5','Test 1.07',1,0,0,0,1,0.330,0.330,0.330,3,107047,127,1,1,0),
('testplayer0','Test 1.08',1,0,0,0,1,0.330,0.330,0.330,3,107048,127,1,1,0),
('testplayer1','Test 1.08',1,1,2,2,0,0.330,1.000,0.000,3,107049,127,1,1,0),
('testplayer3','Test 1.08',1,1,0,2,0,1.000,0.000,1.000,0,107050,127,1,0,0),
('testplayer4','Test 1.08',1,1,2,2,0,0.330,1.000,0.000,3,107051,127,1,1,0),
('testplayer5','Test 1.08',1,1,0,2,0,1.000,0.000,1.000,0,107052,127,1,0,0),
('testplayer0','Test 1.09',1,0,0,0,1,0.330,0.330,0.330,3,107053,127,1,1,0),
('testplayer1','Test 1.09',1,1,0,2,0,1.000,0.000,1.000,0,107054,127,1,0,0),
('testplayer3','Test 1.09',1,1,2,2,0,0.330,1.000,0.000,3,107055,127,1,1,0),
('testplayer4','Test 1.09',1,1,0,2,0,1.000,0.000,1.000,0,107056,127,1,0,0),
('testplayer5','Test 1.09',1,1,2,2,0,0.330,1.000,0.000,3,107057,127,1,1,0);
/*!40000 ALTER TABLE `standings` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `stewards`
--

LOCK TABLES `stewards` WRITE;
/*!40000 ALTER TABLE `stewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `stewards` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=17396 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subevents`
--

LOCK TABLES `subevents` WRITE;
/*!40000 ALTER TABLE `subevents` DISABLE KEYS */;
INSERT INTO `subevents` VALUES
('Test 1.01',3,1,'Swiss',17378),
('Test 1.01',3,2,'Single Elimination',17379),
('Test 1.02',3,1,'Swiss',17380),
('Test 1.02',3,2,'Single Elimination',17381),
('Test 1.03',3,1,'Swiss',17382),
('Test 1.03',3,2,'Single Elimination',17383),
('Test 1.04',3,1,'Swiss',17384),
('Test 1.04',3,2,'Single Elimination',17385),
('Test 1.05',3,1,'Swiss',17386),
('Test 1.05',3,2,'Single Elimination',17387),
('Test 1.06',3,1,'Swiss',17388),
('Test 1.06',3,2,'Single Elimination',17389),
('Test 1.07',3,1,'Swiss',17390),
('Test 1.07',3,2,'Single Elimination',17391),
('Test 1.08',3,1,'Swiss',17392),
('Test 1.08',3,2,'Single Elimination',17393),
('Test 1.09',3,1,'Swiss',17394),
('Test 1.09',3,2,'Single Elimination',17395);
/*!40000 ALTER TABLE `subevents` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `subformats`
--

LOCK TABLES `subformats` WRITE;
/*!40000 ALTER TABLE `subformats` DISABLE KEYS */;
/*!40000 ALTER TABLE `subformats` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `subtype_bans`
--

LOCK TABLES `subtype_bans` WRITE;
/*!40000 ALTER TABLE `subtype_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `subtype_bans` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tribe_bans`
--

LOCK TABLES `tribe_bans` WRITE;
/*!40000 ALTER TABLE `tribe_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `tribe_bans` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tribes`
--

LOCK TABLES `tribes` WRITE;
/*!40000 ALTER TABLE `tribes` DISABLE KEYS */;
INSERT INTO `tribes` VALUES
('Advisor'),
('Aetherborn'),
('Angel'),
('Antelope'),
('Ape'),
('Archer'),
('Archon'),
('Artificer'),
('Assassin'),
('Assembly-Worker'),
('Avatar'),
('Bard'),
('Bat'),
('Bear'),
('Beast'),
('Berserker'),
('Bird'),
('Camel'),
('Cat'),
('Centaur'),
('Citizen'),
('Cleric'),
('Clue'),
('Construct'),
('Crab'),
('Crocodile'),
('Cyclops'),
('Demon'),
('Detective'),
('Devil'),
('Djinn'),
('Dog'),
('Dragon'),
('Drake'),
('Druid'),
('Dryad'),
('Dwarf'),
('Efreet'),
('Elemental'),
('Elephant'),
('Elf'),
('Elk'),
('Faerie'),
('Fish'),
('Food'),
('Fox'),
('Fungus'),
('Gargoyle'),
('Giant'),
('Gnome'),
('Goat'),
('Goblin'),
('God'),
('Golem'),
('Gorgon'),
('Gremlin'),
('Griffin'),
('Hellion'),
('Homunculus'),
('Horror'),
('Horse'),
('Human'),
('Hydra'),
('Hyena'),
('Illusion'),
('Insect'),
('Jackal'),
('Knight'),
('Lammasu'),
('Leech'),
('Leviathan'),
('Lizard'),
('Merfolk'),
('Minotaur'),
('Mole'),
('Monkey'),
('Nightmare'),
('Noble'),
('Octopus'),
('Ogre'),
('Ooze'),
('Ouphe'),
('Ox'),
('Peasant'),
('Pegasus'),
('Phoenix'),
('Pilot'),
('Plant'),
('Ranger'),
('Rat'),
('Rhino'),
('Rogue'),
('Scarecrow'),
('Scout'),
('Serpent'),
('Shade'),
('Shaman'),
('Shapeshifter'),
('Siren'),
('Skeleton'),
('Snake'),
('Soldier'),
('Specter'),
('Sphinx'),
('Spider'),
('Spirit'),
('Thopter'),
('Thrull'),
('Treefolk'),
('Troll'),
('Turtle'),
('Unicorn'),
('Vampire'),
('Vedalken'),
('Wall'),
('Warlock'),
('Warrior'),
('Weird'),
('Whale'),
('Wizard'),
('Wolf'),
('Wolverine'),
('Wraith'),
('Wurm'),
('Zombie');
/*!40000 ALTER TABLE `tribes` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `trophies`
--

LOCK TABLES `trophies` WRITE;
/*!40000 ALTER TABLE `trophies` DISABLE KEYS */;
/*!40000 ALTER TABLE `trophies` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2024-09-04 18:45:08
