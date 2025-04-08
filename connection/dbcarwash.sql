-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 05:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbcarwash`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Email`, `Password`, `ProfilePicture`) VALUES
(1, 'carwash@gmail.com', 'carwash', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `BookingID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ServiceID` int(11) NOT NULL,
  `BookingDate` datetime DEFAULT current_timestamp(),
  `StatusID` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`BookingID`, `CustomerID`, `ServiceID`, `BookingDate`, `StatusID`) VALUES
(1, 6, 2, '2025-04-04 09:55:33', 1),
(3, 5, 3, '2025-04-05 11:15:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `EmailAddress` varchar(100) NOT NULL,
  `Age` int(11) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `ConfirmPassword` varchar(255) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `BookingDate` datetime DEFAULT NULL,
  `StatusID` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `Username`, `EmailAddress`, `Age`, `Password`, `ConfirmPassword`, `ProfilePicture`, `ServiceID`, `BookingDate`, `StatusID`) VALUES
(3, 'carwashboy', 'boy@gmail.com', 898989, '$2y$10$Z0WsRDFjENGDiyUIJCLcte38a4hS4MkJkDR.9YIRmIq2XprRWpP86', '', NULL, NULL, '2025-03-05 09:53:00', 2),
(5, 'jake', 'jake@gmail.com', 21, '$2y$10$xXfY7cF6uIr/z9Vgehpb7ecbI51bLmDUrXTc3J9qIC6MSroYPsd/6', '$2y$10$xXfY7cF6uIr/z9Vgehpb7ecbI51bLmDUrXTc3J9qIC6MSroYPsd/6', NULL, NULL, NULL, 1),
(6, '', '', NULL, '$2y$10$nL7klza5ui7ayDR42gO9au2eqXc4n9lnllz2rbFaQgqhGhMlyH7Gq', '', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Ratings` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `ReportID` int(11) NOT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `Generated_Date` datetime NOT NULL,
  `Details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `ServiceID` int(11) NOT NULL,
  `ServiceName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`ServiceID`, `ServiceName`, `Description`) VALUES
(2, 'Waxing', 'Professional car waxing'),
(3, 'Engine Detailing', 'Detailed cleaning of engine bay'),
(4, 'kqdkmqlkm', 'kwdkkajdka\r\n\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `StatusID` int(11) NOT NULL,
  `StatusName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`StatusID`, `StatusName`) VALUES
(1, 'Pending'),
(2, 'Completed'),
(3, 'Cancelled');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`BookingID`),
  ADD UNIQUE KEY `unique_customer_service` (`CustomerID`,`ServiceID`),
  ADD KEY `ServiceID` (`ServiceID`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD KEY `ServiceID` (`ServiceID`),
  ADD KEY `fk_customer_status` (`StatusID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`ServiceID`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`StatusID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `StatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`StatusID`) REFERENCES `status` (`StatusID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_customer_status` FOREIGN KEY (`StatusID`) REFERENCES `status` (`StatusID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
