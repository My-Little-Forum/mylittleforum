-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: dd2034
-- Erstellungszeit: 01. Jan 2020 um 23:42
-- Server-Version: 5.7.28-nmm1-log
-- PHP-Version: 5.6.38-nmm2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `d030b03d`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mlf2_scores`
--

CREATE TABLE `mlf2_scores` (
  `posting_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mlf2_votes`
--

CREATE TABLE `mlf2_votes` (
  `user_id` int(11) NOT NULL,
  `posting_id` int(11) NOT NULL,
  `vote` int(11) NOT NULL,
  `tstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `mlf2_scores`
--
ALTER TABLE `mlf2_scores`
  ADD PRIMARY KEY (`posting_id`);

--
-- Indizes für die Tabelle `mlf2_votes`
--
ALTER TABLE `mlf2_votes`
  ADD PRIMARY KEY (`posting_id`,`user_id`,`vote`),
  ADD KEY `ix_posting_id` (`posting_id`),
  ADD KEY `ix_user_id` (`user_id`);
COMMIT;
