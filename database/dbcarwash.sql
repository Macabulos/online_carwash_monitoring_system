-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 01:54 PM
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
  `StatusID` int(11) DEFAULT 1,
  `is_google_user` tinyint(1) DEFAULT 0,
  `Status` enum('Active','Blocked') NOT NULL DEFAULT 'Active',
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `Username`, `EmailAddress`, `Age`, `Password`, `ConfirmPassword`, `ProfilePicture`, `ServiceID`, `BookingDate`, `StatusID`, `is_google_user`, `Status`, `otp_code`, `otp_expires_at`) VALUES
(14, 'jake', 'jake@gmail.com', 24, '$2y$10$r7GSgBQPup9aCDa4XFxHJ.vdUrkak2JoHUIGNj9iLCQTvsQcMsSTq', '', '../uploads/ChatGPT Image Apr 7, 2025, 10_12_33 AM.png', NULL, NULL, 1, 0, 'Active', '602808', '2025-05-05 10:58:41'),
(15, 'john', 'hatdog@gmail.com', 21, '$2y$10$kEWcs3FsQ1ORAuxBvBu49.0vp5xPkk0JHu/sV7UDD5Ps/cWUpxMMa', '', NULL, NULL, NULL, 1, 0, 'Active', NULL, NULL),
(17, 'hala', 'vayoh90813@idoidraw.com', 21, '$2y$10$EEanRBjomKma9vayWD6ppufn99MtUXsroCg.VuliQuO3LnEGC3bHS', '', NULL, NULL, NULL, 1, 0, 'Active', NULL, NULL),
(18, 'what', 'pagorij549@harinv.com', 21, '$2y$10$HS0eOb9BfMKwr6I8SYcYaur3elFmVxQaRYi6.aO9I2MfZTNyo.0b.', '', NULL, NULL, NULL, 1, 0, 'Active', '652398', '2025-05-05 10:55:52'),
(19, 'jebini', 'jebini7709@harinv.com', 21, '$2y$10$kP7/Kycz5fVLE.NJ7HmCTeBnHrhz2V6tbPkCjfXkqVpAREybS6CQi', '', NULL, NULL, NULL, 1, 0, 'Active', '106037', '2025-05-05 11:19:36');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Ratings` int(11) DEFAULT NULL,
  `Response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `CustomerID`, `ServiceID`, `Comments`, `Ratings`, `Response`) VALUES
(10, 14, 1, 'love it', 5, NULL);

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
(8, 'Car Detailing', 'Hand wash to remove dirt, grime, and other contaminants from the surface.\r\nClay bar treatment to remove embedded contaminants that washing cannot.\r\nPolishing to restore shine, remove swirl marks, and correct imperfections in the paint.\r\nWaxing to protect the paint and provide a long-lasting glossy finish.\r\nWheel cleaning and tire dressing to ensure rims are spotless and tires have a fresh, dark appearance.\r\nWindow cleaning to remove streaks and enhance visibility.', '1744273334_cardetailing3-7b88-8696.webp'),
(9, 'Quick Exterior', 'Try Our Quick Exterior\r\nInvolves cleaning and restoring or exceeding the original condition of the surface of the car\'s finish (usually a paint with a glossy finish), chrome trim and windows as well as other visible components on the exterior of the vehicle. A wide array of products and techiniques are used to do this based on the surface type and surface condition.\r\n\r\nDuration:4-8 hours depending on the condition of the car\'s paint.', '1745377305_08a06805f296462a8e0942ae484a5fa8.jpg'),
(10, 'Quick Interior Detailing', 'Try Our Quick Interior\r\nInvolves deep cleaning of the whole interior cabin. Autmobile interiors of the last 50 years have variety of materials used inside the cabin such as synthetic carpet upholstery, vinyl, leather, various natural fibers, carbon fiber composites, platics and others. Different techniques and products are used to address cleaning these. Vacuuming is the standard, liquid and foam chemicals, as well us brushes may be used to removes stains on upholstery. Some nonporous surfaces may also be polished.\r\n\r\nDuration: 4-8 hours depending on the gravity of the dirt', '1745377358_Spritz-Interior-Detailer-UV-Protection-Rectangle.webp'),
(11, 'Signature Engine', 'Try Our Signature Engine\r\nThorough cleaning of the engine bay and under the hood. Application of degreasers, metal polish, chrome polish might be needed to remove the dust, oil deposits and other contaminants in the engine bay. Chemicals will be applied to the plastic and rubber parts to restore it to its close to brand new look.\r\n\r\nDuration: 4-8 hours', '1745377435_lycoming-940.png');

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

-- --------------------------------------------------------

--
-- Table structure for table `temp_registrations`
--

CREATE TABLE `temp_registrations` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `email` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`email`, `code`, `created_at`) VALUES
('earlkaye@gmail.com', '490439', '2025-05-04 10:43:09');

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
  ADD KEY `StatusID` (`StatusID`),
  ADD KEY `fk_bookings_customer` (`CustomerID`);

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
-- Indexes for table `temp_registrations`
--
ALTER TABLE `temp_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`email`);

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
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `StatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `temp_registrations`
--
ALTER TABLE `temp_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
