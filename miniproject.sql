-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2025 at 06:35 PM
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
-- Database: `miniproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoptions`
--

CREATE TABLE `adoptions` (
  `AdoptionID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `PetID` int(11) DEFAULT NULL,
  `ApplicationDate` date NOT NULL,
  `AdoptionDate` date DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoptions`
--

INSERT INTO `adoptions` (`AdoptionID`, `CustomerID`, `PetID`, `ApplicationDate`, `AdoptionDate`, `Status`, `CreatedAt`) VALUES
(13, 8, 25, '2024-10-24', '2024-10-24', 'Rejected', '2024-10-24 09:56:51'),
(14, 10, 25, '2024-10-24', '2024-10-24', 'Approved', '2024-10-24 10:02:42'),
(16, 8, 26, '2024-11-04', '2024-11-04', 'Rejected', '2024-11-04 06:09:03'),
(17, 8, 27, '2024-11-04', '2024-11-04', 'Approved', '2024-11-04 16:16:48'),
(19, 2, 33, '2024-11-07', '2024-11-11', 'Approved', '2024-11-07 09:15:13'),
(20, 2, 29, '2024-11-07', '2024-11-07', 'Approved', '2024-11-07 09:18:15'),
(21, 10, 37, '2024-11-07', '2024-11-08', 'Approved', '2024-11-07 09:28:55'),
(23, 2, 39, '2025-01-01', '2025-01-01', 'Rejected', '2025-01-01 08:47:28'),
(24, 10, 39, '2025-01-01', '2025-01-01', 'Rejected', '2025-01-01 08:47:45'),
(25, 2, 30, '2025-01-02', '2025-01-02', 'Approved', '2025-01-02 17:05:33');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `UserType` enum('Customer','Admin') DEFAULT 'Customer',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomerID`, `Name`, `Email`, `Password`, `Phone`, `Address`, `UserType`, `CreatedAt`) VALUES
(1, 'admin', 'admin@gmail.com', 'addwin-alan', '0123456789', 'Kochi', 'Admin', '2024-09-28 15:07:39'),
(2, 'user1', 'user1@gmail.com', 'user1user1', '9744836733', 'kochi', 'Customer', '2024-09-29 11:22:27'),
(8, 'user2', 'user2@gmail.com', 'user2user2', '0123456789', 'kozhikode', 'Customer', '2024-10-03 16:18:57'),
(10, 'user3', 'user3@gmail.com', 'user3user3', '0123456789', 'paravoor', 'Customer', '2024-10-08 06:22:58'),
(12, 'Ronith Menon', 'ronii123@gmail.com', 'ronithmenon', '9753678954', 'Ernakulam', 'Customer', '2024-11-04 04:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `DonationID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Credit Card','UPI') NOT NULL,
  `DonationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `CreditCardNumber` varchar(255) DEFAULT NULL,
  `ExpiryDate` varchar(7) DEFAULT NULL,
  `CVV` varchar(255) DEFAULT NULL,
  `UPIID` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`DonationID`, `CustomerID`, `Amount`, `PaymentMethod`, `DonationDate`, `CreditCardNumber`, `ExpiryDate`, `CVV`, `UPIID`) VALUES
(3, 2, 62.00, 'Credit Card', '2024-10-30 06:43:59', '1111111111111111', '12/12', '222', NULL),
(6, 8, 900.00, 'UPI', '2024-10-30 09:27:47', NULL, NULL, NULL, 'addwie@upi'),
(11, 2, 77.00, 'Credit Card', '2024-10-31 03:05:04', '6666666666666666', '11/11', '111', NULL),
(13, 12, 22.00, 'UPI', '2024-11-04 00:07:52', NULL, NULL, NULL, 'addwin@upi'),
(20, 2, 30.00, 'Credit Card', '2024-11-07 06:46:47', '3333333333333333', '12/11', '121', NULL),
(21, 10, 100.00, 'Credit Card', '2024-11-07 09:29:54', '2222222222222222', '12/11', '133', NULL),
(22, 2, 290.00, 'UPI', '2024-11-11 10:28:12', NULL, NULL, NULL, 'kev@sbi'),
(23, 2, 100.00, 'UPI', '2024-11-29 05:38:55', NULL, NULL, NULL, 'addwie@upi'),
(24, 8, 300.00, 'Credit Card', '2025-01-01 08:46:30', '6666666666666666', '11/27', '555', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `FeedbackText` text NOT NULL,
  `Rating` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`FeedbackID`, `CustomerID`, `FeedbackText`, `Rating`, `CreatedAt`) VALUES
(4, 8, 'helllllllllllllllllllllllllllooooooooooooooooooooooooooooooooooooooooooo thissssssssssssssssssssssssssssss isssssssssssssssssssssss aaaaaaaaaaaaa tessssssssttttttttttttttttttttttttttttttttttttttt\r\n\r\nokayyyyyyyyyyyyyyyyyy?', 1, '2024-10-05 07:04:19'),
(6, 2, 'dfdffffffffffffffffffffffffffffffffffffffffffffffffffffffffdfdfdf fdddddddddd    dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd ook bieeeeeeeeee', 2, '2024-10-05 07:34:29'),
(7, 2, 'very happy now', 4, '2024-11-06 09:42:25'),
(8, 10, 'very good', 4, '2024-11-07 09:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `PetID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Species` varchar(50) NOT NULL,
  `Breed` varchar(50) DEFAULT NULL,
  `Age` smallint(6) DEFAULT NULL,
  `Gender` enum('Male','Female') DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Status` enum('Available','Adopted') DEFAULT 'Available',
  `ImageURL` varchar(255) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`PetID`, `Name`, `Species`, `Breed`, `Age`, `Gender`, `Description`, `Status`, `ImageURL`, `CustomerID`, `CreatedAt`) VALUES
(25, 'Roni', 'Dog', 'Pug', 25, 'Male', 'Very Good Dog', 'Adopted', 'pexels-katlovessteve-551628.jpg', 2, '2024-10-22 07:34:31'),
(26, 'Arjun', 'Dog', 'German Sheperd', 26, 'Male', 'Arjun reddy is a lovely pet', 'Available', '1.jpg', 12, '2024-11-04 04:35:09'),
(27, 'Jerry', 'Dog', 'Indie', 12, 'Male', 'Very Wholesome Dog.', 'Adopted', '2.jpg', 2, '2024-11-04 11:08:42'),
(28, 'Creti', 'Dog', 'Indie', 24, 'Female', 'She is adorable and very disciplined.', 'Available', '3.jpg', 10, '2024-11-04 15:36:01'),
(29, 'Sera', 'Dog', 'Labrador', 6, 'Female', '6-month-old cute puppy. good health.', 'Adopted', '4.jpg', 10, '2024-11-04 15:42:32'),
(30, 'Toni', 'Dog', 'Dash', 3, 'Male', 'Trained Dog', 'Adopted', '5.jpg', 10, '2024-11-04 15:43:56'),
(31, 'Sura', 'Cat', 'Spotted cat', 14, 'Male', 'Well Behaving cat.', 'Available', '7.jpg', 8, '2024-11-04 15:56:26'),
(33, 'Sam', 'Cat', 'Snowshoe', 15, 'Male', 'Loves Kids , well maintained cat.', 'Adopted', '9.jpg', 8, '2024-11-04 15:58:59'),
(35, 'Cookie', 'Cat', 'Domestic Shorthair', 1, 'Male', 'Healthy Kitten', 'Available', '10.jpg', 8, '2024-11-04 16:15:30'),
(37, 'Bruno1', 'Dog', 'American pit Bull', 25, 'Female', 'Very obedient dog', 'Adopted', '6.jpeg', 2, '2024-11-07 09:16:39'),
(38, 'Marco', 'Dog', 'Rat Terrier', 27, 'Male', 'Very Family Friendly Dog.', 'Available', '2.jpg', 8, '2025-01-01 08:37:41'),
(39, 'Dayana', 'Cat', 'Domestic Shorthair', 96, 'Female', 'Mother of 5 kittens. Well behaved.', 'Available', '8.jpg', 8, '2025-01-01 08:44:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD PRIMARY KEY (`AdoptionID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `PetID` (`PetID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`DonationID`),
  ADD KEY `idx_customer_id` (`CustomerID`),
  ADD KEY `idx_donation_date` (`DonationDate`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`PetID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoptions`
--
ALTER TABLE `adoptions`
  MODIFY `AdoptionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `DonationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `PetID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD CONSTRAINT `adoptions_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  ADD CONSTRAINT `adoptions_ibfk_2` FOREIGN KEY (`PetID`) REFERENCES `pets` (`PetID`);

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE SET NULL;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
