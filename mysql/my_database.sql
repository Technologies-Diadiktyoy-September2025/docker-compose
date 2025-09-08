CREATE DATABASE IF NOT EXISTS my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE my_database;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `lists`
--

CREATE TABLE `lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_follows`
--

CREATE TABLE `list_follows` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `list_id` int(10) UNSIGNED NOT NULL,
  `followed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_items`
--

CREATE TABLE `list_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `list_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `list_items`
--

INSERT INTO `list_items` (`id`, `list_id`, `content_id`, `added_at`) VALUES
(1, 5, 1, '2025-09-06 18:21:07'),
(2, 5, 2, '2025-09-06 19:45:48'),
(3, 5, 3, '2025-09-06 19:46:23'),
(4, 5, 4, '2025-09-06 19:50:01'),
(6, 1, 6, '2025-09-07 17:26:54'),
(7, 1, 7, '2025-09-07 17:43:32'),
(9, 7, 9, '2025-09-07 17:59:34'),
(12, 1, 11, '2025-09-07 18:47:21'),
(13, 1, 12, '2025-09-07 18:47:45'),
(14, 8, 13, '2025-09-07 19:01:04'),
(15, 8, 14, '2025-09-07 19:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `streaming_content`
--

CREATE TABLE `streaming_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `youtube_video_id` varchar(50) NOT NULL,
  `title` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `channel_title` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `added_by_user_id` int(10) UNSIGNED NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `streaming_content`
--

INSERT INTO `streaming_content` (`id`, `youtube_video_id`, `title`, `description`, `thumbnail_url`, `duration`, `channel_title`, `published_at`, `added_by_user_id`, `added_at`) VALUES
(1, '9bZkp7q19f0', 'PSY - GANGNAM STYLE(강남스타일) M/V', 'PSY - ‘I LUV IT’ M/V @ https://youtu.be/Xvjnoagk6GU\nPSY - ‘New Face’ M/V @https://youtu.be/OwJPPaEyqhI\n\nPSY - 8TH ALBUM \'4X2=8\' on iTunes @\nhttps://smarturl.it/PSY_8thAlbum\n\nPSY - GANGNAM STYLE(강남스타일) on iTunes @ http://smarturl.it/PsyGangnam\n\n#PSY #싸이 #GANGNAMSTYLE #강남스타일\n\nMore about PSY@\nhttp://www.youtube.com/officialpsy\nhttp://www.facebook.com/officialpsy\nhttp://twitter.com/psy_oppa\nhttps://www.instagram.com/42psy42\nhttp://iTunes.com/PSY\nhttp://sptfy.com/PSY\nhttp://weibo.com/psyoppa', 'https://i.ytimg.com/vi/9bZkp7q19f0/mqdefault.jpg', NULL, 'officialpsy', '2012-07-15 07:46:32', 2, '2025-09-06 18:21:07'),
(2, 'WBkCZ8MaAl8', 'HIGHLIGHTS | Brighton v Man City | Premier League', '#brightonandhovealbion #premierleague #mancity \n\nAction from the Premier League, as Brighton welcomed Manchester City to the Amex. \n\nHighlights in partnership with @mpbcom. Buy, sell and trade used camera gear. https://bit.ly/MPB457', 'https://i.ytimg.com/vi/WBkCZ8MaAl8/mqdefault.jpg', NULL, 'Official Brighton & Hove Albion FC', '2025-08-31 21:00:06', 2, '2025-09-06 19:45:48'),
(3, '39VJVyKhbGM', 'AGUEROOOOOOO!!! - When Sergio Agüero won the Premier League for Man City vs QPR, 2012', '', 'https://i.ytimg.com/vi/39VJVyKhbGM/mqdefault.jpg', NULL, 'Football Clips', '2022-04-05 14:48:46', 2, '2025-09-06 19:46:23'),
(4, 'DoHWPi2kBlE', 'Why Nobody Can Stop Jack Grealish', 'Jack Grealish has been on fire since his loan move from Manchester City to Everton. Already racking up 4 assists, he now has more than he managed in the previous two seasons combined. But what’s behind this transformation? @JamesLawrenceAllcott  is here on the latest edition of The Breakdown as we take a closer look at Grealish’s first few games for the Toffees.\n\nSubscribe to the official Premier League YouTube channel: http://preml.ge/PremierLeagueYouTube \nPremier League website: http://preml.ge/PremierLeagueWebsite\nFollow the Premier League on Instagram: http://preml.ge/PremierLeagueInstagram\nFollow the Premier League on X: https://preml.ge/PremierLeagueX\nFollow the Premier League on WhatsApp: https://preml.ge/PremierLeagueWhatsApp\nLike the Premier League on Facebook: http://preml.ge/PremierLeagueFacebook\nPlay Fantasy Premier League: http://preml.ge/FantasyPremierLeague\nTo license Premier League match footage: https://imgreplay.com/contact\n\n#football #premierleague #soccer \n\nYour safety online\n\nVisit the Child Exploitation and Online Protection website for confidential support if something has happened online which has made you feel unsafe, if you are worried about someone else or to report online abuse. (https://www.ceop.police.uk/safety-centre)\n\nYou should contact the Police by calling 999 if you or anybody else is in any sort of danger.\n\nVisit CEOP’s Thinkuknow website for advice and guidance on safe surfing and staying safe online for example when using mobile phones, blogs, social media, chatting, online gaming and emailing. (https://www.thinkuknow.co.uk)\n\nYou can also visit the Premier League safeguarding page for more information. (https://www.premierleague.com/safeguarding)', 'https://i.ytimg.com/vi/DoHWPi2kBlE/mqdefault.jpg', NULL, 'Premier League', '2025-09-04 10:45:01', 2, '2025-09-06 19:50:00'),
(5, 'EIdDpYcwcxY', 'Highlights: STUNNING Szoboszlai Free-kick | Liverpool 1-0 Arsenal', 'Watch Premier League highlights from Anfield as Dominik Szoboszlai\'s stunning free-kick in the 83rd minute saw Liverpool secure all three points against Arsenal.\n\nStarting XI: Alisson, Szoboszlai, Van Dijk, Konate, Kerkez, Gravenberch, Wirtz, Mac Allister, Salah, Gakpo, Ekitike. \n\nSubstitutes: Mamardashvili, Bradley, Gomez, Robertson, Endo, Jones, Chiesa, Elliott, Ngumoha.\n\n🔴 Get closer the champions with All Red Video - Full details can be found at video.liverpoolfc.com - All Red Video is available for new members with the first month free and the option to cancel at any time.  Ts & Cs apply\n🔔 SUBSCRIBE for free, so you never miss a video or live stream! https://www.youtube.com/subscription_center?add_user=LiverpoolFC\n📺 Watch even more from the Reds with a YouTube Channel Membership, including LFC emojis, extra uploads and LIVE academy games: https://www.youtube.com/LiverpoolFC/join\n🛍️ Shop LFC - Get your replica kits and much more! https://lfc.tv/3YTiEyj\nLiverpool FC - YouTube\n\n❗️Subtitles proving difficult to follow? You can edit the settings in order to make them as readable and personalised as possible. Just select subtitles from the settings menu, then options, where you can modify the font, size, colour, background and much more.', 'https://i.ytimg.com/vi/EIdDpYcwcxY/mqdefault.jpg', NULL, 'Liverpool FC', '2025-08-31 21:00:13', 1, '2025-09-07 12:17:33'),
(6, 'kfcqgb3aW8s', 'Liverpool WON’T Sign Guehi in January ❌', '#liverpool \n#guehi \n#crystalpalace \n#realmadrid', 'https://i.ytimg.com/vi/kfcqgb3aW8s/mqdefault.jpg', NULL, 'LeosGoals', '2025-09-07 13:50:12', 1, '2025-09-07 17:26:54'),
(7, 'Yra8d73UnxM', 'Trying on the shirt for the first time 🤩9️⃣', '🔴 Get closer the champions with All Red Video - Full details can be found at video.liverpoolfc.com - All Red Video is available for new members with the first month free and the option to cancel at any time.  Ts & Cs apply\n🔔 SUBSCRIBE for free, so you never miss a video or live stream! https://www.youtube.com/subscription_center?add_user=LiverpoolFC\n📺 Watch even more from the Reds with a YouTube Channel Membership, including LFC emojis, extra uploads and LIVE academy games: https://www.youtube.com/LiverpoolFC/join\n🛍️ Shop LFC - Get your replica kits and much more! https://lfc.tv/3YTiEyj\nLiverpool FC - YouTube\n\n❗️Subtitles proving difficult to follow? You can edit the settings in order to make them as readable and personalised as possible. Just select subtitles from the settings menu, then options, where you can modify the font, size, colour, background and much more.', 'https://i.ytimg.com/vi/Yra8d73UnxM/mqdefault.jpg', NULL, 'Liverpool FC', '2025-09-02 18:00:29', 1, '2025-09-07 17:43:32'),
(8, 'CicKccgEzbc', 'ΠΑΟΚ - Φερεντσβάρος 5 - 0 | Highlights - UEFA Europa League 2024/25 - 12/12/2024 | COSMOTE SPORT HD', 'Εγγραφείτε στο κανάλι της COSMOTE TV: https://www.youtube.com/@COSMOTE_TV\n\nΠαρακολουθήστε τα highlights της αναμέτρησης ΠΑΟΚ - Φερεντσβάρος, του UEFA Europa League, στις 12/12/2024.\n\nΟι σκόρερ των ομάδων:\nΠΑΟΚ - Taison (10\'), Thomas B. (29\'), Chalov F. (76\'), Zivkovic A. (80\'), Despodov K. (89\')\nΦερεντσβάρος - \n\nΓια να παρακολουθήσετε όλα τα Highlights του UEFA Europa League πατήστε εδώ: https://bit.ly/EuropaLeague25', 'https://i.ytimg.com/vi/CicKccgEzbc/mqdefault.jpg', NULL, 'COSMOTE TV', '2024-12-12 22:38:04', 3, '2025-09-07 17:59:11'),
(9, 'kYfajx744y0', 'Τα στιγμιότυπα του ΠΑΟΚ-HNK Rijeka - PAOK TV', 'Subscribe to our official Youtube channel: http://www.youtube.com/channel/UCInZn...\nViber Sticker Pack: https://vb.me/ebb707\nPAOK TV official: https://tv.paokfc.gr/\nFacebook: http://www.facebook.com/PAOKFOOTBALL\nTwitter: http://twitter.com/PAOK_FC\nInstagram: http://instagram.com/paok_fc', 'https://i.ytimg.com/vi/kYfajx744y0/mqdefault.jpg', NULL, 'PAOK FC / ΠΑΕ ΠΑΟΚ', '2025-08-28 20:56:55', 3, '2025-09-07 17:59:34'),
(10, 'ge4gV0GzmcQ', 'Video ge4gV0GzmcQ', '', NULL, NULL, NULL, NULL, 1, '2025-09-07 18:44:17'),
(11, 'MICDYrG2_4A', 'Arne Slot\'s Liverpool is SCARIER Than You Think', 'Arne Slot’s Liverpool is starting to look frightening. With a fresh system and a wave of new signings, the Reds now have depth, flexibility, and firepower across the pitch. Here’s why Liverpool under Slot could be one of the scariest teams in Europe this season.\n\n#Liverpool #ArneSlot #PremierLeague #LFC #Football', 'https://i.ytimg.com/vi/MICDYrG2_4A/mqdefault.jpg', NULL, 'PedTalksSports', '2025-09-07 00:15:00', 1, '2025-09-07 18:47:21'),
(12, 'qpsz74C9EdA', 'A new number 9.', '🔴 Get closer the champions with All Red Video - Full details can be found at video.liverpoolfc.com - All Red Video is available for new members with the first month free and the option to cancel at any time.  Ts & Cs apply\n🔔 SUBSCRIBE for free, so you never miss a video or live stream! https://www.youtube.com/subscription_center?add_user=LiverpoolFC\n📺 Watch even more from the Reds with a YouTube Channel Membership, including LFC emojis, extra uploads and LIVE academy games: https://www.youtube.com/LiverpoolFC/join\n🛍️ Shop LFC - Get your replica kits and much more! https://lfc.tv/3YTiEyj\nLiverpool FC - YouTube\n\n❗️Subtitles proving difficult to follow? You can edit the settings in order to make them as readable and personalised as possible. Just select subtitles from the settings menu, then options, where you can modify the font, size, colour, background and much more.', 'https://i.ytimg.com/vi/qpsz74C9EdA/mqdefault.jpg', NULL, 'Liverpool FC', '2025-09-02 00:01:02', 1, '2025-09-07 18:47:45'),
(13, 'y7KEgKqnAhI', 'Prwti tou PAS gia tin season 2008-09', 'Prwti tou PAS gia tin season 2008-09\r\n\r\nEna mikro kai ligo athlio videaki apo tin prwti proponisi tou PAS etsi gia na parete mia geusi :)', 'https://i.ytimg.com/vi/y7KEgKqnAhI/mqdefault.jpg', NULL, 'ArXiLaMaS', '2008-07-16 20:54:44', 4, '2025-09-07 19:01:04'),
(14, 'wMnkSmI9XRA', 'Ο Τραμπ μετονομάζει Υπουργείο Άμυνας σε Υπουργείο Πολέμου', '', 'https://i.ytimg.com/vi/wMnkSmI9XRA/mqdefault.jpg', NULL, 'Vérité', '2025-09-06 16:42:25', 4, '2025-09-07 19:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `password_hash`, `email`, `created_at`) VALUES
(1, 'Nikos', 'Nikolaou', 'TestNo1', '$2y$10$f2VCS2OONfiP6/a9fotNXe13ojLP6KdUWTXyTWeg9PTl2y0YUZZMe', 'serafnikol@gmail.com', '2025-09-06 16:08:07'),
(2, 'Lazaros', 'Theofylaktou', 'TestNo2', '$2y$10$ldpUAqaqOXnzo7A81khngu7Ztt/D95lAvQIqqTT8F6KRV9JbzcnqK', 'p20theo@ionio.gr', '2025-09-06 16:09:06'),
(3, 'Nikos', 'Iakovidis', 'TestNo3', '$2y$10$d.H.ZqaKIbCLdJoFj/odgeTgpHFaWgeIXEig.zy8lnhhplJMek7My', 'p20iako@ionio.gr', '2025-09-07 17:56:59'),
(4, 'Pericles', 'Zacharis', 'PeriZaha', '$2y$10$DY6ee1giLXOO8dbA1litRu7Z98wRwcnrAAFM0nTLmESMsh3C4Wq7K', 'pericleszacharis@gmail.com', '2025-09-07 18:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_follows`
--

CREATE TABLE `user_follows` (
  `id` int(10) UNSIGNED NOT NULL,
  `follower_id` int(10) UNSIGNED NOT NULL,
  `following_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_follows`
--

INSERT INTO `user_follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(4, 1, 2, '2025-09-07 18:20:16'),
(5, 1, 3, '2025-09-07 18:20:24'),
(6, 4, 1, '2025-09-07 19:01:17'),
(7, 4, 3, '2025-09-07 19:01:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_lists`
--

CREATE TABLE `user_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `list_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_lists`
--

INSERT INTO `user_lists` (`id`, `user_id`, `list_name`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 1, 'ListTestNo1', 'list test no 1', 1, '2025-09-06 17:31:51', '2025-09-06 17:31:51'),
(2, 2, 'Lest Test 2 lazos', 'test 2', 1, '2025-09-06 17:35:59', '2025-09-06 17:35:59'),
(5, 2, 'test 3 lazos new', 't3l new', 1, '2025-09-06 17:58:05', '2025-09-06 18:15:46'),
(7, 3, 'list paok', 'list paok', 1, '2025-09-07 17:58:57', '2025-09-07 17:58:57'),
(8, 4, 'Pas Giannina', 'pas giannina', 1, '2025-09-07 18:59:24', '2025-09-07 18:59:31');

-- --------------------------------------------------------

--
-- Table structure for table `youtube_credentials`
--

CREATE TABLE `youtube_credentials` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `youtube_credentials`
--

INSERT INTO `youtube_credentials` (`id`, `user_id`, `access_token`, `refresh_token`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'ya29.a0AS3H6NyFGiULwI2EjUW2ioAg7VH7mXGzWwWhXNsHu9ocBUWSffqVCX702OoVG3YcGoT87LXTS1WJuAuxf0Kc09CFaIGrFvvRl4P3SOSQ4FwmvOznTgI5Kv9o3JzDJMOMtKTzqRs7CdDi6WZL7aVC61-4xaptKmUUTZb6ObQTJezOdDO3wslb5uv_RLX7KiuHLTSV9r5SaCgYKARkSARISFQHGX2MiTIWqKYiVJEeFHLPY1e564A0207', '1//09DgYktCSShRaCgYIARAAGAkSNwF-L9IrmDvzyQA3wP9rkeJg-iiIKLHGIx_LAhZIVPBFXaC36e8jdOkZ-gq7Pl6jyy22xNr90Z4', '2025-09-07 12:13:17', '2025-09-06 19:39:44', '2025-09-07 12:13:18'),
(2, 1, 'ya29.a0AS3H6NwNlYS7f0V-yOMoaSb5jmipbH4kL75TgcoMUW3i9g7hJDb03TD8S0nn9NTk4bTrExaIKIi9NT1ZvStzVNtLnmfYENwKi3tWZ2lA58PzVgEzS_ZSGJBMOfXi0xUWVfACT-eEki2BW2VZ0zvLq2BWTqSD-eL8wx_wP20-bPkUpvHDTnfR04Xnp84SfszehWi9UqyPaCgYKAewSARYSFQHGX2MicJ0ISDfzkLHtiVrWYNPYjw0207', '1//09IiHr0nWTpNWCgYIARAAGAkSNwF-L9IrnqYV69wr746iK_17Zub2BBGH3gRMvfm9Ae5GWub9Lmm4xlpHT438Z9jVR0_WO_nXkXA', '2025-09-07 18:44:01', '2025-09-07 12:17:18', '2025-09-07 18:44:02'),
(3, 3, 'ya29.a0AS3H6NyeMgi8vK2fQapNrn_EzY5qbeYGofx9gsmBDdkI-LYdF8A70HWl6TCLpMOrd02vf33xIn0oxFpio9SfiL7QobVxP9H0zEMobb_pwieU9rsJDr09mTY9NN0sUe_xnWlj5uGvVz0rIVyt6qxZZuPFTVGrcmic37y4NTqjCGiFAtNvRm-byrtitfLfNOcqfGQ0kc8aCgYKAQ8SARUSFQHGX2MiWAFVEk_G2VK3c837zCtoFQ0206', '1//09cWY3KHuPARBCgYIARAAGAkSNwF-L9IrRoeQjLwiPkCPgFEsuq0JT-JVKi38slG3danWCFFADWBb7-4Cg5lyjHEqwBbkpFYT0mE', '2025-09-07 17:58:29', '2025-09-07 17:58:30', '2025-09-07 17:58:30'),
(4, 4, 'ya29.a0AS3H6NxnJzyV676ZVoXiJ4R8Vqua29fQImKGkYUlgsHpHVvXpvAjaTEnQzo0wLZMXDzHfv1ub9akQLU5zEyb3t8P8SM2TsLhkAOBXnocdMg-TelBzXCZ6BSEIqcLTnVg993cjhCiP5wDYnmq8nLVZQ0F81F7uSJgL0DXY623tEMilGIpTKGqg4HipyMVnW-3zI4bQmAaCgYKAf0SARESFQHGX2Miz09xTBSxiDLHwgnjDNWtuQ0206', '1//09tUhR5gSRYUbCgYIARAAGAkSNwF-L9Ir3LvP0wzo0IB3h2hk6Py2D0JKO-CgSeuIr8I_4UE50I0wzsAIqQnfvAj23LoRfxqZ9Ro', '2025-09-07 18:59:02', '2025-09-07 18:59:03', '2025-09-07 18:59:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lists`
--
ALTER TABLE `lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lists_user` (`user_id`);

--
-- Indexes for table `list_follows`
--
ALTER TABLE `list_follows`
  ADD PRIMARY KEY (`user_id`,`list_id`),
  ADD KEY `fk_follows_list` (`list_id`);

--
-- Indexes for table `list_items`
--
ALTER TABLE `list_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_list_content` (`list_id`,`content_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `streaming_content`
--
ALTER TABLE `streaming_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_video` (`youtube_video_id`),
  ADD KEY `added_by_user_id` (`added_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indexes for table `user_lists`
--
ALTER TABLE `user_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `youtube_credentials`
--
ALTER TABLE `youtube_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lists`
--
ALTER TABLE `lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `list_items`
--
ALTER TABLE `list_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `streaming_content`
--
ALTER TABLE `streaming_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_follows`
--
ALTER TABLE `user_follows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_lists`
--
ALTER TABLE `user_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `youtube_credentials`
--
ALTER TABLE `youtube_credentials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lists`
--
ALTER TABLE `lists`
  ADD CONSTRAINT `fk_lists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_follows`
--
ALTER TABLE `list_follows`
  ADD CONSTRAINT `fk_follows_list` FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_follows_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_items`
--
ALTER TABLE `list_items`
  ADD CONSTRAINT `list_items_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `user_lists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `list_items_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `streaming_content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `streaming_content`
--
ALTER TABLE `streaming_content`
  ADD CONSTRAINT `streaming_content_ibfk_1` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD CONSTRAINT `user_follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_lists`
--
ALTER TABLE `user_lists`
  ADD CONSTRAINT `user_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `youtube_credentials`
--
ALTER TABLE `youtube_credentials`
  ADD CONSTRAINT `youtube_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
