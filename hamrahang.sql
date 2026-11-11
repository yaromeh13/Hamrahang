-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2026 at 07:21 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hamraha2_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

DROP TABLE IF EXISTS `album`;
CREATE TABLE IF NOT EXISTS `album` (
  `id_album` int(11) NOT NULL AUTO_INCREMENT,
  `album_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `album_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `album_rd` date DEFAULT NULL,
  `album_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_album`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `album2artist`
--

DROP TABLE IF EXISTS `album2artist`;
CREATE TABLE IF NOT EXISTS `album2artist` (
  `id_album2artist` int(11) NOT NULL AUTO_INCREMENT,
  `id_album` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  PRIMARY KEY (`id_album2artist`),
  UNIQUE KEY `uq_album_artist` (`id_album`,`id_artist`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

DROP TABLE IF EXISTS `artist`;
CREATE TABLE IF NOT EXISTS `artist` (
  `id_artist` int(11) NOT NULL AUTO_INCREMENT,
  `artist_name` varchar(255) NOT NULL,
  `artist_data` longtext DEFAULT NULL,
  `artist_bd` date DEFAULT NULL,
  `artist_cc` char(2) DEFAULT NULL COMMENT 'ISO country code e.g. IR, KR, CA',
  `artist_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_artist`),
  KEY `idx_artist_cc` (`artist_cc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artist2genre`
--

DROP TABLE IF EXISTS `artist2genre`;
CREATE TABLE IF NOT EXISTS `artist2genre` (
  `id_artist` int(11) NOT NULL,
  `id_genre` int(11) NOT NULL,
  PRIMARY KEY (`id_artist`,`id_genre`),
  KEY `idx_genre_artist` (`id_genre`,`id_artist`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id_user` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `content_type` int(1) NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id_user`,`id_content`,`content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

DROP TABLE IF EXISTS `genre`;
CREATE TABLE IF NOT EXISTS `genre` (
  `id_genre` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `data` text DEFAULT NULL,
  `genre_act` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_genre`),
  UNIQUE KEY `uq_genre_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item2genre`
--

DROP TABLE IF EXISTS `item2genre`;
CREATE TABLE IF NOT EXISTS `item2genre` (
  `item_type` tinyint(4) NOT NULL,
  `item_id` int(11) NOT NULL,
  `id_genre` int(11) NOT NULL,
  PRIMARY KEY (`item_type`,`item_id`,`id_genre`),
  KEY `idx_genre_item` (`id_genre`,`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item2lang`
--

DROP TABLE IF EXISTS `item2lang`;
CREATE TABLE IF NOT EXISTS `item2lang` (
  `item_type` tinyint(4) NOT NULL,
  `item_id` int(11) NOT NULL,
  `lang_code` char(5) NOT NULL,
  PRIMARY KEY (`item_type`,`item_id`,`lang_code`),
  KEY `idx_lang_item` (`lang_code`,`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
CREATE TABLE IF NOT EXISTS `language` (
  `code` char(5) NOT NULL,
  `name` varchar(64) NOT NULL,
  `data` text DEFAULT NULL,
  `lang_act` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id_user` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `content_type` int(1) NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id_user`,`id_content`,`content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_song`
--

DROP TABLE IF EXISTS `link_song`;
CREATE TABLE IF NOT EXISTS `link_song` (
  `id_sng2lnk` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT 0,
  `id_song` int(11) NOT NULL,
  `song_link` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `song_bit` int(1) NOT NULL DEFAULT 128,
  `file_id` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_unq` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sng2lnk`),
  KEY `link_song_ibfk_1` (`id_song`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_video`
--

DROP TABLE IF EXISTS `link_video`;
CREATE TABLE IF NOT EXISTS `link_video` (
  `id_vdo2lnk` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_video` int(11) NOT NULL,
  `video_link` varchar(500) NOT NULL,
  `vdo_bit` int(1) NOT NULL,
  `file_id` varchar(500) NOT NULL,
  `file_unq` varchar(500) NOT NULL,
  `link_act` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_vdo2lnk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lyric`
--

DROP TABLE IF EXISTS `lyric`;
CREATE TABLE IF NOT EXISTS `lyric` (
  `src` varchar(64) NOT NULL COMMENT 'plan-B key: s{publicId}l_fa / s{publicId}s_en',
  `item_type` tinyint(3) UNSIGNED NOT NULL COMMENT '0=song 1=podcast 2=musicvideo ...',
  `item_id` int(10) UNSIGNED NOT NULL COMMENT 'real id without IDHM offset',
  `lng` char(3) NOT NULL COMMENT 'fa / en / ...',
  `knd` char(3) NOT NULL COMMENT 'lrc | srt',
  `fmt` char(3) NOT NULL DEFAULT 'jsn' COMMENT 'jsn | txt',
  `body` mediumtext NOT NULL COMMENT 'plain text or JSON lines [{t,x},...]',
  `updated_at` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'unix timestamp',
  PRIMARY KEY (`src`),
  UNIQUE KEY `uq_item_lng_knd` (`item_type`,`item_id`,`lng`,`knd`),
  KEY `idx_item` (`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

DROP TABLE IF EXISTS `playlist`;
CREATE TABLE IF NOT EXISTS `playlist` (
  `id_playlist` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int(11) NOT NULL,
  `playlist_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `pl_act` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_playlist`),
  KEY `idx_user` (`id_user`),
  KEY `idx_act` (`pl_act`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlist_items`
--

DROP TABLE IF EXISTS `playlist_items`;
CREATE TABLE IF NOT EXISTS `playlist_items` (
  `id_playlist` int(11) NOT NULL,
  `item_type` tinyint(4) NOT NULL,
  `item_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `added_at` int(11) NOT NULL,
  PRIMARY KEY (`id_playlist`,`item_type`,`item_id`),
  KEY `idx_playlist_pos` (`id_playlist`,`position`),
  KEY `idx_item` (`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_plays`
--

DROP TABLE IF EXISTS `recent_plays`;
CREATE TABLE IF NOT EXISTS `recent_plays` (
  `id_user` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `content_type` int(11) NOT NULL,
  `played_at` timestamp NOT NULL,
  PRIMARY KEY (`id_user`,`id_content`,`content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
CREATE TABLE IF NOT EXISTS `song` (
  `id_song` int(11) NOT NULL AUTO_INCREMENT,
  `song_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `song_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `song_rd` date DEFAULT NULL,
  `song_ply` int(11) DEFAULT 0,
  `song_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_song`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song2album`
--

DROP TABLE IF EXISTS `song2album`;
CREATE TABLE IF NOT EXISTS `song2album` (
  `id_song` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `trk_num` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_song`,`id_album`),
  UNIQUE KEY `uq_song_album` (`id_song`,`id_album`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song2artist`
--

DROP TABLE IF EXISTS `song2artist`;
CREATE TABLE IF NOT EXISTS `song2artist` (
  `id_song` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  UNIQUE KEY `uq_song_artist` (`id_song`,`id_artist`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song2playlist`
--

DROP TABLE IF EXISTS `song2playlist`;
CREATE TABLE IF NOT EXISTS `song2playlist` (
  `id_s2p` int(11) NOT NULL AUTO_INCREMENT,
  `id_song` int(11) NOT NULL,
  `id_playlist` int(11) NOT NULL,
  `s2p_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_s2p`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `song2video`
--

DROP TABLE IF EXISTS `song2video`;
CREATE TABLE IF NOT EXISTS `song2video` (
  `id_song` int(11) NOT NULL,
  `id_video` int(11) NOT NULL,
  UNIQUE KEY `uq_song_video` (`id_song`,`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thumb`
--

DROP TABLE IF EXISTS `thumb`;
CREATE TABLE IF NOT EXISTS `thumb` (
  `id_img` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_img`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tv_login_sessions`
--

DROP TABLE IF EXISTS `tv_login_sessions`;
CREATE TABLE IF NOT EXISTS `tv_login_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `id_tlg` bigint(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','expired') DEFAULT 'pending',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `id_tlg` bigint(20) NOT NULL DEFAULT 0,
  `u_name` varchar(100) DEFAULT NULL,
  `u_pass` varchar(255) NOT NULL,
  `f_name` varchar(100) NOT NULL,
  `l_name` varchar(100) NOT NULL,
  `u_email` varchar(100) DEFAULT NULL,
  `u_data` mediumtext NOT NULL,
  `u_ref` varchar(255) NOT NULL DEFAULT '0',
  `act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uniq_u_name` (`u_name`),
  UNIQUE KEY `uniq_u_email` (`u_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

DROP TABLE IF EXISTS `user_devices`;
CREATE TABLE IF NOT EXISTS `user_devices` (
  `device_token` varchar(128) NOT NULL,
  `id_user` int(11) NOT NULL,
  `device_type` varchar(32) DEFAULT 'unknown',
  `device_label` varchar(128) DEFAULT NULL,
  `refresh_token_hash` char(64) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `last_active_at` int(11) NOT NULL,
  PRIMARY KEY (`device_token`),
  KEY `idx_user` (`id_user`),
  KEY `idx_refresh` (`refresh_token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

DROP TABLE IF EXISTS `user_favorites`;
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id_user` int(11) NOT NULL,
  `item_type` tinyint(4) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id_user`,`item_type`,`item_id`),
  KEY `idx_item` (`item_type`,`item_id`),
  KEY `idx_user_time` (`id_user`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

DROP TABLE IF EXISTS `user_history`;
CREATE TABLE IF NOT EXISTS `user_history` (
  `id_user` int(11) NOT NULL,
  `item_type` tinyint(4) NOT NULL,
  `item_id` int(11) NOT NULL,
  `played_at` int(11) NOT NULL,
  PRIMARY KEY (`id_user`,`item_type`,`item_id`),
  KEY `idx_user_time` (`id_user`,`played_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video`
--

DROP TABLE IF EXISTS `video`;
CREATE TABLE IF NOT EXISTS `video` (
  `id_video` int(11) NOT NULL AUTO_INCREMENT,
  `video_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_rd` date DEFAULT NULL,
  `video_ply` int(11) NOT NULL DEFAULT 0,
  `video_act` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video2artist`
--

DROP TABLE IF EXISTS `video2artist`;
CREATE TABLE IF NOT EXISTS `video2artist` (
  `id_vdo2art` int(11) NOT NULL AUTO_INCREMENT,
  `id_video` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  PRIMARY KEY (`id_vdo2art`),
  UNIQUE KEY `uq_video_artist` (`id_video`,`id_artist`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `link_song`
--
ALTER TABLE `link_song`
  ADD CONSTRAINT `link_song_ibfk_1` FOREIGN KEY (`id_song`) REFERENCES `song` (`id_song`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
