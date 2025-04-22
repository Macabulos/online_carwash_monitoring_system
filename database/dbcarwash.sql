-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 09:27 AM
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
(1, 'carwash@gmail.com', 'carwash', '../uploads/486115675_9452392428189858_5680850207836976720_n.jpg');

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
(27, 14, 8, '2025-04-20 17:47:00', 4);

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
(13, 'john lester', 'johnlestermacabulos@gmail.com', 21, '$2y$10$ewMoCfjzzrSxnbZBNyFdCu4NcSgpPdLkFwoglutGwSmvORcTWmA/2', '', NULL, NULL, NULL, 1),
(14, 'jake', 'jake@gmail.com', 29, '$2y$10$r7GSgBQPup9aCDa4XFxHJ.vdUrkak2JoHUIGNj9iLCQTvsQcMsSTq', '', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Ratings` int(11) DEFAULT NULL,
  `Response` text DEFAULT NULL
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
  `Description` text DEFAULT NULL,
  `ImagePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`ServiceID`, `ServiceName`, `Description`, `ImagePath`) VALUES
(1, 'Carwash ', 'Here’s what you can expect from our complete car wash package:\r\n\r\n🌊 Pre-Wash\r\nA thorough rinse to remove loose dirt, debris, and surface contaminants, setting the stage for a spotless clean.\r\n\r\n🧼 Soap & Detailing\r\nWe use special car wash soap to break down grime and dirt without harming your paint. Our experts gently scrub your car’s body, windows, and wheels using soft brushes or microfiber cloths for a deep clean.\r\n\r\n🚗 Wheel & Tire Cleaning\r\nWe give special attention to your wheels and tires, removing brake dust and road grime, leaving them looking sleek and shiny.\r\n\r\n💦 Rinse & Dry\r\nA final rinse to wash away soap followed by a drying process using microfiber towels or air dryers, ensuring a streak-free and water spot-free finish.', '1744101442_carwash+service-396w.webp'),
(8, 'Car Detailing', 'Hand wash to remove dirt, grime, and other contaminants from the surface.\r\nClay bar treatment to remove embedded contaminants that washing cannot.\r\nPolishing to restore shine, remove swirl marks, and correct imperfections in the paint.\r\nWaxing to protect the paint and provide a long-lasting glossy finish.\r\nWheel cleaning and tire dressing to ensure rims are spotless and tires have a fresh, dark appearance.\r\nWindow cleaning to remove streaks and enhance visibility.', '1744273334_cardetailing3-7b88-8696.webp');

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
(3, 'Cancelled'),
(4, 'In Progress');

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
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `StatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
