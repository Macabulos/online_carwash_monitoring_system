-- phpMyAdmin SQL Dump
-- Version 5.2.1
-- Host: 127.0.0.1
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop existing tables to avoid conflicts
DROP TABLE IF EXISTS `admin`, `customer`, `feedback`, `report`, `service`;

-- --------------------------------------------------------
-- Table structure for `admin`
-- --------------------------------------------------------

CREATE TABLE `admin` (
  `AdminID` INT(11) NOT NULL AUTO_INCREMENT,
  `Email` VARCHAR(100) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `ProfilePicture` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`AdminID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` (`AdminID`, `Email`, `Password`, `ProfilePicture`) VALUES
(1, 'carwash@gmail.com', 'carwash', NULL);

-- --------------------------------------------------------
-- Table structure for `service`
-- --------------------------------------------------------

CREATE TABLE `service` (
  `ServiceID` INT(11) NOT NULL AUTO_INCREMENT,
  `ServiceName` VARCHAR(100) NOT NULL,
  `Description` TEXT DEFAULT NULL,
  PRIMARY KEY (`ServiceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `service` (`ServiceID`, `ServiceName`, `Description`) VALUES
(1, 'Car Wash', 'Complete exterior and interior cleaning'),
(2, 'Waxing', 'Professional car waxing'),
(3, 'Engine Detailing', 'Detailed cleaning of engine bay');

-- --------------------------------------------------------
-- Table structure for `customer` (Includes booking data)
-- --------------------------------------------------------

CREATE TABLE `customer` (
  `CustomerID` INT(11) NOT NULL AUTO_INCREMENT,
  `Username` VARCHAR(50) NOT NULL,
  `EmailAddress` VARCHAR(100) NOT NULL,
  `Age` INT(11) DEFAULT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `ConfirmPassword` VARCHAR(255) NOT NULL,
  `ProfilePicture` VARCHAR(255) DEFAULT NULL,
  `ServiceID` INT(11) DEFAULT NULL,
  `BookingDate` DATETIME DEFAULT NULL,
  `Status` VARCHAR(20) DEFAULT 'Pending',
  PRIMARY KEY (`CustomerID`),
  FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customer` (`CustomerID`, `Username`, `EmailAddress`, `Age`, `Password`, `ConfirmPassword`, `ProfilePicture`, `ServiceID`, `BookingDate`, `Status`) VALUES
(1, 'jake', 'jake@gmail.com', 21, '$2y$10$AOFVJRug9V9RJOUVpnWrveo1GcB2fDVlHeIBYwVz7kDrIXxqcyYC.', '', NULL, 1, '2025-03-24 13:28:00', 'Pending'),
(2, 'jl', 'jl@gmail.com', 21, '$2y$10$n.wAYHHHJprjRtjzjxYAUOqZE2OJkYAgUSVyd/yLedXJ4ZxsL6ct2', '', NULL, 2, '2025-03-05 09:49:00', 'Pending');

-- --------------------------------------------------------
-- Table structure for `feedback`
-- --------------------------------------------------------

CREATE TABLE `feedback` (
  `FeedbackID` INT(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` INT(11) DEFAULT NULL,
  `Comments` TEXT DEFAULT NULL,
  `Ratings` INT(11) DEFAULT NULL,
  PRIMARY KEY (`FeedbackID`),
  FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `report`
-- --------------------------------------------------------

CREATE TABLE `report` (
  `ReportID` INT(11) NOT NULL AUTO_INCREMENT,
  `AdminID` INT(11) DEFAULT NULL,
  `Generated_Date` DATETIME NOT NULL,
  `Details` TEXT DEFAULT NULL,
  PRIMARY KEY (`ReportID`),
  FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
