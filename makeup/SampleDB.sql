-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               8.0.31-0ubuntu0.22.04.1 - (Ubuntu)
-- Server Betriebssystem:        Linux
-- HeidiSQL Version:             12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Exportiere Datenbank Struktur für makeup
CREATE DATABASE IF NOT EXISTS `makeup` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `makeup`;

-- Exportiere Struktur von Tabelle makeup.sampledata
CREATE TABLE IF NOT EXISTS `sampledata` (
  `uid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `year` int unsigned NOT NULL DEFAULT '0',
  `city` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `deleted` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- Exportiere Daten aus Tabelle makeup.sampledata: ~6 rows (ungefähr)
INSERT INTO `sampledata` (`uid`, `name`, `year`, `city`, `country`, `deleted`) VALUES
	(1, 'Margo Cooper', 2016, 'Bremen', 'Bulgaria', 0),
	(2, 'Julya Gershun	', 2017, 'El Gouna', 'Ukraine', 0),
	(3, 'Janet Leyva', 2018, 'Hurgada', 'Peru', 0),
	(4, 'Nicole Menayo', 2019, 'Hurgada', 'Spain', 0),
	(5, 'Pierinna Patino', 2020, 'Hurgada', 'Peru', 0),
	(6, 'Natalie Kocendova', 2022, 'Hurgada', 'Czech Republic', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
