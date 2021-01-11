-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2020-07-30 21:10:59
-- サーバのバージョン： 8.0.20
-- PHP のバージョン: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `artwork`
--
CREATE DATABASE IF NOT EXISTS `artwork` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `artwork`;

-- --------------------------------------------------------

--
-- テーブルの構造 `artwork`
--

CREATE TABLE `artwork` (
  `id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `tag` varchar(256) DEFAULT NULL,
  `comment` text,
  `img` varchar(32) NOT NULL,
  `last_update` date NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- テーブルのデータのダンプ `artwork`
--

INSERT INTO `artwork` (`id`, `name`, `tag`, `comment`, `img`, `last_update`, `deleted`) VALUES
(41, 'The Titan\'s Goblet', 'Thomas Cole,drawing,American,1833,表面,Luman Reed,John M. Falconer', '絵画の表面。メトロポリタン美術館所蔵のもの。', '5f17bf96a0e5a.jpg', '2020-07-22', 0),
(42, 'The Titan’s Goblet', 'Thomas Cole,drawing,American,1833,裏面,Luman Reed,John M. Falconer', '絵画の裏面。メトロポリタン美術館所蔵のもの。', '5f17f147f06eb.jpg', '2020-07-22', 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `damage`
--

CREATE TABLE `damage` (
  `id` int NOT NULL,
  `artwork_id` int NOT NULL,
  `type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `adddate` date NOT NULL,
  `deldate` date NOT NULL DEFAULT '0000-00-00',
  `color` varchar(8) NOT NULL,
  `shape_id` int NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `radius` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- テーブルのデータのダンプ `damage`
--

INSERT INTO `damage` (`id`, `artwork_id`, `type`, `comment`, `adddate`, `deldate`, `color`, `shape_id`, `x`, `y`, `radius`) VALUES
(110, 41, '傷', '白い。擦り傷？ヤケ？', '2020-07-22', '0000-00-00', '#f406c0', 1, 1938.87, 2041.78, 0),
(112, 41, '傷', '白い。擦り傷？ヤケ？絵具の一部？', '2019-07-22', '2020-06-22', '#ff00ff', 1, 1452.63, 1852.93, 0),
(114, 41, '傷', '白い。擦り傷？汚れ？ヤケ？絵具の一部？', '2018-07-22', '2020-06-22', '#ff00ff', 1, 1391.33, 1791.08, 0),
(115, 42, '筆跡', '(?)#04.29.1との記述あり', '2020-07-22', '2020-07-22', '#00ffff', 2, 3038.87, 2501.82, 0),
(117, 42, 'キズ', '', '2020-07-22', '0000-00-00', '#0000ff', 4, 3293.85, 3802.36, 0),
(119, 42, 'カビ', '', '2020-07-22', '0000-00-00', '#ffff00', 3, 332.194, 146.529, 0),
(120, 42, 'カビ', '', '2018-07-22', '2019-07-22', '#ffff00', 3, 3267.72, 2119.11, 0),
(122, 42, 'キズ', '大きめ。要経過観察', '2018-07-22', '0000-00-00', '#0000ff', 4, 105.25, 1769.38, 202);

-- --------------------------------------------------------

--
-- テーブルの構造 `damage_img`
--

CREATE TABLE `damage_img` (
  `id` int NOT NULL,
  `damage_id` int NOT NULL,
  `img` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- テーブルのデータのダンプ `damage_img`
--

INSERT INTO `damage_img` (`id`, `damage_id`, `img`) VALUES
(52, 115, '5f1803e18fdf4.jpg');

-- --------------------------------------------------------

--
-- テーブルの構造 `shape`
--

CREATE TABLE `shape` (
  `id` int NOT NULL,
  `name` varchar(32) NOT NULL,
  `src` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- テーブルのデータのダンプ `shape`
--

INSERT INTO `shape` (`id`, `name`, `src`) VALUES
(1, 'circle', 'shapes_01.png'),
(2, 'square', 'shapes_02.png'),
(3, 'cross', 'shapes_03.png'),
(4, 'heart', 'shapes_04.png'),
(5, 'triangle', 'shapes_05.png'),
(6, 'diamond', 'shapes_06.png'),
(7, 'pentagon', 'shapes_07.png'),
(8, 'hexagon', 'shapes_08.png'),
(9, 'right', 'shapes_09.png'),
(10, 'left', 'shapes_10.png'),
(11, 'up', 'shapes_11.png'),
(12, 'down', 'shapes_12.png'),
(13, 'vertical', 'shapes_13.png'),
(14, 'horizontal', 'shapes_14.png'),
(15, 'rightdown', 'shapes_15.png'),
(16, 'leftdown', 'shapes_16.png'),
(17, 'vertical-oval', 'shapes_17.png'),
(18, 'horizontal-oval', 'shapes_18.png'),
(19, 'rightdown-oval', 'shapes_19.png'),
(20, 'leftdown-oval', 'shapes_20.png');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `artwork`
--
ALTER TABLE `artwork`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `damage`
--
ALTER TABLE `damage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artwork_id` (`artwork_id`),
  ADD KEY `shape_id` (`shape_id`);

--
-- テーブルのインデックス `damage_img`
--
ALTER TABLE `damage_img`
  ADD PRIMARY KEY (`id`),
  ADD KEY `damage_id` (`damage_id`);

--
-- テーブルのインデックス `shape`
--
ALTER TABLE `shape`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルのAUTO_INCREMENT
--

--
-- テーブルのAUTO_INCREMENT `artwork`
--
ALTER TABLE `artwork`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- テーブルのAUTO_INCREMENT `damage`
--
ALTER TABLE `damage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- テーブルのAUTO_INCREMENT `damage_img`
--
ALTER TABLE `damage_img`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- テーブルのAUTO_INCREMENT `shape`
--
ALTER TABLE `shape`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `damage`
--
ALTER TABLE `damage`
  ADD CONSTRAINT `damage_ibfk_1` FOREIGN KEY (`artwork_id`) REFERENCES `artwork` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `damage_ibfk_2` FOREIGN KEY (`shape_id`) REFERENCES `shape` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- テーブルの制約 `damage_img`
--
ALTER TABLE `damage_img`
  ADD CONSTRAINT `damage_img_ibfk_1` FOREIGN KEY (`damage_id`) REFERENCES `damage` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
