SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `password` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `vorname` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `email` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `disabled` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `created` text CHARACTER SET latin1 NOT NULL,
  `lastmod` int(11) NOT NULL,
  `url` text CHARACTER SET latin1 NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `text` longblob,
  `disabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `created` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `lastmod` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `url` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `text` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `draftsold` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `created` text CHARACTER SET latin1 NOT NULL,
  `lastmod` text CHARACTER SET latin1 NOT NULL,
  `url` text CHARACTER SET latin1 NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `timestamp` int(11) NOT NULL,
  `message` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `typeofaction` mediumtext COLLATE utf8_german2_ci NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ip` text COLLATE utf8_german2_ci NOT NULL,
  `device` text COLLATE utf8_german2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `menu` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `link` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;


ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `articles`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `drafts`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `draftsold`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `log`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `logins`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `menu`
  ADD UNIQUE KEY `id` (`id`);


ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `draftsold`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `logins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
