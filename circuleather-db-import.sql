-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Sep 28, 2026 at 09:03 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `Ontvangst`
--

CREATE TABLE `Ontvangst` (
  `ID` int(11) NOT NULL,
  `herkomst` varchar(255) NOT NULL,
  `datum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Ontvangst`
--
ALTER TABLE `Ontvangst`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voorraad`
--
ALTER TABLE `voorraad`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

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
