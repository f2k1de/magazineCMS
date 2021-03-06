-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 30. Aug 2017 um 20:52
-- Server-Version: 5.7.19-0ubuntu0.16.04.1
-- PHP-Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


--
-- Tabellenstruktur für Tabelle `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` mediumtext COLLATE utf8_general_ci NOT NULL,
  `password` mediumtext COLLATE utf8_general_ci NOT NULL,
  `vorname` mediumtext COLLATE utf8_general_ci NOT NULL,
  `email` mediumtext COLLATE utf8_general_ci NOT NULL,
  `disabled` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `articles`
--

CREATE TABLE `articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `teaser` text COLLATE utf8_general_ci NOT NULL,
  `created` text CHARACTER SET latin1 NOT NULL,
  `lastmod` int(11) NOT NULL,
  `url` text CHARACTER SET latin1 NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `details` tinyint(1) NOT NULL,
  `text` longblob,
  `disabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `drafts`
--

CREATE TABLE `drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` mediumtext COLLATE utf8_general_ci NOT NULL,
  `teaser` text COLLATE utf8_general_ci NOT NULL,
  `created` mediumtext COLLATE utf8_general_ci NOT NULL,
  `lastmod` mediumtext COLLATE utf8_general_ci NOT NULL,
  `url` mediumtext COLLATE utf8_general_ci NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `details` tinyint(1) NOT NULL,
  `text` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `draftsold`
--

CREATE TABLE `draftsold` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `teaser` text COLLATE utf8_general_ci NOT NULL,
  `created` text CHARACTER SET latin1 NOT NULL,
  `lastmod` text CHARACTER SET latin1 NOT NULL,
  `url` text CHARACTER SET latin1 NOT NULL,
  `aktuell` tinyint(1) NOT NULL,
  `details` tinyint(1) NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `issue`
--

CREATE TABLE `issue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `link` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log`
--

CREATE TABLE `log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `timestamp` int(11) NOT NULL,
  `message` mediumtext COLLATE utf8_general_ci NOT NULL,
  `typeofaction` mediumtext COLLATE utf8_general_ci NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `logins`
--

CREATE TABLE `logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ip` text COLLATE utf8_general_ci NOT NULL,
  `device` text COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `menu`
--

CREATE TABLE `menu` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `link` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `articles`
--
ALTER TABLE `articles`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `drafts`
--
ALTER TABLE `drafts`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `draftsold`
--
ALTER TABLE `draftsold`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `issue`
--
ALTER TABLE `issue`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `log`
--
ALTER TABLE `log`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `logins`
--
ALTER TABLE `logins`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `menu`
--
ALTER TABLE `menu`
  ADD UNIQUE KEY `id` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
