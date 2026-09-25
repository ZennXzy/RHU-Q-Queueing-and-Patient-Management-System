-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2026 at 10:46 AM
-- Generation Time: Sep 23, 2026 at 08:27 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rhu_q_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_auth`
--

CREATE TABLE `admin_auth` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `role` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_auth`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointments_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `service` varchar(250) NOT NULL,
  `remarks` text NOT NULL,
  `server_name` int(11) NOT NULL,
  `status` varchar(250) NOT NULL,
  `note` text DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `log_message` varchar(200) NOT NULL,
  `log_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

--
-- Table structure for table `patient_auth`
--

CREATE TABLE `patient_auth` (
  `patient_auth_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `fullname` int(100) NOT NULL,
  `email` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_active` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_records`
--

CREATE TABLE `patient_records` (
  `patient_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `employment_status` varchar(50) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `civil_status` varchar(20) NOT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `mothers_name` varchar(100) DEFAULT NULL,
  `fathers_name` varchar(100) DEFAULT NULL,
  `spouses_name` varchar(100) DEFAULT NULL,
  `phil_status` varchar(50) DEFAULT NULL,
  `phil_number` varchar(30) DEFAULT NULL,
  `principal_member` varchar(100) DEFAULT NULL,
  `yakap_member` varchar(10) DEFAULT 'No',
  `date_registered` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_records`
--
-- --------------------------------------------------------

--
-- Table structure for table `queue_display`
--

CREATE TABLE `queue_display` (
  `queue_display_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue_information`
--

CREATE TABLE `queue_information` (
  `queue_info_id` int(11) NOT NULL,
  `queue_code` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_name` varchar(200) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `service_duration` timestamp NULL DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `service` varchar(100) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `service` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) NOT NULL,
  `service_duration` varchar(100) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_auth`
--
ALTER TABLE `admin_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointments_id`),
  ADD KEY `appointments_ibfk_2` (`server_name`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `patient_auth`
--
ALTER TABLE `patient_auth`
  ADD PRIMARY KEY (`patient_auth_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `patient_id` (`patient_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `patient_records`
--
ALTER TABLE `patient_records`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_patient_name` (`lastname`,`firstname`);

--
-- Indexes for table `pending_appointments`
--
ALTER TABLE `pending_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);
=======
-- Indexes for table `queue_display`
--
ALTER TABLE `queue_display`
  ADD PRIMARY KEY (`queue_display_id`);

--
-- Indexes for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD PRIMARY KEY (`queue_info_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `patient_id` (`patient_name`),
  ADD KEY `queue_id` (`queue_code`),
  ADD KEY `transactions_ibfk_3` (`service`);
=======
  ADD KEY `transactions_ibfk_3` (`service`),
  ADD KEY `transactions_ibfk_1` (`server_name`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `queue_code_3` (`queue_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_auth`
--
ALTER TABLE `admin_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `patient_auth`
--
ALTER TABLE `patient_auth`
  MODIFY `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `pending_appointments`
--
ALTER TABLE `pending_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`);

--
-- Constraints for table `patient_auth`
--
ALTER TABLE `patient_auth`
  ADD CONSTRAINT `patient_auth_ibfk_1` FOREIGN KEY (`email`) REFERENCES `patient_records` (`patient_id`);

--
-- Constraints for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD CONSTRAINT `queue_information_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`);
  ADD CONSTRAINT `queue_information_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`),
  ADD CONSTRAINT `queue_information_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
