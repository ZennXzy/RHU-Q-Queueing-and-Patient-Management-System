-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 06:11 PM
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

INSERT INTO `admin_auth` (`id`, `username`, `password_hash`, `fullname`, `email`, `status`, `role`, `created_at`, `last_active`) VALUES
(4, 'paul', '$2y$12$cyX4s6IqAzDcbVhL7VTO5OEhjfvWZBaFSp14jvWvMHxg73jmpcetC', 'Paul Anthony Fontamillas', 'paul@gmail.com', 'Online', 'Admin', '2026-08-09 00:14:45', '2026-10-06 20:59:10'),
(10, 'carlo', '$2y$12$BBTwb7UVHzNPdFW/zZU0XOEoxlo2ZNW2eFPjBHhoh4pBupiTlRSOG', 'Carlo Agustin', 'carloagustin@gmail.com', 'Offline', 'Doctor/Staff', '2026-09-08 19:47:26', '2026-10-06 20:59:02'),
(12, 'alex', '$2y$12$1s5/eTv22w5LCFhTMpj4YendeEIy7yFF5xQUCvcS8Cx6lZWb33F7a', 'RHU-Q: Display', '', 'Offline', 'Display', '2026-09-08 22:17:31', '2026-09-16 20:49:03'),
(13, 'sample', '$2y$12$isoajsYWJvuCUzXf4iXI9.Org6ewpUM6ugm3SmLJYuGs/UB.9dc5a', 'sample', 'sample@gmail.com', 'Offline', 'Doctor/Staff', '2026-09-12 17:54:09', '2026-09-17 18:29:32'),
(14, 'jamesbond', '$2y$12$IxR3RufOZqoe/YefG7Zdkuq.DiB3x.ggUicTthkw9.RWsNEkckubO', 'james the bond', 'jamesbond@gmail.co', 'Offline', 'Doctor/staff', '2026-09-17 01:50:46', '2026-09-17 01:50:46');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointments_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `contact_number` varchar(11) DEFAULT NULL,
  `service` varchar(250) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) DEFAULT NULL,
  `status` varchar(250) NOT NULL,
  `note` text DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointments_id`, `patient_id`, `contact_number`, `service`, `remarks`, `server_name`, `status`, `note`, `appointment_date`, `created_at`, `updated_at`) VALUES
(24, 250, NULL, 'Medical Consultation', 'None', NULL, 'Approved', NULL, '2026-10-10', '2026-10-03 18:41:44', '2026-10-04 16:36:47');

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

INSERT INTO `logs` (`log_id`, `log_message`, `log_date`) VALUES
(216, 'zennXzy (Patient) logged in successfully.', '2026-10-03 14:58:10'),
(217, 'Account zennXzy logged out.', '2026-10-03 15:02:46'),
(218, 'paul (Admin) logged in successfully.', '2026-10-03 15:03:25'),
(219, 'paul just logged out.', '2026-10-03 15:04:22'),
(220, 'zennXzy (Patient) logged in successfully.', '2026-10-03 15:05:35'),
(221, 'Account zennXzy logged out.', '2026-10-03 15:08:01'),
(222, 'zennXzy (Patient) logged in successfully.', '2026-10-03 15:08:11'),
(223, 'Account zennXzy logged out.', '2026-10-03 15:08:42'),
(224, 'zennXzy (Patient) logged in successfully.', '2026-10-03 15:13:03'),
(225, 'Patient ID 250 created queue ticket \\A\\0001 for Medical Consultation.', '2026-10-03 16:05:41'),
(226, 'Patient ID 250 created queue ticket A0001 for Medical Consultation.', '2026-10-03 16:07:20'),
(227, 'paul (Admin) logged in successfully.', '2026-10-03 16:07:40'),
(228, 'Patient ID 250 submitted a new appointment request (ID: 20) for Medical Consultation.', '2026-10-03 17:10:06'),
(229, 'root declined an appointment.', '2026-10-03 17:58:06'),
(230, 'Patient ID 250 submitted a new appointment request (ID: 21) for Immunization.', '2026-10-03 18:14:20'),
(231, 'Paul Anthony Fontamillas completed a task.', '2026-10-03 18:15:43'),
(232, 'paul added an Appointment.', '2026-10-03 18:15:43'),
(233, 'Paul Anthony Fontamillas completed a task.', '2026-10-03 18:25:47'),
(234, 'paul added an Appointment.', '2026-10-03 18:25:47'),
(235, 'Staff/Doctor approved an appointment (ID: 21).', '2026-10-03 18:37:46'),
(236, 'Staff/Doctor approved an appointment (ID: 22).', '2026-10-03 18:37:47'),
(237, 'Staff/Doctor approved an appointment (ID: 23).', '2026-10-03 18:37:48'),
(238, 'Staff/Doctor approved an appointment (ID: 20).', '2026-10-03 18:37:48'),
(239, 'Patient ID 250 submitted a new appointment request (ID: 24) for Medical Consultation.', '2026-10-03 18:41:44'),
(240, 'Staff/Doctor approved an appointment (ID: 24).', '2026-10-03 18:41:53'),
(241, 'Staff/Doctor approved an appointment (ID: 24).', '2026-10-03 18:52:43'),
(242, 'Staff/Doctor approved an appointment (ID: 24).', '2026-10-03 18:53:05'),
(243, 'carlo (Admin) logged in successfully.', '2026-10-04 16:06:07'),
(244, 'Staff/Doctor approved an appointment (ID: 24).', '2026-10-04 16:36:47'),
(245, 'Staff/Doctor approved an appointment (ID: 24).', '2026-10-04 16:36:47'),
(246, 'zennXzy (Patient) logged in successfully.', '2026-10-06 14:01:49'),
(247, 'paul (Admin) logged in successfully.', '2026-10-06 14:02:35'),
(248, 'paul (Admin) logged in successfully.', '2026-10-06 18:26:20'),
(249, 'zennXzy (Patient) logged in successfully.', '2026-10-06 20:03:20'),
(250, 'zennXzy (Patient) logged in successfully.', '2026-10-06 20:05:42'),
(251, 'zennXzy (Patient) logged in successfully.', '2026-10-06 20:06:34'),
(252, 'zennXzy logged out.', '2026-10-06 20:19:58'),
(253, 'paul logged out.', '2026-10-06 20:20:09'),
(254, 'paul (Admin) logged in successfully.', '2026-10-06 20:20:26'),
(255, 'zennXzy (Patient) logged in successfully.', '2026-10-06 20:21:02'),
(256, 'paul logged out.', '2026-10-06 20:58:40'),
(257, 'carlo (Admin) logged in successfully.', '2026-10-06 20:58:48'),
(258, 'carlo logged out.', '2026-10-06 20:59:02'),
(259, 'paul (Admin) logged in successfully.', '2026-10-06 20:59:10');

-- --------------------------------------------------------

--
-- Table structure for table `patient_auth`
--

CREATE TABLE `patient_auth` (
  `patient_auth_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` int(11) DEFAULT NULL,
  `account_token` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_auth`
--

INSERT INTO `patient_auth` (`patient_auth_id`, `patient_id`, `username`, `password_hash`, `fullname`, `email`, `account_token`, `status`, `role`, `created_at`, `last_active`) VALUES
(9, 250, 'zennXzy', '$2y$12$Hnb/VEa9ZUumnjg4LHer6uZ5lODT9BUUSOECoroByKWX97FgvPie6', 'Paul Anthony Fontamillas', NULL, 538625, 'Online', 'Patient', '2026-10-03 14:53:11', '2026-10-06 20:21:02');

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
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `account_token` int(11) NOT NULL,
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

INSERT INTO `patient_records` (`patient_id`, `firstname`, `lastname`, `middlename`, `suffix`, `address`, `age`, `gender`, `date_of_birth`, `contact_number`, `email`, `account_token`, `employment_status`, `occupation`, `business_name`, `civil_status`, `religion`, `mothers_name`, `fathers_name`, `spouses_name`, `phil_status`, `phil_number`, `principal_member`, `yakap_member`, `date_registered`) VALUES
(250, 'Paul Anthony', 'Fontamillas', NULL, NULL, 'Dapawan Odiongan Romblon', 25, 'Male', '2001-09-18', NULL, NULL, 538625, 'Unemployed', NULL, NULL, 'Single', 'Roman Catholicism', 'n/a n/a', 'n/a n/a', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-10-03'),
(251, 'Tony', 'Fontamillas', 'Fetalver', NULL, 'Dapawan Odiongan Romblon', 23, 'Male', '2003-09-16', '09123456789', NULL, 151639, 'Unemployed', NULL, NULL, 'Single', 'Roman Catholicism', 'n/a n/a', 'n/a n/a', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-10-03');

-- --------------------------------------------------------

--
-- Table structure for table `queue_display`
--

CREATE TABLE `queue_display` (
  `queue_display_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue_display`
--

INSERT INTO `queue_display` (`queue_display_id`, `service_type`, `status`, `date_created`) VALUES
(104, 'Medical Consultation', 'Active', '2026-10-06 14:02:54'),
(105, 'Immunization', 'Active', '2026-10-06 14:02:54'),
(106, 'Pre-Natal', 'Inactive', '2026-10-06 14:02:54'),
(107, 'Medical Laboratory', 'Inactive', '2026-10-06 14:02:54'),
(108, 'Family Planning', 'Inactive', '2026-10-06 14:02:54'),
(109, 'Dental Examination/Tooth Extraction', 'Inactive', '2026-10-06 14:02:54'),
(110, 'Availment of anti-Tuberculosis Drugs', 'Inactive', '2026-10-06 14:02:54'),
(111, 'Child Care services', 'Inactive', '2026-10-06 14:02:54'),
(112, 'Availment of Tetanus Vaccine and Anti Rabies Vaccine', 'Inactive', '2026-10-06 14:02:54');

-- --------------------------------------------------------

--
-- Table structure for table `queue_information`
--

CREATE TABLE `queue_information` (
  `queue_info_id` int(11) NOT NULL,
  `queue_code` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_name` varchar(200) NOT NULL,
  `blood_pressure` varchar(100) NOT NULL,
  `respiratory_rate` varchar(100) NOT NULL,
  `temperature` varchar(100) NOT NULL,
  `pulse_rate` varchar(100) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `height` varchar(100) NOT NULL,
  `diagnosis` text NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `service_duration` timestamp NULL DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue_information`
--

INSERT INTO `queue_information` (`queue_info_id`, `queue_code`, `patient_id`, `service_name`, `blood_pressure`, `respiratory_rate`, `temperature`, `pulse_rate`, `weight`, `height`, `diagnosis`, `remarks`, `server_name`, `status`, `accepted_at`, `service_duration`, `created_at`) VALUES
(206, 'A0001', 250, 'Medical Consultation', '120/80', '18', '36', '72', '65', '168', 'none', NULL, NULL, 'Completed', '2026-10-03 10:15:34', '2026-10-03 10:15:43', '2026-10-03 16:07:20'),
(207, 'A0002', 251, 'Medical Consultation', '', '', '', '', '', '', '', NULL, NULL, 'Completed', '2026-10-03 10:25:42', '2026-10-03 10:25:47', '2026-10-03 18:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `service` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) NOT NULL,
  `service_duration` varchar(100) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `patient_id`, `queue_code`, `service`, `remarks`, `server_name`, `service_duration`, `transaction_date`) VALUES
(111, 250, 'A0001', 'Medical Consultation', 'None', 4, '0 days, 0 hours, 0 minutes', '2026-10-03 18:15:43'),
(112, 251, 'A0002', 'Medical Consultation', 'qwe', 4, '0 days, 0 hours, 0 minutes', '2026-10-03 18:25:47');

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
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointments_id`),
  ADD KEY `appointments_ibfk_2` (`server_name`),
  ADD KEY `appointments_ibfk_3` (`patient_id`);

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
  ADD UNIQUE KEY `patient_id` (`patient_id`),
  ADD UNIQUE KEY `account_token` (`account_token`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `patient_records`
--
ALTER TABLE `patient_records`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `account_token` (`account_token`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_patient_name` (`lastname`,`firstname`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `patient_auth`
--
ALTER TABLE `patient_auth`
  MODIFY `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `queue_display`
--
ALTER TABLE `queue_display`
  MODIFY `queue_display_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient_auth`
--
ALTER TABLE `patient_auth`
  ADD CONSTRAINT `patient_auth_ibfk_1` FOREIGN KEY (`email`) REFERENCES `patient_records` (`patient_id`),
  ADD CONSTRAINT `patient_auth_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`);

--
-- Constraints for table `queue_information`
--
ALTER TABLE `queue_information`
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
