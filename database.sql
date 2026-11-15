-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Окт 07 2022 г., 11:58
-- Server version: 5.7.36-cll-lve
-- PHP version: 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `a110259_build`
--

-- --------------------------------------------------------

--
-- Table structure for table `ban`
--

CREATE TABLE `ban` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `tipe` int(11) DEFAULT '0',
  `cause` int(11) DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  `adm` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `black_list`
--

CREATE TABLE `black_list` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `id_ank` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `time` int(11) NOT NULL,
  `text` varchar(5000) NOT NULL,
  `id_opp` int(11) DEFAULT '0',
  `del` int(11) DEFAULT '0',
  `id_clan` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `contacts_mail`
--

CREATE TABLE `contacts_mail` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `id_ank` int(11) DEFAULT '0',
  `new_msg` int(11) DEFAULT '1',
  `time` int(11) DEFAULT NULL,
  `del` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `inv`
--

CREATE TABLE `inv` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `set` int(11) DEFAULT '0' COMMENT 'Equipment Set ID',
  `name` varchar(300) NOT NULL COMMENT 'Item Name',
  `type` int(11) DEFAULT '0' COMMENT 'Item Slot Type',
  `power` int(11) DEFAULT '0' COMMENT 'Power Attribute',
  `block` int(11) DEFAULT '0',
  `health` int(11) DEFAULT '0',
  `energy` int(11) NOT NULL,
  `max_smitch` int(11) DEFAULT '0',
  `smitch` int(11) DEFAULT '0',
  `yes` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `rune` int(11) DEFAULT '0',
  `rune_param` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `set` int(11) DEFAULT '0' COMMENT 'Equipment Set ID',
  `name` varchar(300) NOT NULL COMMENT 'Item Name',
  `type` int(11) DEFAULT '0' COMMENT 'Item Slot Type',
  `gold` int(11) DEFAULT '0' COMMENT 'Gold / Rubies',
  `power` int(11) DEFAULT '0' COMMENT 'Power Attribute',
  `block` int(11) DEFAULT '0',
  `health` int(11) DEFAULT '0',
  `max_smitch` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `res` int(11) DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `set`, `name`, `type`, `gold`, `power`, `block`, `health`, `max_smitch`, `level`, `res`) VALUES
(1, 1, 'Recruit Helmet', 1, 0, 30, 30, 30, 10, 1, 10),
(2, 1, 'Recruit Shoulders', 2, 0, 30, 30, 30, 10, 1, 10),
(3, 1, 'Recruit Armor', 3, 0, 30, 30, 30, 10, 1, 10),
(4, 1, 'Recruit Gloves', 4, 0, 30, 30, 30, 10, 1, 10),
(5, 1, 'Recruit Sword', 5, 0, 30, 30, 30, 10, 1, 10),
(6, 1, 'Recruit Shield', 6, 0, 30, 30, 30, 10, 1, 10),
(7, 1, 'Recruit Pants', 7, 0, 30, 30, 30, 10, 1, 10),
(8, 1, 'Recruit Boots', 8, 0, 30, 30, 30, 10, 1, 10),
(9, 2, 'Warrior Helmet', 1, 0, 90, 90, 90, 20, 5, 45),
(10, 2, 'Warrior Shoulders', 2, 0, 90, 90, 90, 20, 5, 45),
(11, 2, 'Warrior Armor', 3, 0, 90, 90, 90, 20, 5, 45),
(12, 2, 'Warrior Gloves', 4, 0, 90, 90, 90, 20, 5, 45),
(13, 2, 'Warrior Sword', 5, 0, 90, 90, 90, 20, 5, 45),
(14, 2, 'Warrior Shield', 6, 0, 90, 90, 90, 20, 5, 45),
(15, 2, 'Warrior Pants', 7, 0, 90, 90, 90, 20, 5, 45),
(16, 2, 'Warrior Boots', 8, 0, 90, 90, 90, 20, 5, 45),
(17, 3, 'Knight Helmet', 1, 0, 270, 270, 270, 30, 10, 120),
(18, 3, 'Knight Shoulders', 2, 0, 270, 270, 270, 30, 10, 120),
(19, 3, 'Knight Armor', 3, 0, 270, 270, 270, 30, 10, 120),
(20, 3, 'Knight Gloves', 4, 0, 270, 270, 270, 30, 10, 120),
(21, 3, 'Knight Sword', 5, 0, 270, 270, 270, 30, 10, 120),
(22, 3, 'Knight Shield', 6, 0, 270, 270, 270, 30, 10, 120),
(23, 3, 'Knight Pants', 7, 0, 270, 270, 270, 30, 10, 120),
(24, 3, 'Knight Boots', 8, 0, 270, 270, 270, 30, 10, 120),
(25, 4, 'Chosen Helmet', 1, 0, 540, 540, 540, 40, 15, 250),
(26, 4, 'Chosen Shoulders', 2, 0, 540, 540, 540, 40, 15, 250),
(27, 4, 'Chosen Armor', 3, 0, 540, 540, 540, 40, 15, 250),
(28, 4, 'Chosen Gloves', 4, 0, 540, 540, 540, 40, 15, 250),
(29, 4, 'Chosen Sword', 5, 0, 540, 540, 540, 40, 15, 250),
(30, 4, 'Chosen Shield', 6, 0, 540, 540, 540, 40, 15, 250),
(31, 4, 'Chosen Pants', 7, 0, 540, 540, 540, 40, 15, 250),
(32, 4, 'Chosen Boots', 8, 0, 540, 540, 540, 40, 15, 250),
(33, 5, 'Legendary Helmet', 1, 5000, 980, 980, 980, 50, 20, 0),
(34, 5, 'Legendary Shoulders', 2, 5000, 980, 980, 980, 50, 20, 0),
(35, 5, 'Legendary Armor', 3, 5000, 980, 980, 980, 50, 20, 0),
(36, 5, 'Legendary Gloves', 4, 5000, 980, 980, 980, 50, 20, 0),
(37, 5, 'Legendary Sword', 5, 5000, 980, 980, 980, 50, 20, 0),
(38, 5, 'Legendary Shield', 6, 5000, 980, 980, 980, 50, 20, 0),
(39, 5, 'Legendary Pants', 7, 5000, 980, 980, 980, 50, 20, 0),
(40, 5, 'Legendary Boots', 8, 5000, 980, 980, 980, 50, 20, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lec_boss`
--

CREATE TABLE `lec_boss` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `param` varchar(11) DEFAULT '0',
  `level` varchar(11) DEFAULT '0',
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `lec_boss`
--

INSERT INTO `lec_boss` (`id`, `name`, `param`, `level`, `type`) VALUES
(1, 'Wild Ram', '40', '1', 'metal'),
(2, 'Giant Eagle', '60', '1', 'tkan'),
(3, 'Shadow Hyena', '80', '1', 'kamen'),
(4, 'Dire Wolf', '100', '1', 'swit'),
(5, 'Savage Lion', '120', '1', 'book'),
(6, 'Forest Serpent', '240', '2', 'metal'),
(7, 'Toxic Lizard', '320', '2', 'tkan'),
(8, 'Dread Spider', '440', '2', 'kamen'),
(9, 'Orc Marauder', '610', '2', 'swit'),
(10, 'Lesser Dragon', '780', '2', 'book'),
(11, 'Frost Dragon', '950', '3', 'metal'),
(12, 'Spider King', '1250', '3', 'tkan'),
(13, 'Half-Blood Demon', '1500', '3', 'kamen'),
(14, 'Abyssal Demon', '1700', '3', 'swit'),
(15, 'Undead Orc', '2000', '3', 'book'),
(16, 'Holy Knight: Adrian', '2500', '4', 'metal'),
(17, 'Holy Knight: Albert', '2700', '4', 'tkan'),
(18, 'Holy Knight: Luke', '3000', '4', 'kamen'),
(19, 'Holy Knight: Jack', '3500', '4', 'swit'),
(20, 'Holy Knight: Gunter', '4000', '4', 'book'),
(21, 'Undead Human', '4500', '5', 'metal'),
(22, 'Cursed Knight', '5000', '5', 'tkan'),
(23, 'Executioner', '5500', '5', 'kamen'),
(24, 'Temptor', '6000', '5', 'swit'),
(25, 'Lord of the Damned', '6500', '5', 'book');

-- --------------------------------------------------------

--
-- Table structure for table `lec_fight`
--

CREATE TABLE `lec_fight` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `id_bot` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `default2` int(11) DEFAULT '0',
  `default` int(11) DEFAULT '0',
  `bot_power` int(11) DEFAULT '0',
  `bot_block` int(11) DEFAULT '0',
  `bot_hp` int(11) DEFAULT '0',
  `power` int(11) DEFAULT '0',
  `block` int(11) DEFAULT '0',
  `hp` int(11) DEFAULT '0',
  `clon_hp` int(11) DEFAULT '0',
  `fight_time` int(11) DEFAULT NULL,
  `s2` int(11) DEFAULT '0',
  `s4` int(11) DEFAULT '0',
  `s5` int(11) DEFAULT '0',
  `s11` int(11) DEFAULT '0',
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lec_user`
--

CREATE TABLE `lec_user` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '1',
  `metal` int(11) NOT NULL DEFAULT '0',
  `tkan` int(11) NOT NULL DEFAULT '0',
  `kamen` int(11) NOT NULL DEFAULT '0',
  `swit` int(11) NOT NULL DEFAULT '0',
  `book` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE `mail` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `id_ank` int(11) DEFAULT '0',
  `text` varchar(5000) NOT NULL,
  `time` int(11) DEFAULT NULL,
  `check` int(11) DEFAULT '1',
  `del_user` int(11) DEFAULT '0',
  `del_ank` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `text` varchar(5000) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `like` int(11) DEFAULT '0',
  `dislike` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `name`, `text`, `time`, `like`, `dislike`) VALUES
(1, 'Welcome to Kingdom of Lyonesse!', 'Welcome brave heroes to the realm of Lyonesse! Gear up, explore the Dark Forest, challenge rivals in the PvP Arena, and rise through the Hall of Fame!', 1690000000, 0, 0),
(2, 'Grand Opening & Server Launch', 'The server is now live with enhanced RPG features, new abilities, forging systems, and weekly Royal Tournaments!', 1690100000, 0, 0);


-- --------------------------------------------------------

--
-- Table structure for table `quest`
--

CREATE TABLE `quest` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `koll` int(11) NOT NULL DEFAULT '0',
  `gold` int(11) NOT NULL DEFAULT '0',
  `silver` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `quest`
--

INSERT INTO `quest` (`id`, `name`, `koll`, `gold`, `silver`) VALUES
(1, 'Buy 100 Rubies', 100, 50, 5000),
(2, 'Buy 1000 Rubies', 1000, 500, 30000),
(3, 'Mine Iron in the Dark Forest', 5, 3, 2200),
(4, 'Gather Cloth in the Dark Forest', 5, 3, 2000),
(5, 'Gather Stone in the Dark Forest', 5, 3, 1900),
(6, 'Find Scrolls in the Dark Forest', 5, 3, 2100),
(7, 'Fight Battles in the PvP Arena', 100, 5, 4000),
(8, 'Win Battles in the PvP Arena', 50, 6, 3800);

-- --------------------------------------------------------

--
-- Table structure for table `quest_user`
--

CREATE TABLE `quest_user` (
  `id` int(11) NOT NULL,
  `id_quest` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `koll` int(11) NOT NULL DEFAULT '0',
  `max_koll` int(11) NOT NULL,
  `last` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `param` int(11) DEFAULT '0',
  `time_max` int(11) DEFAULT '0',
  `reset_max` int(11) DEFAULT '0',
  `reset` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `fight` int(11) DEFAULT '0',
  `fight_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `skills_fight`
--

CREATE TABLE `skills_fight` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `id_skills` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tavern`
--

CREATE TABLE `tavern` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '1',
  `id_quest` int(11) DEFAULT '1',
  `glava` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tavern_fight`
--

CREATE TABLE `tavern_fight` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `bot_power` int(11) DEFAULT '0',
  `bot_block` int(11) DEFAULT '0',
  `bot_hp` int(11) DEFAULT '0',
  `power` int(11) DEFAULT '0',
  `block` int(11) DEFAULT '0',
  `hp` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(300) NOT NULL,
  `password` varchar(300) NOT NULL,
  `hash_password` varchar(300) NOT NULL,
  `email` varchar(300) NOT NULL,
  `time_reg` int(11) NOT NULL,
  `time_on` int(11) NOT NULL,
  `ip` varchar(300) NOT NULL,
  `class` varchar(30) NOT NULL COMMENT 'Hero Class',
  `sex` varchar(30) NOT NULL COMMENT 'Gender (1=Male, 2=Female)',
  `gold` int(11) NOT NULL DEFAULT '0' COMMENT 'Rubies',
  `silver` int(11) NOT NULL DEFAULT '0' COMMENT 'Coins',
  `adm` int(11) DEFAULT '0',
  `ref` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '1',
  `exp` int(11) DEFAULT '0',
  `exp_` int(11) DEFAULT '0',
  `health` int(11) DEFAULT '10',
  `power` int(11) DEFAULT '10',
  `block` int(11) DEFAULT '10',
  `login_settings` int(11) DEFAULT '0',
  `spam_message` int(11) DEFAULT NULL,
  `chat_check` int(11) DEFAULT '0',
  `spam_message_forum` int(11) DEFAULT NULL,
  `news` int(11) DEFAULT '0',
  `save` int(11) DEFAULT '0',
  `bonus` int(11) DEFAULT '0',
  `limit_inv` int(11) DEFAULT '30',
  `premium` int(11) DEFAULT '1',
  `premium_time` int(11) DEFAULT NULL,
  `start` int(11) DEFAULT '1',
  `ads` int(11) DEFAULT '0',
  `avatar` int(11) DEFAULT '1',
  `metal` int(11) DEFAULT '0',
  `tkan` int(11) DEFAULT '0',
  `kamen` int(11) DEFAULT '0',
  `swit` int(11) DEFAULT '0',
  `book` int(11) DEFAULT '0',
  `kill` int(11) DEFAULT '0',
  `medik` int(11) DEFAULT '0',
  `mag` int(11) DEFAULT '0',
  `skills` int(11) DEFAULT '1',
  `skills_check` int(11) DEFAULT '0',
  `achi_check` int(11) DEFAULT '0',
  `key` int(11) DEFAULT '0',
  `key2` int(11) DEFAULT '0',
  `sun1` int(11) DEFAULT '0',
  `sun2` int(11) DEFAULT '0',
  `tour` int(11) DEFAULT '0',
  `arena` int(11) DEFAULT '20',
  `lec` int(11) DEFAULT '100',
  `arena_tav` int(11) DEFAULT '0',
  `skills_invise` int(11) DEFAULT '0',
  `ref_ruby` int(11) DEFAULT '0',
  `id_clan` int(11) NOT NULL DEFAULT '0',
  `daily_streak` int(11) NOT NULL DEFAULT '0',
  `daily_last_claim` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '',
  `kills_boss` int(11) NOT NULL DEFAULT '0',
  `kills_pvp` int(11) NOT NULL DEFAULT '0',
  `forged_items` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `ban`
--
ALTER TABLE `ban`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `black_list`
--
ALTER TABLE `black_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts_mail`
--
ALTER TABLE `contacts_mail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inv`
--
ALTER TABLE `inv`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lec_boss`
--
ALTER TABLE `lec_boss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lec_fight`
--
ALTER TABLE `lec_fight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lec_user`
--
ALTER TABLE `lec_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quest`
--
ALTER TABLE `quest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quest_user`
--
ALTER TABLE `quest_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills_fight`
--
ALTER TABLE `skills_fight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tavern`
--
ALTER TABLE `tavern`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tavern_fight`
--
ALTER TABLE `tavern_fight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ban`
--
ALTER TABLE `ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `black_list`
--
ALTER TABLE `black_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts_mail`
--
ALTER TABLE `contacts_mail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inv`
--
ALTER TABLE `inv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `lec_boss`
--
ALTER TABLE `lec_boss`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `lec_fight`
--
ALTER TABLE `lec_fight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lec_user`
--
ALTER TABLE `lec_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quest`
--
ALTER TABLE `quest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quest_user`
--
ALTER TABLE `quest_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills_fight`
--
ALTER TABLE `skills_fight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tavern`
--
ALTER TABLE `tavern`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tavern_fight`
--
ALTER TABLE `tavern_fight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Table structure for table `clans`
--

CREATE TABLE IF NOT EXISTS `clans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `leader_id` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `exp` int(11) NOT NULL DEFAULT '0',
  `gold` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `leader_id` (`leader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `clan_members`
--

CREATE TABLE IF NOT EXISTS `clan_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '1',
  `joined_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `clan_id` (`clan_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `clan_boss`
--

CREATE TABLE IF NOT EXISTS `clan_boss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` int(11) NOT NULL,
  `boss_name` varchar(50) NOT NULL,
  `hp` int(11) NOT NULL,
  `max_hp` int(11) NOT NULL,
  `reward_gold` int(11) NOT NULL DEFAULT '1000',
  `reward_exp` int(11) NOT NULL DEFAULT '500',
  `last_attack` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `clan_id` (`clan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `achievements_user`
--

CREATE TABLE IF NOT EXISTS `achievements_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_id` varchar(50) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT '0',
  `unlocked` tinyint(1) NOT NULL DEFAULT '0',
  `unlocked_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `achievement_id` (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `arena_challenges`
--

CREATE TABLE IF NOT EXISTS `arena_challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challenger_id` int(11) NOT NULL,
  `opponent_id` int(11) NOT NULL,
  `challenger_stats` text NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `winner_id` int(11) NOT NULL DEFAULT '0',
  `battle_log` text NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `resolved_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `opponent_status` (`opponent_id`, `status`),
  KEY `challenger_status` (`challenger_id`, `status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `site_settings`
--

CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `paypal_orders`
--

CREATE TABLE IF NOT EXISTS `paypal_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `package_id` tinyint(3) unsigned NOT NULL,
  `rubies` int(11) NOT NULL,
  `vip_days` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `paypal_order_id` varchar(64) DEFAULT NULL,
  `paypal_capture_id` varchar(64) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `created_at` int(11) NOT NULL,
  `captured_at` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_order_id` (`paypal_order_id`),
  UNIQUE KEY `paypal_capture_id` (`paypal_capture_id`),
  KEY `user_id_status` (`user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
