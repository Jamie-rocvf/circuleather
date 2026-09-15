-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Sep 15, 2026 at 07:45 AM
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
  `voorraad.ID` int(11) NOT NULL,
  `locatie` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `prijs` float(11,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `besteldatum` date NOT NULL,
  `verstuurdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bestellingen`
--
ALTER TABLE `bestellingen`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `placeholder` (`voorraad.ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bestellingen`
--
ALTER TABLE `bestellingen`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bestellingen`
--
ALTER TABLE `bestellingen`
  ADD CONSTRAINT `1` FOREIGN KEY (`voorraad.ID`) REFERENCES `voorraad` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
