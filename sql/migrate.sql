
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Export von Tabelle static_post
# ------------------------------------------------------------

DROP TABLE IF EXISTS `static_post`;

CREATE TABLE `static_post` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(8) DEFAULT NULL,
  `language` varchar(3) DEFAULT 'de',
  `headline` varchar(255) DEFAULT NULL,
  `nav_title` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `author` varchar(255) DEFAULT 'ROSH',
  `visible` tinyint(1) DEFAULT 0,
  `createDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `changeDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `pid_lang_pk` (`pid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Export von Tabelle broadcasts
# ------------------------------------------------------------
DROP TABLE IF EXISTS `broadcasts`;

CREATE TABLE `broadcasts` (
  `uid` int(9) NOT NULL AUTO_INCREMENT,
  `bcid` int(7) NOT NULL DEFAULT 0,
  `language` varchar(3) NOT NULL DEFAULT '',
  `message` tinytext DEFAULT NULL,
  `type` enum('success','info','warning','danger') DEFAULT NULL,
  `createDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `startDate` datetime DEFAULT NULL,
  `expireDate` datetime DEFAULT NULL,
  `hide` tinyint(1) DEFAULT 0,
  UNIQUE KEY `broadcasts_uid_uindex` (`uid`),
  UNIQUE KEY `bcid_lang_pk` (`bcid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




# Export von Tabelle user_oauth
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_oauth`;

CREATE TABLE `user_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text NOT NULL,
  `description` text DEFAULT NULL,
  `provider` varchar(255) DEFAULT '',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_useroauth` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
