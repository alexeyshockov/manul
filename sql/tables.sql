/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Dump of table manul_errors
# ------------------------------------------------------------

DROP TABLE IF EXISTS `manul_errors`;

CREATE TABLE `manul_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` char(10) CHARACTER SET utf8 NOT NULL,
  `queue` char(100) CHARACTER SET utf8 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `entity_type` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `packet` longtext CHARACTER SET utf8,
  `exception_type` char(255) CHARACTER SET utf8 NOT NULL,
  `exception_description` text CHARACTER SET utf8,
  `exception_stack_trace` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Package level errors.';

# Dump of table manul_profiling
# ------------------------------------------------------------

DROP TABLE IF EXISTS `manul_profiling`;

CREATE TABLE `manul_profiling` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `entity_type` char(50) CHARACTER SET utf8 NOT NULL,
  `entity_local_id` int(10) unsigned zerofill DEFAULT NULL,
  `entity_remote_id` int(10) unsigned NOT NULL,
  `system` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `processing_time` int(10) unsigned NOT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `resolving_local_id_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `resolving_dependencies_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `creating_importer_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `filling_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `saving_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `binding_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  `hash_checking_time` int(10) unsigned DEFAULT NULL COMMENT 'Float number, multiplied by 1000 (3 digits after decimal point). Seconds.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Time profiling for importing step.';

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
