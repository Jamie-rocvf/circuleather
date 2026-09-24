-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Sep 23, 2026 at 07:18 AM
-- Server version: 12.2.2-MariaDB-ubu2404
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `circuleather`
--

-- --------------------------------------------------------

--
-- Table structure for table `bestellingen`
--

CREATE TABLE `bestellingen` (
  `ID` int(11) NOT NULL,
  `locatie` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `besteldatum` date NOT NULL,
  `verstuurdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `bestellingen`
--

INSERT INTO `bestellingen` (`ID`, `locatie`, `email`, `status`, `besteldatum`, `verstuurdatum`) VALUES
(1, 'Amsterdam', 'jan@example.com', 'In behandeling', '2026-09-23', NULL),
(2, 'Utrecht', 'lisa@example.com', 'Verzonden', '2026-09-23', '2026-09-23');

-- --------------------------------------------------------

--
-- Table structure for table `bestelling_items`
--

CREATE TABLE `bestelling_items` (
  `bestelling_id` int(11) NOT NULL,
  `voorraad_id` int(11) NOT NULL,
  `aantal` int(11) NOT NULL DEFAULT 1,
  `prijs` float(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `bestelling_items`
--

INSERT INTO `bestelling_items` (`bestelling_id`, `voorraad_id`, `aantal`, `prijs`) VALUES
(1, 101, 2, 19.99),
(1, 105, 1, 49.50),
(2, 102, 1, 89.00);

-- --------------------------------------------------------

--
-- Table structure for table `gebruikers`
--

CREATE TABLE `gebruikers` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mag_insert` tinyint(1) NOT NULL DEFAULT 0,
  `mag_orders` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `gebruikers`
--

INSERT INTO `gebruikers` (`id`, `username`, `password`, `mag_insert`, `mag_orders`) VALUES
(1, 'jamie', '$2y$10$89A/5WRsBqH0NEZ2g0OMY.Erye0jhnyZDA8IQbJKyytn/Tv0dDXuG', 1, 1),
(2, 'uitpakker', '$2y$10$m5vHpo9fpl6xndCs5uyOPesKbGI6zgmjdJX/AAZO9h0m3.IXPm30K', 1, 0),
(3, 'inpakker', '$2y$10$8Jim2zjKtnCVIt.l9PLsBe9C7CYf5C2VrKdMb.deAELJ.HtTF2MBC', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Ontvangst`
--

CREATE TABLE `Ontvangst` (
  `ID` int(11) NOT NULL,
  `herkomst` varchar(255) NOT NULL,
  `datum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Ontvangst`
--

INSERT INTO `Ontvangst` (`ID`, `herkomst`, `datum`) VALUES
(1, 'Amsterdam', '2026-09-22');

-- --------------------------------------------------------

--
-- Table structure for table `ontvangst_items`
--

CREATE TABLE `ontvangst_items` (
  `ontvangst_id` int(11) NOT NULL,
  `voorraad_id` int(11) NOT NULL,
  `gewichtG` float(11,1) NOT NULL,
  `bruikbaarheid` float(11,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `ontvangst_items`
--

INSERT INTO `ontvangst_items` (`ontvangst_id`, `voorraad_id`, `gewichtG`, `bruikbaarheid`) VALUES
(1, 202, 1500.0, 0.0);

-- --------------------------------------------------------

--
-- Table structure for table `voorraad`
--

CREATE TABLE `voorraad` (
  `ID` int(11) NOT NULL,
  `leertype` varchar(255) NOT NULL,
  `dikteMM` int(11) NOT NULL,
  `lengteCM` int(11) NOT NULL,
  `breedteCM` int(11) NOT NULL,
  `gewichtG` float(11,1) NOT NULL,
  `kleur` varchar(255) NOT NULL,
  `prijs` float(11,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'beschikbaar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `voorraad`
--

INSERT INTO `voorraad` (`ID`, `leertype`, `dikteMM`, `lengteCM`, `breedteCM`, `gewichtG`, `kleur`, `prijs`, `status`) VALUES
(101, 'schaap', 6, 30, 27, 2358.8, 'bruin', 95.53, 'besteld'),
(102, 'koe', 2, 23, 29, 654.0, 'zwart', 25.18, 'besteld'),
(103, 'koe', 6, 48, 32, 4632.9, 'wit', 122.41, 'beschikbaar'),
(104, 'paard', 3, 106, 81, 14028.4, 'roze', 392.41, 'beschikbaar'),
(105, 'paard', 2, 41, 37, 1659.7, 'roze', 64.37, 'besteld'),
(106, 'kalf', 2, 29, 31, 875.1, 'cognac', 28.41, 'beschikbaar'),
(107, 'varken', 4, 30, 33, 2000.6, 'wit', 56.97, 'beschikbaar'),
(108, 'hert', 2, 96, 63, 5441.3, 'zwart', 236.62, 'beschikbaar'),
(109, 'geit', 6, 48, 50, 6190.3, 'grijs', 206.05, 'beschikbaar'),
(110, 'schaap', 1, 28, 36, 430.4, 'bruin', 21.71, 'beschikbaar'),
(111, 'koe', 6, 108, 57, 15440.5, 'zwart', 532.91, 'beschikbaar'),
(112, 'hert', 2, 43, 42, 1630.9, 'camel', 53.34, 'beschikbaar'),
(113, 'koe', 3, 43, 46, 3063.9, 'roze', 88.15, 'beschikbaar'),
(114, 'schaap', 6, 42, 57, 5396.2, 'zwart', 232.28, 'beschikbaar'),
(115, 'buffel', 5, 104, 48, 10727.8, 'grijs', 437.88, 'beschikbaar'),
(116, 'kalf', 2, 33, 23, 601.2, 'zwart', 23.32, 'beschikbaar'),
(117, 'varken', 1, 64, 88, 2724.7, 'zwart', 80.03, 'beschikbaar'),
(118, 'kalf', 4, 72, 35, 4397.0, 'grijs', 159.92, 'beschikbaar'),
(119, 'schaap', 4, 43, 38, 2988.6, 'bruin', 88.86, 'beschikbaar'),
(120, 'schaap', 5, 23, 25, 1494.3, 'rood', 43.24, 'beschikbaar'),
(121, 'buffel', 2, 35, 31, 1100.2, 'bruin', 37.17, 'beschikbaar'),
(122, 'kalf', 2, 24, 24, 416.4, 'wit', 24.22, 'beschikbaar'),
(123, 'geit', 5, 28, 25, 1697.6, 'bruin', 51.31, 'beschikbaar'),
(124, 'varken', 5, 25, 36, 2083.7, 'roze', 68.95, 'beschikbaar'),
(125, 'paard', 3, 32, 37, 1777.3, 'grijs', 49.44, 'beschikbaar'),
(126, 'schaap', 5, 31, 27, 2202.0, 'zwart', 83.88, 'beschikbaar'),
(127, 'hert', 6, 57, 42, 5325.0, 'roze', 276.86, 'beschikbaar'),
(128, 'geit', 3, 50, 36, 2811.1, 'beige', 90.14, 'beschikbaar'),
(129, 'koe', 6, 48, 25, 3630.4, 'zwart', 126.67, 'beschikbaar'),
(130, 'buffel', 1, 27, 24, 302.3, 'camel', 11.31, 'beschikbaar'),
(131, 'koe', 2, 43, 45, 2038.8, 'cognac', 64.75, 'beschikbaar'),
(132, 'varken', 2, 61, 45, 2286.2, 'wit', 113.57, 'beschikbaar'),
(133, 'varken', 2, 66, 71, 4889.5, 'beige', 136.04, 'beschikbaar'),
(134, 'hert', 2, 29, 35, 1061.4, 'grijs', 43.74, 'beschikbaar'),
(135, 'buffel', 3, 67, 56, 6106.5, 'camel', 161.63, 'beschikbaar'),
(136, 'paard', 5, 67, 72, 9358.9, 'donkerbruin', 302.62, 'beschikbaar'),
(137, 'varken', 4, 33, 33, 1633.2, 'cognac', 88.68, 'beschikbaar'),
(138, 'paard', 6, 27, 29, 2527.9, 'donkerbruin', 89.25, 'beschikbaar'),
(139, 'hert', 5, 79, 59, 11819.2, 'donkerbruin', 396.70, 'beschikbaar'),
(140, 'varken', 4, 110, 44, 7874.8, 'camel', 340.85, 'beschikbaar'),
(141, 'varken', 2, 69, 26, 1961.9, 'beige', 67.21, 'beschikbaar'),
(142, 'varken', 4, 27, 23, 1296.9, 'donkerbruin', 54.07, 'beschikbaar'),
(143, 'kalf', 5, 26, 37, 2130.5, 'bruin', 83.51, 'beschikbaar'),
(144, 'kalf', 6, 87, 80, 20827.0, 'roze', 663.25, 'beschikbaar'),
(145, 'hert', 4, 51, 48, 3986.8, 'camel', 142.71, 'beschikbaar'),
(146, 'geit', 2, 29, 27, 675.8, 'wit', 30.08, 'beschikbaar'),
(147, 'buffel', 5, 61, 71, 11662.3, 'donkerbruin', 315.93, 'beschikbaar'),
(148, 'buffel', 6, 59, 37, 5299.3, 'camel', 212.40, 'beschikbaar'),
(149, 'buffel', 4, 99, 26, 4822.4, 'wit', 180.67, 'beschikbaar'),
(150, 'kalf', 1, 33, 29, 399.5, 'wit', 21.89, 'beschikbaar'),
(151, 'buffel', 1, 57, 26, 622.4, 'beige', 32.18, 'beschikbaar'),
(152, 'koe', 2, 99, 42, 3698.2, 'grijs', 115.09, 'beschikbaar'),
(153, 'hert', 2, 43, 33, 1054.7, 'cognac', 38.51, 'beschikbaar'),
(154, 'buffel', 6, 30, 26, 1920.3, 'donkerbruin', 88.61, 'beschikbaar'),
(155, 'koe', 5, 51, 27, 2879.6, 'cognac', 136.35, 'beschikbaar'),
(156, 'paard', 6, 28, 39, 2647.1, 'rood', 126.22, 'beschikbaar'),
(157, 'buffel', 3, 30, 25, 990.4, 'roze', 45.93, 'beschikbaar'),
(158, 'paard', 4, 71, 85, 12300.9, 'bruin', 362.69, 'beschikbaar'),
(159, 'hert', 1, 46, 28, 555.5, 'camel', 24.62, 'beschikbaar'),
(160, 'kalf', 1, 30, 35, 431.8, 'rood', 22.32, 'beschikbaar'),
(161, 'buffel', 3, 37, 31, 1363.0, 'camel', 65.04, 'beschikbaar'),
(162, 'geit', 2, 48, 56, 2935.8, 'grijs', 104.10, 'beschikbaar'),
(163, 'hert', 6, 48, 25, 3316.8, 'bruin', 129.33, 'beschikbaar'),
(164, 'kalf', 1, 55, 53, 1127.7, 'zwart', 43.82, 'beschikbaar'),
(165, 'schaap', 4, 24, 27, 1199.0, 'donkerbruin', 45.37, 'beschikbaar'),
(166, 'buffel', 5, 74, 89, 17508.9, 'roze', 479.03, 'beschikbaar'),
(167, 'koe', 6, 29, 29, 1848.0, 'roze', 75.55, 'beschikbaar'),
(168, 'buffel', 6, 49, 25, 3611.7, 'rood', 134.26, 'beschikbaar'),
(169, 'hert', 6, 72, 77, 13138.0, 'roze', 442.41, 'beschikbaar'),
(170, 'koe', 3, 58, 41, 3165.6, 'wit', 107.38, 'beschikbaar'),
(171, 'kalf', 1, 53, 43, 809.4, 'rood', 37.57, 'beschikbaar'),
(172, 'koe', 5, 28, 38, 2332.5, 'beige', 83.66, 'beschikbaar'),
(173, 'kalf', 4, 33, 33, 2271.9, 'zwart', 69.93, 'beschikbaar'),
(174, 'buffel', 1, 50, 39, 1060.9, 'camel', 34.79, 'beschikbaar'),
(175, 'kalf', 1, 34, 38, 648.6, 'donkerbruin', 22.90, 'beschikbaar'),
(176, 'kalf', 1, 30, 28, 296.3, 'rood', 16.17, 'beschikbaar'),
(177, 'schaap', 1, 44, 54, 970.3, 'rood', 46.41, 'beschikbaar'),
(178, 'kalf', 4, 52, 35, 3288.0, 'grijs', 145.76, 'beschikbaar'),
(179, 'buffel', 5, 23, 32, 1904.4, 'cognac', 67.19, 'beschikbaar'),
(180, 'kalf', 4, 35, 37, 2713.9, 'camel', 106.24, 'beschikbaar'),
(181, 'buffel', 3, 52, 47, 3728.2, 'bruin', 129.14, 'beschikbaar'),
(182, 'geit', 5, 37, 26, 2559.3, 'camel', 61.18, 'beschikbaar'),
(183, 'geit', 4, 76, 66, 8617.4, 'zwart', 257.63, 'beschikbaar'),
(184, 'buffel', 6, 57, 55, 6840.9, 'cognac', 328.22, 'beschikbaar'),
(185, 'schaap', 1, 105, 35, 1796.3, 'donkerbruin', 72.94, 'beschikbaar'),
(186, 'hert', 3, 47, 56, 3840.3, 'cognac', 109.18, 'beschikbaar'),
(187, 'buffel', 6, 27, 30, 1947.9, 'roze', 70.30, 'beschikbaar'),
(188, 'hert', 2, 34, 32, 946.3, 'camel', 37.54, 'beschikbaar'),
(189, 'buffel', 1, 34, 32, 445.0, 'rood', 27.52, 'beschikbaar'),
(190, 'koe', 6, 49, 37, 4573.8, 'roze', 191.66, 'beschikbaar'),
(191, 'geit', 6, 51, 49, 6342.0, 'zwart', 203.15, 'beschikbaar'),
(192, 'buffel', 2, 34, 39, 1072.7, 'wit', 42.80, 'beschikbaar'),
(193, 'hert', 1, 42, 32, 531.0, 'cognac', 29.45, 'beschikbaar'),
(194, 'paard', 4, 26, 37, 1923.2, 'zwart', 58.53, 'beschikbaar'),
(195, 'paard', 6, 23, 33, 1795.5, 'zwart', 63.89, 'beschikbaar'),
(196, 'hert', 1, 24, 32, 286.6, 'donkerbruin', 15.88, 'beschikbaar'),
(197, 'paard', 4, 44, 49, 4456.4, 'camel', 153.60, 'beschikbaar'),
(198, 'paard', 4, 47, 46, 4212.8, 'grijs', 149.31, 'beschikbaar'),
(199, 'buffel', 2, 120, 81, 7628.7, 'beige', 340.56, 'beschikbaar'),
(200, 'paard', 2, 73, 31, 2085.8, 'grijs', 82.42, 'beschikbaar'),
(201, 'aap', 6, 30, 27, 2358.8, 'pinky pink', 95.53, 'beschikbaar'),
(202, 'koe', 3, 39, 40, 1500.0, 'rood', 90.00, 'beschikbaar');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bestellingen`
--
ALTER TABLE `bestellingen`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `bestelling_items`
--
ALTER TABLE `bestelling_items`
  ADD KEY `bestelling.ID` (`bestelling_id`),
  ADD KEY `voorraad.ID` (`voorraad_id`);

--
-- Indexes for table `gebruikers`
--
ALTER TABLE `gebruikers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `Ontvangst`
--
ALTER TABLE `Ontvangst`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `ontvangst_items`
--
ALTER TABLE `ontvangst_items`
  ADD KEY `ontvangst.ID` (`ontvangst_id`),
  ADD KEY `voorraad.ID` (`voorraad_id`);

--
-- Indexes for table `voorraad`
--
ALTER TABLE `voorraad`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bestellingen`
--
ALTER TABLE `bestellingen`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Ontvangst`
--
ALTER TABLE `Ontvangst`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `voorraad`
--
ALTER TABLE `voorraad`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bestelling_items`
--
ALTER TABLE `bestelling_items`
  ADD CONSTRAINT `1` FOREIGN KEY (`bestelling_id`) REFERENCES `bestellingen` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`voorraad_id`) REFERENCES `voorraad` (`ID`);

--
-- Constraints for table `ontvangst_items`
--
ALTER TABLE `ontvangst_items`
  ADD CONSTRAINT `1` FOREIGN KEY (`ontvangst_id`) REFERENCES `Ontvangst` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`voorraad_id`) REFERENCES `voorraad` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
