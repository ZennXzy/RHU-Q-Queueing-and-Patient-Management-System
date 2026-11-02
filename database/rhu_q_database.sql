-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2026 at 05:10 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `barangay_id` int(11) NOT NULL,
  `barangay_address` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`barangay_id`, `barangay_address`, `created_at`) VALUES
(1, 'Amatong, Odiongan, Romblon', '0000-00-00 00:00:00'),
(2, 'Anahao, Odiongan, Romblon', '0000-00-00 00:00:00'),
(3, 'Bangon, Odiongan, Romblon', '0000-00-00 00:00:00'),
(4, 'Batiano, Odiongan, Romblon', '0000-00-00 00:00:00'),
(5, 'Budiong, Odiongan, Romblon', '0000-00-00 00:00:00'),
(6, 'Canduyong, Odiongan, Romblon', '0000-00-00 00:00:00'),
(7, 'Dapawan (Poblacion), Odiongan, Romblon', '0000-00-00 00:00:00'),
(8, 'Gabawan, Odiongan, Romblon', '0000-00-00 00:00:00'),
(9, 'Libertad, Odiongan, Romblon', '0000-00-00 00:00:00'),
(10, 'Ligaya (Poblacion), Odiongan, Romblon', '0000-00-00 00:00:00'),
(11, 'Liwanag (Poblacion), Odiongan, Romblon', '0000-00-00 00:00:00'),
(12, 'Liwayway (Poblacion), Odiongan, Romblon', '0000-00-00 00:00:00'),
(13, 'Malilico, Odiongan, Romblon', '0000-00-00 00:00:00'),
(14, 'Mayha, Odiongan, Romblon', '0000-00-00 00:00:00'),
(15, 'Panique, Odiongan, Romblon', '0000-00-00 00:00:00'),
(16, 'Pato-o, Odiongan, Romblon', '0000-00-00 00:00:00'),
(17, 'Poctoy, Odiongan, Romblon', '0000-00-00 00:00:00'),
(18, 'Progreso Este, Odiongan, Romblon', '0000-00-00 00:00:00'),
(19, 'Progreso Weste, Odiongan, Romblon', '0000-00-00 00:00:00'),
(20, 'Rizal, Odiongan, Romblon', '0000-00-00 00:00:00'),
(21, 'Tabing Dagat (Poblacion), Odiongan, Romblon', '0000-00-00 00:00:00'),
(22, 'Tabobo-an, Odiongan, Romblon', '0000-00-00 00:00:00'),
(23, 'Tuburan, Odiongan, Romblon', '0000-00-00 00:00:00'),
(24, 'Tumingad, Odiongan, Romblon', '0000-00-00 00:00:00'),
(25, 'Tulay, Odiongan, Romblon', '0000-00-00 00:00:00');

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
  `firstname` int(11) DEFAULT NULL,
  `lastname` int(11) DEFAULT NULL,
  `middlename` int(11) DEFAULT NULL,
  `suffix` int(11) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `account_token` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_auth`
--

--
-- Table structure for table `patient_records`
--

CREATE TABLE `patient_records` (
  `patient_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `address` int(11) DEFAULT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
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
  `date_registered` date DEFAULT current_timestamp(),
  `account_token` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_records`
--

--
-- Table structure for table `queue_information`
--

CREATE TABLE `queue_information` (
  `queue_info_id` int(11) NOT NULL,
  `queue_code` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_name` int(11) DEFAULT NULL,
  `service_snapshot` varchar(250) DEFAULT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(250) NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `status`, `created_at`) VALUES
(1, 'Medical Consultation', 'Inactive', '0000-00-00 00:00:00'),
(2, 'Immunization', 'Inactive', '0000-00-00 00:00:00'),
(4, 'Medical Laboratory', 'Inactive', '0000-00-00 00:00:00'),
(5, 'Family Planning', 'Inactive', '0000-00-00 00:00:00'),
(6, 'Dental Examination/Tooth Extraction', 'Inactive', '0000-00-00 00:00:00'),
(8, 'Child Care Services', 'Inactive', '0000-00-00 00:00:00'),
(15, 'Availment of Tetanus Vaccine and Anti Rabies Vaccine', 'Inactive', '2026-10-29 04:27:11'),
(16, 'Availment of Anti-Tuberculosis Drugs', 'Active', '2026-10-29 04:36:14'),
(23, 'Pre-Natal', 'Inactive', '2026-11-01 15:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `service` int(11) DEFAULT NULL,
  `service_snapshot` varchar(250) DEFAULT NULL,
  `blood_pressure` varchar(100) NOT NULL,
  `respiratory_rate` varchar(100) NOT NULL,
  `temperature` varchar(100) NOT NULL,
  `pulse_rate` varchar(100) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `height` varchar(100) NOT NULL,
  `diagnosis` text NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) NOT NULL,
  `service_duration` varchar(100) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
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
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`barangay_id`),
  ADD KEY `barangay_address` (`barangay_address`);

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
  ADD KEY `firstname` (`firstname`),
  ADD KEY `lastname` (`lastname`,`middlename`,`suffix`),
  ADD KEY `patient_auth_ibfk_5` (`middlename`),
  ADD KEY `patient_auth_ibfk_6` (`suffix`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `patient_records`
--
ALTER TABLE `patient_records`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `firstname` (`firstname`,`lastname`,`middlename`,`suffix`),
  ADD KEY `barangay_id` (`address`);

--
-- Indexes for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD PRIMARY KEY (`queue_info_id`),
  ADD UNIQUE KEY `server_name` (`server_name`),
  ADD KEY `service_name` (`service_name`),
  ADD KEY `queue_information_ibfk_1` (`patient_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `service_name` (`service_name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `transactions_ibfk_1` (`server_name`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `queue_code_3` (`queue_code`),
  ADD KEY `service_name` (`service`);

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
  MODIFY `appointments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=575;

--
-- AUTO_INCREMENT for table `patient_auth`
--
ALTER TABLE `patient_auth`
  MODIFY `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

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
  ADD CONSTRAINT `patient_auth_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_auth_ibfk_3` FOREIGN KEY (`firstname`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_auth_ibfk_4` FOREIGN KEY (`lastname`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_auth_ibfk_5` FOREIGN KEY (`middlename`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_auth_ibfk_6` FOREIGN KEY (`suffix`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE;

--
-- Constraints for table `patient_records`
--
ALTER TABLE `patient_records`
  ADD CONSTRAINT `patient_records_ibfk_1` FOREIGN KEY (`address`) REFERENCES `barangay` (`barangay_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD CONSTRAINT `queue_information_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `queue_information_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `queue_information_ibfk_3` FOREIGN KEY (`service_name`) REFERENCES `services` (`service_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`service`) REFERENCES `services` (`service_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
