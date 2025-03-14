  -- phpMyAdmin SQL Dump
  -- version 5.2.1
  -- https://www.phpmyadmin.net/
  --
  -- Host: 127.0.0.1
  -- Generation Time: Mar 12, 2025 at 07:31 AM
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
    `Password` varchar(255) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

  --
  -- Dumping data for table `admin`
  --

  INSERT INTO `admin` (`AdminID`, `Email`, `Password`) VALUES
  (1, 'carwash@gmail.com', 'carwash');

  -- --------------------------------------------------------

  --
  -- Table structure for table `booking`
  --

  CREATE TABLE `booking` (
    `BookingID` int(11) NOT NULL,
    `CustomerID` int(11) DEFAULT NULL,
    `ServiceID` int(11) DEFAULT NULL,
    `Date` datetime NOT NULL,
    `Status` varchar(20) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
    `ConfirmPassword` varchar(255) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

  -- --------------------------------------------------------

  --
  -- Table structure for table `feedback`
  --

  CREATE TABLE `feedback` (
    `FeedbackID` int(11) NOT NULL,
    `BookingID` int(11) DEFAULT NULL,
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
  -- Indexes for dumped tables
  --

  --
  -- Indexes for table `admin`
  --
  ALTER TABLE `admin`
    ADD PRIMARY KEY (`AdminID`);

  --
  -- Indexes for table `booking`
  --
  ALTER TABLE `booking`
    ADD PRIMARY KEY (`BookingID`),
    ADD KEY `CustomerID` (`CustomerID`),
    ADD KEY `ServiceID` (`ServiceID`);

  --
  -- Indexes for table `customer`
  --
  ALTER TABLE `customer`
    ADD PRIMARY KEY (`CustomerID`);

  --
  -- Indexes for table `feedback`
  --
  ALTER TABLE `feedback`
    ADD PRIMARY KEY (`FeedbackID`),
    ADD KEY `BookingID` (`BookingID`);

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
  -- AUTO_INCREMENT for dumped tables
  --

  --
  -- AUTO_INCREMENT for table `admin`
  --
  ALTER TABLE `admin`
    MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

  --
  -- AUTO_INCREMENT for table `booking`
  --
  ALTER TABLE `booking`
    MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT;

  --
  -- AUTO_INCREMENT for table `customer`
  --
  ALTER TABLE `customer`
    MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT;

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
    MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT;

  --
  -- Constraints for dumped tables
  --

  --
  -- Constraints for table `booking`
  --
  ALTER TABLE `booking`
    ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`) ON DELETE CASCADE ON UPDATE CASCADE;

  --
  -- Constraints for table `feedback`
  --
  ALTER TABLE `feedback`
    ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;

  --
  -- Constraints for table `report`
  --
  ALTER TABLE `report`
    ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;
  COMMIT;

  /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
  /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
  /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
