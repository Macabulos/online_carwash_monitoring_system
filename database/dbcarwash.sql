-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 09:36 AM
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
  `StatusID` int(11) DEFAULT 1,
  `NumberOfCars` int(11) DEFAULT 1,
  `CarType` varchar(50) DEFAULT NULL,
  `CarQuantity` int(11) NOT NULL DEFAULT 1,
  `CarTypeID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`BookingID`, `CustomerID`, `ServiceID`, `BookingDate`, `StatusID`, `NumberOfCars`, `CarType`, `CarQuantity`, `CarTypeID`, `EmployeeID`) VALUES
(42, 21, 8, '2025-05-06 14:00:00', 1, 1, NULL, 1, 0, NULL),
(43, 21, 1, '2025-05-06 13:00:00', 1, 1, NULL, 1, 0, NULL),
(45, 21, 10, '2025-05-06 10:00:00', 1, 1, NULL, 1, 0, NULL),
(47, 14, 1, '2025-05-08 11:00:00', 4, 1, NULL, 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `car_types`
--

CREATE TABLE `car_types` (
  `CarTypeID` int(11) NOT NULL,
  `TypeName` varchar(50) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `BasePrice` decimal(10,2) NOT NULL,
  `EstimatedDuration` int(11) NOT NULL COMMENT 'Duration in minutes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_types`
--

INSERT INTO `car_types` (`CarTypeID`, `TypeName`, `Description`, `BasePrice`, `EstimatedDuration`) VALUES
(1, 'Small Car', 'Compact cars, hatchbacks, and subcompacts (e.g., Honda Civic, Toyota Corolla)', 100.00, 30),
(2, 'Medium Sedan', 'Mid-size sedans and small SUVs (e.g., Toyota Camry, Honda CR-V)', 200.00, 45),
(3, 'Large SUV', 'Full-size SUVs and trucks (e.g., Ford Explorer, Chevy Tahoe)', 250.00, 60),
(4, 'Luxury Vehicle', 'High-end vehicles requiring special care (e.g., Mercedes, BMW)', 300.00, 75),
(5, 'Truck/Van', 'Pickup trucks and large vans (e.g., Ford F-150, Chevy Express)', 280.00, 60),
(6, 'Sedan', 'Standard 4-door car', 180.00, 45),
(7, 'SUV', 'Sports Utility Vehicle', 230.00, 60),
(8, 'Pickup', 'Light-duty truck', 260.00, 65),
(9, 'Van', 'Family or cargo van', 240.00, 55),
(10, 'Motorcycle', 'Two-wheeled motor vehicle', 120.00, 30);

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
(14, 'jake', 'jake@gmail.com', 24, '$2y$10$r7GSgBQPup9aCDa4XFxHJ.vdUrkak2JoHUIGNj9iLCQTvsQcMsSTq', '', '../uploads/ChatGPT Image Apr 7, 2025, 10_12_33 AM.png', NULL, NULL, 1, 0, 'Active', '129308', '2025-05-06 01:08:40'),
(21, 'john', 'foriri7051@idoidraw.com', 21, '$2y$10$48FLxDMGpIAfkNfeYJ90OuR.wg2C2ojOyoBs9xXw9vRGc6Tgt6xk6', '', '../uploads/ChatGPT Image Apr 7, 2025, 09_53_23 AM.png', NULL, NULL, 1, 0, 'Active', '757625', '2025-05-06 08:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EmployeeID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `AssignedServiceID` int(11) DEFAULT NULL,
  `Availability` enum('Available','Assigned','On Leave') DEFAULT 'Available',
  `HireDate` date DEFAULT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`EmployeeID`, `FirstName`, `LastName`, `Position`, `ContactNumber`, `AssignedServiceID`, `Availability`, `HireDate`, `ProfilePicture`) VALUES
(1, 'jake', 'amano', 'role', '3456789', 1, 'Assigned', '2025-05-23', '../uploads/employees/emp_682fee73b4062.png'),
(2, 'jake', 'amano', 'role', '3456789', 1, 'Assigned', '2025-05-23', '../uploads/employees/emp_68300963ad2c5.png'),
(3, 'jake', 'amano', 'role', '3456789', 1, 'Assigned', '2025-05-23', '../uploads/employees/emp_6830096912794.png');

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
(11, 21, 1, 'loveit', 5, NULL);

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
  `ImagePath` varchar(255) DEFAULT NULL,
  `BasePrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `EstimatedDuration` int(11) NOT NULL DEFAULT 60 COMMENT 'Duration in minutes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`ServiceID`, `ServiceName`, `Description`, `ImagePath`, `BasePrice`, `EstimatedDuration`) VALUES
(1, 'Carwash ', 'Here’s what you can expect from our complete car wash package:\r\n\r\n🌊 Pre-Wash\r\nA thorough rinse to remove loose dirt, debris, and surface contaminants, setting the stage for a spotless clean.\r\n\r\n🧼 Soap & Detailing\r\nWe use special car wash soap to break down grime and dirt without harming your paint. Our experts gently scrub your car’s body, windows, and wheels using soft brushes or microfiber cloths for a deep clean.\r\n\r\n🚗 Wheel & Tire Cleaning\r\nWe give special attention to your wheels and tires, removing brake dust and road grime, leaving them looking sleek and shiny.\r\n\r\n💦 Rinse & Dry\r\nA final rinse to wash away soap followed by a drying process using microfiber towels or air dryers, ensuring a streak-free and water spot-free finish.', '1744101442_carwash+service-396w.webp', 4567890.00, 60),
(8, 'Car Detailing', 'Hand wash to remove dirt, grime, and other contaminants from the surface.\r\nClay bar treatment to remove embedded contaminants that washing cannot.\r\nPolishing to restore shine, remove swirl marks, and correct imperfections in the paint.\r\nWaxing to protect the paint and provide a long-lasting glossy finish.\r\nWheel cleaning and tire dressing to ensure rims are spotless and tires have a fresh, dark appearance.\r\nWindow cleaning to remove streaks and enhance visibility.', '1744273334_cardetailing3-7b88-8696.webp', 500.00, 120),
(9, 'Quick Exterior', 'Try Our Quick Exterior\r\nInvolves cleaning and restoring or exceeding the original condition of the surface of the car\'s finish (usually a paint with a glossy finish), chrome trim and windows as well as other visible components on the exterior of the vehicle. A wide array of products and techiniques are used to do this based on the surface type and surface condition.\r\n\r\nDuration:4-8 hours depending on the condition of the car\'s paint.', '1745377305_08a06805f296462a8e0942ae484a5fa8.jpg', 40.00, 90),
(10, 'Quick Interior Detailing', 'Try Our Quick Interior\r\nInvolves deep cleaning of the whole interior cabin. Autmobile interiors of the last 50 years have variety of materials used inside the cabin such as synthetic carpet upholstery, vinyl, leather, various natural fibers, carbon fiber composites, platics and others. Different techniques and products are used to address cleaning these. Vacuuming is the standard, liquid and foam chemicals, as well us brushes may be used to removes stains on upholstery. Some nonporous surfaces may also be polished.\r\n\r\nDuration: 4-8 hours depending on the gravity of the dirt', '1745377358_Spritz-Interior-Detailer-UV-Protection-Rectangle.webp', 50.00, 90),
(11, 'Signature Engine', 'Try Our Signature Engine\r\nThorough cleaning of the engine bay and under the hood. Application of degreasers, metal polish, chrome polish might be needed to remove the dust, oil deposits and other contaminants in the engine bay. Chemicals will be applied to the plastic and rubber parts to restore it to its close to brand new look.\r\n\r\nDuration: 4-8 hours', '1745377435_lycoming-940.png', 75.00, 120);

-- --------------------------------------------------------

--
-- Table structure for table `service_car_types`
--

CREATE TABLE `service_car_types` (
  `ServiceID` int(11) NOT NULL,
  `CarTypeID` int(11) NOT NULL,
  `AdditionalPrice` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_car_types`
--

INSERT INTO `service_car_types` (`ServiceID`, `CarTypeID`, `AdditionalPrice`) VALUES
(1, 1, 1.00),
(1, 2, 1.00),
(1, 3, 1.00),
(1, 4, 1.00),
(1, 5, 1.00),
(1, 6, 1.00),
(1, 7, 1.00),
(1, 8, 1.00),
(1, 9, 1.00),
(1, 10, 1.00),
(8, 1, 1.00),
(8, 2, 1.00),
(8, 3, 1.00),
(8, 4, 1.00),
(8, 5, 1.00),
(8, 6, 1.00),
(8, 7, 1.00),
(8, 8, 1.00),
(8, 9, 1.00),
(8, 10, 1.00);

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
  ADD KEY `fk_bookings_customer` (`CustomerID`),
  ADD KEY `fk_bookings_employees` (`EmployeeID`);

--
-- Indexes for table `car_types`
--
ALTER TABLE `car_types`
  ADD PRIMARY KEY (`CarTypeID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD KEY `ServiceID` (`ServiceID`),
  ADD KEY `fk_customer_status` (`StatusID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD KEY `AssignedServiceID` (`AssignedServiceID`);

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
-- Indexes for table `service_car_types`
--
ALTER TABLE `service_car_types`
  ADD PRIMARY KEY (`ServiceID`,`CarTypeID`),
  ADD KEY `CarTypeID` (`CarTypeID`);

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
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `car_types`
--
ALTER TABLE `car_types`
  MODIFY `CarTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_employees` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`AssignedServiceID`) REFERENCES `service` (`ServiceID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE;

--
-- Constraints for table `service_car_types`
--
ALTER TABLE `service_car_types`
  ADD CONSTRAINT `service_car_types_ibfk_1` FOREIGN KEY (`ServiceID`) REFERENCES `service` (`ServiceID`),
  ADD CONSTRAINT `service_car_types_ibfk_2` FOREIGN KEY (`CarTypeID`) REFERENCES `car_types` (`CarTypeID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
