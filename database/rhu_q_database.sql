-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 02:41 PM
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

INSERT INTO `admin_auth` (`id`, `username`, `password_hash`, `fullname`, `email`, `status`, `role`, `created_at`, `last_active`) VALUES
(4, 'paul', '$2y$12$cyX4s6IqAzDcbVhL7VTO5OEhjfvWZBaFSp14jvWvMHxg73jmpcetC', 'Paul Anthony Fontamillas', 'paul@gmail.com', 'Online', 'Admin', '2026-08-09 00:14:45', '2026-09-17 18:29:40'),
(10, 'carlo', '$2y$12$BBTwb7UVHzNPdFW/zZU0XOEoxlo2ZNW2eFPjBHhoh4pBupiTlRSOG', 'Carlo Agustin', 'carloagustin@gmail.com', 'Online', 'Doctor/Staff', '2026-09-08 19:47:26', '2026-09-18 19:37:17'),
(12, 'alex', '$2y$12$1s5/eTv22w5LCFhTMpj4YendeEIy7yFF5xQUCvcS8Cx6lZWb33F7a', 'RHU-Q: Display', '', 'Offline', 'Display', '2026-09-08 22:17:31', '2026-09-16 20:49:03'),
(13, 'sample', '$2y$12$isoajsYWJvuCUzXf4iXI9.Org6ewpUM6ugm3SmLJYuGs/UB.9dc5a', 'sample', 'sample@gmail.com', 'Offline', 'Doctor/Staff', '2026-09-12 17:54:09', '2026-09-17 18:29:32'),
(14, 'jamesbond', '$2y$12$IxR3RufOZqoe/YefG7Zdkuq.DiB3x.ggUicTthkw9.RWsNEkckubO', 'james the bond', 'jamesbond@gmail.co', 'Offline', 'Doctor/staff', '2026-09-17 01:50:46', '2026-09-17 01:50:46');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_messages`
--

CREATE TABLE `appointment_messages` (
  `id` int(11) NOT NULL,
  `pending_appointment_id` int(11) NOT NULL,
  `sender_type` enum('Admin','Patient') NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approved_appointments`
--

CREATE TABLE `approved_appointments` (
  `id` int(11) NOT NULL,
  `pending_appointment_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `approved_at` datetime DEFAULT current_timestamp()
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

CREATE TABLE `pending_appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `requested_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `server_name` int(11) NOT NULL,
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
  `patient_name` int(11) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) NOT NULL,
  `service` varchar(100) NOT NULL,
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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `fullname` (`fullname`);

--
-- Indexes for table `appointment_messages`
--
ALTER TABLE `appointment_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pending_appointment_id` (`pending_appointment_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `approved_appointments`
--
ALTER TABLE `approved_appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pending_appointment_id` (`pending_appointment_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `patient_auth`
--
ALTER TABLE `patient_auth`
  ADD PRIMARY KEY (`patient_auth_id`);

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

--
-- Indexes for table `queue_display`
--
ALTER TABLE `queue_display`
  ADD PRIMARY KEY (`queue_display_id`);

--
-- Indexes for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD PRIMARY KEY (`queue_info_id`),
  ADD UNIQUE KEY `server_name` (`server_name`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `server_name` (`server_name`),
  ADD KEY `patient_id` (`patient_name`),
  ADD KEY `queue_id` (`queue_code`),
  ADD KEY `transactions_ibfk_3` (`service`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_auth`
--
ALTER TABLE `admin_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `appointment_messages`
--
ALTER TABLE `appointment_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approved_appointments`
--
ALTER TABLE `approved_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `patient_auth`
--
ALTER TABLE `patient_auth`
  MODIFY `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `pending_appointments`
--
ALTER TABLE `pending_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_display`
--
ALTER TABLE `queue_display`
  MODIFY `queue_display_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_messages`
--
ALTER TABLE `appointment_messages`
  ADD CONSTRAINT `appointment_messages_ibfk_1` FOREIGN KEY (`pending_appointment_id`) REFERENCES `pending_appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_auth` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `approved_appointments`
--
ALTER TABLE `approved_appointments`
  ADD CONSTRAINT `approved_appointments_ibfk_1` FOREIGN KEY (`pending_appointment_id`) REFERENCES `pending_appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approved_appointments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_auth` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_appointments`
--
ALTER TABLE `pending_appointments`
  ADD CONSTRAINT `pending_appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD CONSTRAINT `queue_information_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`),
  ADD CONSTRAINT `queue_information_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
