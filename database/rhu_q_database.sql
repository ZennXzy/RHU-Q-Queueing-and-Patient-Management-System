-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2026 at 05:32 AM
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
(4, 'paul', '$2y$12$cyX4s6IqAzDcbVhL7VTO5OEhjfvWZBaFSp14jvWvMHxg73jmpcetC', 'Paul Anthony Fontamillas', 'paul@gmail.com', 'Online', 'Admin', '2026-08-09 00:14:45', '2026-09-25 09:46:47'),
(10, 'carlo', '$2y$12$BBTwb7UVHzNPdFW/zZU0XOEoxlo2ZNW2eFPjBHhoh4pBupiTlRSOG', 'Carlo Agustin', 'carloagustin@gmail.com', 'Online', 'Doctor/Staff', '2026-09-08 19:47:26', '2026-09-29 10:00:20'),
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
  `contact_number` varchar(11) NOT NULL,
  `service` varchar(250) NOT NULL,
  `remarks` text NOT NULL,
  `server_name` int(11) NOT NULL,
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
(17, 184, '09863453210', 'Medical Consultation', 'qweqweqew', 4, 'Declined', 'qweqwe', '2026-11-07', '2026-09-25 10:02:28', '2026-09-25 11:05:22');

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
(2, 'Account carlo created successfully.', '2026-09-08 19:47:26'),
(4, 'Account carlo logged in successfully.', '2026-09-08 20:13:05'),
(5, 'Account carlo logged in successfully.', '2026-09-08 20:16:27'),
(6, 'Account carlo logged in successfully.', '2026-09-08 22:14:14'),
(7, 'Account carlo just logged out.', '2026-09-08 22:15:45'),
(8, 'Account alex created successfully.', '2026-09-08 22:17:31'),
(9, 'Account alex logged in successfully.', '2026-09-08 22:17:45'),
(10, 'Account alex just logged out.', '2026-09-08 22:26:20'),
(11, 'Account alex logged in successfully.', '2026-09-08 22:27:34'),
(12, 'Account alex just logged out.', '2026-09-08 22:34:29'),
(13, 'Account alex logged in successfully.', '2026-09-08 22:42:32'),
(14, 'Account alex just logged out.', '2026-09-08 22:54:43'),
(15, 'Account carlo logged in successfully.', '2026-09-08 22:57:27'),
(16, 'Account carlo just logged out.', '2026-09-09 00:29:13'),
(17, 'Account paul just logged out.', '2026-09-09 00:29:29'),
(18, 'Account paul logged in successfully.', '2026-09-09 15:28:33'),
(19, 'Account paul just logged out.', '2026-09-09 18:07:05'),
(20, 'Account paul logged in successfully.', '2026-09-09 18:07:15'),
(21, 'Account paul just logged out.', '2026-09-09 18:07:41'),
(22, 'Account paul logged in successfully.', '2026-09-09 18:07:50'),
(23, 'Account paul just logged out.', '2026-09-09 23:23:49'),
(24, 'Account paul logged in successfully.', '2026-09-09 23:23:59'),
(25, 'Account carlo logged in successfully.', '2026-09-09 23:45:15'),
(26, 'Account carlo logged in successfully.', '2026-09-10 01:04:10'),
(27, 'Account paul just logged out.', '2026-09-10 01:04:21'),
(28, 'Account paul logged in successfully.', '2026-09-10 01:04:29'),
(29, 'Account carlo just logged out.', '2026-09-10 01:20:44'),
(30, 'Account alex logged in successfully.', '2026-09-10 01:21:03'),
(31, 'Account alex just logged out.', '2026-09-10 01:27:44'),
(32, 'Account alex logged in successfully.', '2026-09-10 01:27:49'),
(33, 'Account paul just logged out.', '2026-09-10 01:28:01'),
(34, 'Account paul logged in successfully.', '2026-09-10 01:28:08'),
(35, 'Account paul just logged out.', '2026-09-10 01:29:04'),
(36, 'Account carlo logged in successfully.', '2026-09-10 01:29:12'),
(37, 'Account carlo logged in successfully.', '2026-09-10 01:32:15'),
(38, 'Account carlo logged in successfully.', '2026-09-10 01:36:37'),
(39, 'Account carlo logged in successfully.', '2026-09-10 01:40:47'),
(40, 'Account paul logged in successfully.', '2026-09-10 01:41:44'),
(41, 'Account paul just logged out.', '2026-09-10 01:42:02'),
(42, 'Account carlo logged in successfully.', '2026-09-10 01:43:02'),
(43, 'Account paul logged in successfully.', '2026-09-10 01:45:09'),
(44, 'Account carlo logged in successfully.', '2026-09-10 01:51:26'),
(45, 'Account paul just logged out.', '2026-09-10 01:51:37'),
(46, 'Account alex logged in successfully.', '2026-09-10 01:51:46'),
(47, 'Account carlo just logged out.', '2026-09-10 01:53:21'),
(48, 'Account paul logged in successfully.', '2026-09-10 01:53:29'),
(49, 'Account paul just logged out.', '2026-09-10 01:54:59'),
(50, 'Account carlo logged in successfully.', '2026-09-10 01:55:05'),
(51, 'Account carlo just logged out.', '2026-09-10 02:04:03'),
(52, 'Account paul logged in successfully.', '2026-09-10 02:04:10'),
(53, 'Account alex logged in successfully.', '2026-09-10 02:07:45'),
(54, 'Account carlo logged in successfully.', '2026-09-10 02:08:44'),
(55, 'Account paul just logged out.', '2026-09-10 02:23:06'),
(56, 'Account carlo just logged out.', '2026-09-10 02:23:29'),
(57, 'Account carlo logged in successfully.', '2026-09-10 02:23:41'),
(58, 'Account carlo just logged out.', '2026-09-10 02:33:32'),
(59, 'Account paul logged in successfully.', '2026-09-10 02:33:41'),
(60, 'Account paul just logged out.', '2026-09-10 02:34:05'),
(61, 'Account carlo logged in successfully.', '2026-09-10 02:34:12'),
(62, 'Account carlo just logged out.', '2026-09-10 02:43:42'),
(63, 'Account paul logged in successfully.', '2026-09-10 02:43:49'),
(64, 'Account paul just logged out.', '2026-09-10 02:48:57'),
(65, 'Account carlo logged in successfully.', '2026-09-10 02:49:04'),
(66, 'Account carlo just logged out.', '2026-09-10 02:49:46'),
(67, 'Account carlo logged in successfully.', '2026-09-10 02:49:52'),
(68, 'Account carlo just logged out.', '2026-09-10 02:50:59'),
(69, 'Account carlo logged in successfully.', '2026-09-10 02:51:07'),
(70, 'Account alex logged in successfully.', '2026-09-10 03:01:26'),
(71, 'Account paul logged in successfully.', '2026-09-10 04:11:44'),
(72, 'Account paul just logged out.', '2026-09-10 04:12:16'),
(73, 'Account paul logged in successfully.', '2026-09-10 04:31:03'),
(74, 'Account alex logged in successfully.', '2026-09-10 04:34:24'),
(75, 'Account carlo just logged out.', '2026-09-10 04:35:29'),
(76, 'Account paul logged in successfully.', '2026-09-11 00:46:55'),
(77, 'Account carlo logged in successfully.', '2026-09-11 01:05:08'),
(78, 'Account paul logged in successfully.', '2026-09-11 04:35:04'),
(79, 'Account paul logged in successfully.', '2026-09-12 17:45:15'),
(80, 'Account sample created successfully.', '2026-09-12 17:54:09'),
(81, 'Account sample logged in successfully.', '2026-09-12 18:52:54'),
(82, 'Account sample has been updated.', '2026-09-12 19:04:29'),
(83, 'Account sample logged in successfully.', '2026-09-12 19:06:01'),
(84, 'Account paul logged in successfully.', '2026-09-14 18:35:28'),
(85, 'Account paul just logged out.', '2026-09-14 19:34:43'),
(86, 'Account paul logged in successfully.', '2026-09-14 19:34:51'),
(87, 'Account paul has been updated.', '2026-09-14 21:00:56'),
(88, 'Account carlo has been updated.', '2026-09-14 21:01:42'),
(89, 'Account carlo has been updated.', '2026-09-14 21:01:55'),
(90, 'Account paul has been updated.', '2026-09-14 21:01:57'),
(91, 'Account paul logged in successfully.', '2026-09-14 21:07:07'),
(92, 'Account paul logged in successfully.', '2026-09-14 21:13:57'),
(93, 'Account paul logged in successfully.', '2026-09-14 21:14:36'),
(94, 'Account paul logged in successfully.', '2026-09-14 21:31:29'),
(95, 'Account paul logged in successfully.', '2026-09-14 21:32:40'),
(96, 'Account paul logged in successfully.', '2026-09-14 21:45:53'),
(97, 'Account paul logged in successfully.', '2026-09-14 21:47:19'),
(98, 'Account paul logged in successfully.', '2026-09-14 21:49:09'),
(99, 'Account paul logged in successfully.', '2026-09-14 21:49:32'),
(100, 'Account paul logged in successfully.', '2026-09-14 21:51:34'),
(101, 'Account paul logged in successfully.', '2026-09-14 21:55:42'),
(102, 'Account paul logged in successfully.', '2026-09-15 16:26:06'),
(103, 'Account alex logged in successfully.', '2026-09-15 16:26:40'),
(104, 'Account sample logged in successfully.', '2026-09-15 17:02:04'),
(105, 'Account paul logged in successfully.', '2026-09-15 18:10:50'),
(106, 'Account paul just logged out.', '2026-09-15 19:43:51'),
(107, 'Account sample logged in successfully.', '2026-09-15 19:44:32'),
(108, 'Account carlo logged in successfully.', '2026-09-15 19:44:42'),
(109, 'Account carlo just logged out.', '2026-09-15 19:45:40'),
(110, 'Account paul logged in successfully.', '2026-09-15 19:45:48'),
(111, 'Account paul logged in successfully.', '2026-09-15 21:16:05'),
(112, 'Account paul logged out.', '2026-09-16 18:09:03'),
(113, 'Account paul logged in successfully.', '2026-09-16 18:14:57'),
(114, 'Account carlo logged in successfully.', '2026-09-16 19:38:05'),
(115, 'Account alex logged in successfully.', '2026-09-16 20:27:51'),
(116, 'Account alex logged out.', '2026-09-16 20:49:08'),
(117, 'Account sample logged in successfully.', '2026-09-16 20:49:18'),
(118, 'Account paul just logged out.', '2026-09-17 01:13:55'),
(119, 'Account paul logged in successfully.', '2026-09-17 01:14:14'),
(120, 'Account jamesbond created successfully.', '2026-09-17 01:50:46'),
(121, 'Account paul just logged out.', '2026-09-17 01:52:11'),
(122, 'Account paul logged in successfully.', '2026-09-17 13:43:14'),
(123, 'Account paul just logged out.', '2026-09-17 13:44:07'),
(124, 'Account paul logged in successfully.', '2026-09-17 13:44:14'),
(125, 'Account paul just logged out.', '2026-09-17 13:44:25'),
(126, 'Account paul logged in successfully.', '2026-09-17 13:44:37'),
(127, 'Account sample logged in successfully.', '2026-09-17 16:47:50'),
(128, 'Account paul just logged out.', '2026-09-17 18:29:24'),
(129, 'Account sample just logged out.', '2026-09-17 18:29:32'),
(130, 'Account paul logged in successfully.', '2026-09-17 18:29:40'),
(131, 'Account carlo logged in successfully.', '2026-09-18 15:04:38'),
(132, 'Account carlo logged in successfully.', '2026-09-18 16:38:50'),
(133, 'Account carlo just logged out.', '2026-09-18 19:26:13'),
(134, 'Account carlo logged in successfully.', '2026-09-18 19:37:17'),
(135, 'Account carlo has been updated.', '2026-09-18 20:09:41'),
(136, 'Account  completed a task.', '2026-09-19 00:32:56'),
(137, 'Account paul logged in successfully.', '2026-09-19 00:34:03'),
(138, 'Account  completed a task.', '2026-09-19 00:34:21'),
(139, 'Account paul logged in successfully.', '2026-09-19 00:35:45'),
(140, 'Account Paul Anthony Fontamillas completed a task.', '2026-09-19 00:36:01'),
(141, 'Account Carlo Agustin completed a task.', '2026-09-19 00:36:42'),
(144, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 01:19:54'),
(145, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 01:44:52'),
(146, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 01:45:24'),
(147, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 01:46:14'),
(148, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 01:50:03'),
(149, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 02:12:03'),
(150, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 03:05:58'),
(151, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 03:13:01'),
(152, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 03:23:37'),
(153, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 03:25:07'),
(154, 'Paul Anthony Fontamillas completed a task.', '2026-09-19 03:28:50'),
(155, 'carlo logged in successfully.', '2026-09-19 03:40:44'),
(156, 'Paul Anthony Fontamillas completed a task.', '2026-09-21 17:26:03'),
(157, 'Paul Anthony Fontamillas completed a task.', '2026-09-21 17:29:48'),
(158, 'paul added an Appointment.', '2026-09-21 17:29:48'),
(159, 'paul just logged out.', '2026-09-22 13:36:41'),
(160, 'paul logged in successfully.', '2026-09-22 13:37:38'),
(161, 'carlo logged in successfully.', '2026-09-22 16:07:19'),
(164, 'Paul Anthony Fontamillas completed a task.', '2026-09-22 23:22:51'),
(165, 'paul added an Appointment.', '2026-09-22 23:22:51'),
(166, 'carlo just logged out.', '2026-09-22 23:53:40'),
(167, 'paul just logged out.', '2026-09-23 00:26:25'),
(168, 'paul logged in successfully.', '2026-09-23 00:43:04'),
(169, 'carlo logged in successfully.', '2026-09-23 02:04:31'),
(170, 'carlo just logged out.', '2026-09-23 02:05:02'),
(171, 'paul just logged out.', '2026-09-23 02:05:45'),
(172, 'paul logged in successfully.', '2026-09-25 09:46:47'),
(173, 'Paul Anthony Fontamillas completed a task.', '2026-09-25 10:02:11'),
(174, 'paul added an Appointment.', '2026-09-25 10:02:11'),
(175, 'Paul Anthony Fontamillas completed a task.', '2026-09-25 10:02:28'),
(176, 'paul added an Appointment.', '2026-09-25 10:02:28'),
(177, 'root declined an appointment.', '2026-09-25 10:47:34'),
(178, 'root declined an appointment.', '2026-09-25 11:05:23'),
(179, 'root added a queue from appointments.', '2026-09-25 11:49:51'),
(180, 'Paul Anthony Fontamillas completed a task.', '2026-09-25 11:53:48'),
(181, 'paul added an Appointment.', '2026-09-25 11:53:48'),
(182, 'root added a queue from appointments.', '2026-09-25 11:53:57'),
(183, 'Paul Anthony Fontamillas completed a task.', '2026-09-25 14:05:58'),
(184, 'carlo logged in successfully.', '2026-09-25 16:04:09'),
(185, 'Carlo Agustin completed a task.', '2026-09-25 16:04:54'),
(186, 'carlo logged in successfully.', '2026-09-29 10:00:21');

-- --------------------------------------------------------

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

INSERT INTO `patient_records` (`patient_id`, `firstname`, `lastname`, `middlename`, `suffix`, `address`, `age`, `gender`, `date_of_birth`, `contact_number`, `email`, `employment_status`, `occupation`, `business_name`, `civil_status`, `religion`, `mothers_name`, `fathers_name`, `spouses_name`, `phil_status`, `phil_number`, `principal_member`, `yakap_member`, `date_registered`) VALUES
(115, 'Juan', 'Dela Cruz', 'Santos', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1994-05-12', '09171234567', 'juan.delacruz@example.com', 'Employed', 'Teacher', 'DepEd', 'Single', 'Roman Catholic', 'Maria Santos', 'Jose Dela Cruz', NULL, 'Member', '12-050192837-1', NULL, 'No', '2026-08-13'),
(116, 'Maria Clara', 'Santos', 'Reyes', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1999-08-23', '09189876543', 'maria.santos@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Elena Reyes', 'Tomas Santos', NULL, 'Dependent', '12-050192837-1', 'Tomas Santos', 'Yes', '2026-08-13'),
(117, 'Jose', 'Rizal', 'Protasio', 'Jr.', 'Zone 2, Polangui, Albay', 0, 'Male', '1985-06-19', '09191112222', 'jose.rizal@example.com', 'Self-Employed', 'Doctor', 'Rizal Clinic', 'Married', 'Roman Catholic', 'Teodora Alonso', 'Francisco Mercado', 'Josephine Bracken', 'Member', '12-098765432-3', NULL, 'No', '2026-08-13'),
(119, 'Melchora', 'Aquino', 'Ramos', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1952-01-06', '09215556666', 'melchora.a@example.com', 'Unemployed', NULL, NULL, 'Widowed', 'Roman Catholic', 'Valentina de Aquino', 'Juan Ramos', 'Fulgence Ramos', 'Member', '12-112233445-5', NULL, 'Yes', '2026-08-13'),
(121, 'Gabriela', 'Silang', 'Caniño', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1988-03-19', '09239990000', 'gabriela.s@example.com', 'Employed', 'Nurse', 'RHU Clinic', 'Widowed', 'Roman Catholic', 'Monika Caniño', 'Anselmo Caniño', 'Diego Silang', 'Member', '12-334455667-8', NULL, 'No', '2026-08-13'),
(122, 'Emilio', 'Aguinaldo', 'Famy', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1969-03-22', '09241239876', 'emilio.a@example.com', 'Employed', 'Barangay Official', 'LGU Polangui', 'Married', 'Roman Catholic', 'Trinidad Famy', 'Carlos Aguinaldo', 'Hilaria del Rosario', 'Member', '12-445566778-9', NULL, 'No', '2026-08-13'),
(123, 'Marcelo', 'Del Pilar', 'Hilario', 'Sr.', 'Zone 1, Polangui, Albay', 0, 'Male', '1975-08-30', '09252348765', 'marcelo.dp@example.com', 'Self-Employed', 'Writer', 'Bicol Press', 'Married', 'Roman Catholic', 'Blasa Hilario', 'Julian del Pilar', 'Marciana Valenzuela', 'Member', '12-556677889-0', NULL, 'No', '2026-08-13'),
(124, 'Juan Luna', 'Novicio', 'San Pedro', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1982-10-23', '09263457654', 'juan.luna@example.com', 'Self-Employed', 'Painter', 'Luna Arts Studio', 'Married', 'Roman Catholic', 'Laureana Novicio', 'Joaquin Luna', 'Paz Pardo de Tavera', 'Member', '12-667788990-1', NULL, 'No', '2026-08-13'),
(126, 'Gregorio', 'Del Pilar', 'Semprucci', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1997-11-14', '09285675432', 'gregorio.dp@example.com', 'Employed', 'Security Officer', 'Security Corp', 'Single', 'Roman Catholic', 'Felipa Semprucci', 'Fernando del Pilar', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(127, 'Emilio', 'Jacinto', 'Dizon', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1996-12-15', '09296784321', 'emilio.j@example.com', 'Employed', 'Accountant', 'Polangui Rural Bank', 'Single', 'Roman Catholic', 'Josefa Dizon', 'Mariano Jacinto', NULL, 'Member', '12-889900112-3', NULL, 'No', '2026-08-13'),
(128, 'Lapu', 'Lapu', 'Dimagiba', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1970-01-01', '09307893210', 'lapulapu@example.com', 'Self-Employed', 'Fisherman', 'Self', 'Married', 'Other', 'Kagatan Dimagiba', 'Cali Pulako', 'Bulakna Dimagiba', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(129, 'Sultan', 'Kudarat', 'Nawal', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1965-05-05', '09318902109', 'kudarat@example.com', 'Self-Employed', 'Farmer', 'Kudarat Farms', 'Married', 'Islam', 'Fatima Nawal', 'Buisan Nawal', 'Paramisuli Nawal', 'Member', '12-990011223-4', NULL, 'Yes', '2026-08-13'),
(130, 'Panday', 'Pira', 'Alcantara', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1960-06-12', '09329011098', 'panday.pira@example.com', 'Self-Employed', 'Blacksmith', 'Pira Works', 'Married', 'Roman Catholic', 'Maria Alcantara', 'Pedro Pira', 'Elena Alcantara', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(131, 'Francisco', 'Balagtas', 'Baltazar', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1973-04-02', '09330120187', 'balagtas@example.com', 'Employed', 'Poet', 'LGU Cultural Office', 'Married', 'Roman Catholic', 'Juana de la Cruz', 'George Baltazar', 'Juana Tiambeng', 'Member', '12-102938475-6', NULL, 'No', '2026-08-13'),
(132, 'Graciano', 'Lopez Jaena', 'Nellas', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1976-12-18', '09341231098', 'graciano.lj@example.com', 'Employed', 'Journalist', 'Bicol Herald', 'Single', 'Roman Catholic', 'Maria Nellas', 'Placido Lopez', NULL, 'Member', '12-203948576-7', NULL, 'No', '2026-08-13'),
(133, 'Diego', 'Silang', 'Andaya', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1983-12-16', '09352342109', 'diego.silang@example.com', 'Employed', 'Driver', 'LGU Transport', 'Married', 'Roman Catholic', 'Nicole Andaya', 'Miguel Silang', 'Gabriela Silang', 'Dependent', '12-334455667-8', 'Gabriela Silang', 'No', '2026-08-13'),
(134, 'Teresa', 'Magbanua', 'Ferraris', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1981-10-13', '09363453210', 'teresa.m@example.com', 'Employed', 'School Principal', 'Polangui National High', 'Married', 'Roman Catholic', 'Alejandra Ferraris', 'Juan Magbanua', 'Alejandro Balderas', 'Member', '12-304958671-8', NULL, 'No', '2026-08-13'),
(135, 'Mariano', 'Ponce', 'Collantes', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1974-03-23', '09374564321', 'mariano.p@example.com', 'Employed', 'Researcher', 'DOST Bicol', 'Married', 'Roman Catholic', 'Maria Collantes', 'Mariano Ponce Sr.', 'Maria Del Rosario', 'Member', '12-405968712-9', NULL, 'No', '2026-08-13'),
(136, 'Pedro', 'Paterno', 'De Vera', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1967-02-27', '09385675432', 'pedro.paterno@example.com', 'Self-Employed', 'Lawyer', 'Paterno Legal', 'Married', 'Roman Catholic', 'Carmen De Vera', 'Maximo Paterno', 'Luisa Piñeiro', 'Member', '12-506978823-0', NULL, 'No', '2026-08-13'),
(138, 'Vicente', 'Lukban', 'Rilles', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1971-02-11', '09407897654', 'vicente.l@example.com', 'Employed', 'Civil Engineer', 'DPWH', 'Married', 'Roman Catholic', 'Luz Rilles', 'Agustin Lukban', 'Sofia Surtida', 'Member', '12-607988934-1', NULL, 'No', '2026-08-13'),
(139, 'Marina', 'Dizon', 'Santiago', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1986-07-18', '09418908765', 'marina.dizon@example.com', 'Employed', 'Social Worker', 'DSWD', 'Married', 'Roman Catholic', 'Roberta Santiago', 'Jose Dizon', 'Jose Turiano', 'Member', '12-708999045-2', NULL, 'No', '2026-08-13'),
(141, 'Trinidad', 'Tecson', 'Perez', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1978-11-18', '09430120987', 'trinidad.t@example.com', 'Employed', 'Red Cross Staff', 'Philippine Red Cross', 'Separated', 'Roman Catholic', 'Monica Perez', 'Rafael Tecson', NULL, 'Member', '12-809010156-3', NULL, 'No', '2026-08-13'),
(142, 'Gliceria', 'Marella', 'Villavicencio', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1962-05-13', '09441231098', 'gliceria.v@example.com', 'Self-Employed', 'Real Estate Agent', 'Self', 'Widowed', 'Roman Catholic', 'Mariana Villavicencio', 'Vicente Marella', 'Eulalio Villavicencio', 'Member', '12-910121267-4', NULL, 'No', '2026-08-13'),
(144, 'Esperanza', 'Limjap', 'Escolar', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1992-09-08', '09463453210', 'esperanza.l@example.com', 'Employed', 'HR Officer', 'Polangui Mall', 'Married', 'Roman Catholic', 'Francisca Escolar', 'Mariano Limjap', 'Sergio Osmeña', 'Dependent', '12-122343489-6', 'Sergio Osmeña', 'No', '2026-08-13'),
(146, 'Diosdado', 'Macapagal', 'Pangan', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1970-09-28', '09485675432', 'diosdado.m@example.com', 'Employed', 'Economist', 'LGU Planning', 'Married', 'Roman Catholic', 'Romana Pangan', 'Urbano Macapagal', 'Evangelina Macaraeg', 'Member', '12-344565601-8', NULL, 'No', '2026-08-13'),
(147, 'Ramon', 'Magsaysay', 'Del Fierro', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1967-08-31', '09496786543', 'ramon.m@example.com', 'Self-Employed', 'Mechanic', 'Magsaysay Auto Repair', 'Married', 'Roman Catholic', 'Perfecta Del Fierro', 'Ezequiel Magsaysay', 'Luz Banzon', 'Member', '12-455676712-9', NULL, 'No', '2026-08-13'),
(148, 'Manuel', 'Roxas', 'Acuña', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1965-01-01', '09507897654', 'manuel.roxas@example.com', 'Employed', 'Auditor', 'COA Bicol', 'Married', 'Roman Catholic', 'Rosario Acuña', 'Gerardo Roxas', 'Trinidad de Leon', 'Member', '12-566787823-0', NULL, 'No', '2026-08-13'),
(149, 'Manuel', 'Quezon', 'Molina', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1978-08-19', '09518908765', 'manuel.quezon@example.com', 'Employed', 'Government Staff', 'Provincial Capitol', 'Married', 'Roman Catholic', 'Maria Molina', 'Lucio Quezon', 'Aurora Aragon', 'Member', '12-677898934-1', NULL, 'No', '2026-08-13'),
(150, 'Sergio', 'Osmeña', 'Suico', 'Sr.', 'Zone 1, Polangui, Albay', 0, 'Male', '1978-09-09', '09529019876', 'sergio.osmena@example.com', 'Employed', 'Administrator', 'Polangui College', 'Married', 'Roman Catholic', 'Juana Suico', 'Pedro Osmeña', 'Esperanza Limjap', 'Member', '12-122343489-6', NULL, 'No', '2026-08-13'),
(151, 'Jose', 'Laurel', 'Paciano', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1981-03-09', '09530120987', 'jose.laurel@example.com', 'Self-Employed', 'Judge/Lawyer', 'Laurel Office', 'Married', 'Roman Catholic', 'Jacoba Paciano', 'Sotero Laurel', 'Paciencia Hidalgo', 'Member', '12-788909045-2', NULL, 'No', '2026-08-13'),
(152, 'Elpidio', 'Quirino', 'Rivera', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1970-11-16', '09541231098', 'elpidio.q@example.com', 'Employed', 'Tax Consultant', 'BIR Polangui', 'Widowed', 'Roman Catholic', 'Gregoria Rivera', 'Mariano Quirino', 'Alicia Syquia', 'Member', '12-899010156-3', NULL, 'No', '2026-08-13'),
(154, 'Fidel', 'Ramos', 'Valdez', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1968-03-18', '09563453210', 'fidel.ramos@example.com', 'Employed', 'Retired Military', 'AFP', 'Married', 'Protestant', 'Angela Valdez', 'Narciso Ramos', 'Amelita Martinez', 'Member', '12-011232378-5', NULL, 'No', '2026-08-13'),
(155, 'Gloria', 'Arroyo', 'Macapagal', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1987-04-05', '09574564321', 'gloria.arroyo@example.com', 'Employed', 'Professor', 'BU Polangui', 'Married', 'Roman Catholic', 'Evangelina Macaraeg', 'Diosdado Macapagal', 'Jose Miguel Arroyo', 'Member', '12-122343489-0', NULL, 'No', '2026-08-13'),
(158, 'Ferdinand', 'Marcos', 'Edralin', 'Jr.', 'Zone 4, Polangui, Albay', 0, 'Male', '1977-09-13', '09607897654', 'bongbong.marcos@example.com', 'Employed', 'Civil Servant', 'Provincial Office', 'Married', 'Roman Catholic', 'Imelda Romualdez', 'Ferdinand Marcos Sr.', 'Louise Araneta', 'Member', '12-455676712-3', NULL, 'No', '2026-08-13'),
(159, 'Leni', 'Robredo', 'Gerona', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1985-04-23', '09618908765', 'leni.robredo@example.com', 'Self-Employed', 'Human Rights Lawyer', 'Free Legal Assistance', 'Widowed', 'Roman Catholic', 'Salvacion Santo Tomas', 'Antonio Gerona', 'Jesse Robredo', 'Member', '12-566787823-4', NULL, 'No', '2026-08-13'),
(160, 'Manny', 'Pacquiao', 'Dapidran', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1988-12-17', '09629019876', 'manny.pacquiao@example.com', 'Self-Employed', 'Athlete/Coach', 'Pacquiao Gym', 'Married', 'Evangelical', 'Dionisia Dapidran', 'Rosalio Pacquiao', 'Jinkee Jamora', 'Member', '12-677898934-5', NULL, 'No', '2026-08-13'),
(161, 'Pia', 'Wurtzbach', 'Alonzo', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1999-09-24', '09630120987', 'pia.wurtzbach@example.com', 'Self-Employed', 'Model/Host', 'Self', 'Married', 'Roman Catholic', 'Cheryl Alonzo', 'Uwe Wurtzbach', 'Jeremy Jauncey', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(163, 'Lea', 'Salonga', 'Imutan', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1991-02-22', '09652342109', 'lea.salonga@example.com', 'Self-Employed', 'Performer', 'Salonga Productions', 'Married', 'Roman Catholic', 'Ligaya Imutan', 'Feliciano Salonga', 'Robert Chien', 'Member', '12-788909045-6', NULL, 'No', '2026-08-13'),
(164, 'Efren', 'Reyes', 'Manalang', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1974-08-26', '09663453210', 'efren.reyes@example.com', 'Self-Employed', 'Sports Instructor', 'Bata Sports Center', 'Married', 'Roman Catholic', 'Romana Manalang', 'Pedro Reyes', 'Susan Reyes', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(166, 'Hidilyn', 'Diaz', 'Francisco', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1991-02-20', '09685675432', 'hidilyn.diaz@example.com', 'Employed', 'Fitness Instructor', 'Weightlifting Center', 'Married', 'Roman Catholic', 'Emelita Francisco', 'Eduardo Diaz', 'Julius Naranjo', 'Member', '12-899010156-7', NULL, 'No', '2026-08-13'),
(167, 'Miriam', 'Defensor', 'Palma', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1975-06-15', '09696786543', 'miriam.santiago@example.com', 'Employed', 'Professor', 'Polangui Law School', 'Married', 'Roman Catholic', 'Digna Palma', 'Benjamin Defensor', 'Narciso Santiago', 'Member', '12-900121267-8', NULL, 'No', '2026-08-13'),
(168, 'Juan', 'Flavier', 'Martin', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1970-03-03', '09707897654', 'juan.flavier@example.com', 'Employed', 'Doctor', 'DOH Regional', 'Married', 'Roman Catholic', 'Dominadora Martin', 'Victorino Flavier', 'Susan Flavier', 'Member', '12-011232378-9', NULL, 'No', '2026-08-13'),
(169, 'Fe', 'Del Mundo', 'Villanueva', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1961-11-27', '09718908765', 'fe.delmundo@example.com', 'Self-Employed', 'Pediatrician', 'Del Mundo Clinic', 'Single', 'Roman Catholic', 'Paz Villanueva', 'Bernardo Del Mundo', NULL, 'Member', '12-122343489-1', NULL, 'No', '2026-08-13'),
(170, 'Gregorio', 'Zara', 'Ycaro', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1962-03-08', '09729019876', 'gregorio.zara@example.com', 'Employed', 'Engineer', 'Innovation Tech', 'Married', 'Roman Catholic', 'Clementina Ycaro', 'Antonio Zara', 'Liliosa Rivera', 'Member', '12-233454590-2', NULL, 'No', '2026-08-13'),
(171, 'Julian', 'Felipe', 'Reyes', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1981-01-28', '09730120987', 'julian.felipe@example.com', 'Self-Employed', 'Music Teacher', 'Felipe Music', 'Married', 'Roman Catholic', 'Victoria Reyes', 'Justo Felipe', 'Teresa Ventura', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(172, 'Guillermo', 'Tolentino', 'Espinosa', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1980-07-24', '09741231098', 'guillermo.t@example.com', 'Self-Employed', 'Sculptor', 'Tolentino Arts', 'Married', 'Seventh-day Adventist', 'Paz Espinosa', 'Isidro Tolentino', 'Paz Raymundo', 'Member', '12-344565601-3', NULL, 'No', '2026-08-13'),
(173, 'Nick', 'Joaquin', 'Marquez', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1977-05-04', '09752342109', 'nick.joaquin@example.com', 'Self-Employed', 'Author', 'Self', 'Single', 'Roman Catholic', 'Salome Marquez', 'Leocadio Joaquin', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(174, 'F. Sionil', 'Jose', 'Morales', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1974-12-03', '09763453210', 'fsionil.jose@example.com', 'Self-Employed', 'Bookstore Owner', 'Solidaridad Books', 'Married', 'Roman Catholic', 'Sofia Morales', 'Antonio Jose', 'Teresita Jovellanos', 'Member', '12-455676712-4', NULL, 'No', '2026-08-13'),
(175, 'Lino', 'Brocka', 'Ortiz', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1989-04-03', '09774564321', 'lino.brocka@example.com', 'Self-Employed', 'Filmmaker', 'CineBicol', 'Single', 'Roman Catholic', 'Pilar Ortiz', 'Regino Brocka', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(176, 'Nora', 'Aunor', 'Villamayor', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1993-05-21', '09785675432', 'nora.aunor@example.com', 'Self-Employed', 'Actress', 'Aunor Media', 'Separated', 'Roman Catholic', 'Antonia Villamayor', 'Eustaquio Villamayor', NULL, 'Member', '12-566787823-5', NULL, 'Yes', '2026-08-13'),
(177, 'Vilma', 'Santos', 'Recto', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1993-11-03', '09796786543', 'vilma.santos@example.com', 'Employed', 'Public Servant', 'Provincial Board', 'Married', 'Roman Catholic', 'Milagros Santos', 'Amado Santos', 'Ralph Recto', 'Member', '12-677898934-6', NULL, 'No', '2026-08-13'),
(178, 'Fernando', 'Poe', 'Kelley', 'Jr.', 'Zone 4, Polangui, Albay', 0, 'Male', '1979-08-20', '09807897654', 'fpj.poe@example.com', 'Self-Employed', 'Actor/Director', 'FPJ Productions', 'Married', 'Roman Catholic', 'Bessie Kelley', 'Fernando Poe Sr.', 'Susan Roces', 'Member', '12-788909045-7', NULL, 'No', '2026-08-13'),
(180, 'Vic', 'Sotto', 'Castelo', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1974-04-28', '09829019876', 'vic.sotto@example.com', 'Self-Employed', 'TV Host', 'M-Zet Productions', 'Married', 'Roman Catholic', 'Herminia Castelo', 'Marcelino Sotto', 'Pauleen Luna', 'Member', '12-900121267-9', NULL, 'No', '2026-08-13'),
(181, 'Joey', 'De Leon', 'Ramos', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1976-10-14', '09830120987', 'joey.deleon@example.com', 'Self-Employed', 'Writer/Host', 'Self', 'Married', 'Roman Catholic', 'Aida Ramos', 'Jose De Leon Sr.', 'Eileen Macapagal', 'Member', '12-011232378-0', NULL, 'No', '2026-08-13'),
(182, 'Francis', 'Magalona', 'Arroyo', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1984-10-04', '09841231098', 'francis.m@example.com', 'Self-Employed', 'Musician', 'FM Music', 'Married', 'Roman Catholic', 'Teresita Arroyo', 'Pancho Magalona', 'Pia Arroyo', 'Member', '12-122343489-2', NULL, 'No', '2026-08-13'),
(183, 'Eraserheads', 'Buendia', 'Vega', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1990-11-02', '09852342109', 'ely.buendia@example.com', 'Self-Employed', 'Musician', 'Offrecord', 'Single', 'Roman Catholic', 'Lina Vega', 'Ely Buendia Sr.', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(184, 'Freddie', 'Aguilar', 'Pascual', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1973-03-14', '09863453210', 'freddie.aguilar@example.com', 'Self-Employed', 'Singer', 'Anak Music', 'Married', 'Islam', 'Luz Pascual', 'Raymundo Aguilar', 'Jolie Aguilar', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(185, 'Gary', 'Valenciano', 'Santiago', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1984-08-06', '09874564321', 'gary.v@example.com', 'Self-Employed', 'Performer', 'Manik Music', 'Married', 'Protestant', 'Grimilda Santiago', 'Vicente Valenciano', 'Angeli Pangilinan', 'Member', '12-233454590-3', NULL, 'No', '2026-08-13'),
(186, 'Martin', 'Nievera', 'Ramon', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1982-02-05', '09885675432', 'martin.nievera@example.com', 'Self-Employed', 'Singer', 'Concert King Inc', 'Separated', 'Roman Catholic', 'Lourdes Ramon', 'Bert Nievera', NULL, 'Member', '12-344565601-4', NULL, 'No', '2026-08-13'),
(187, 'Regine', 'Velasquez', 'Alcasid', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1990-04-22', '09896786543', 'regine.velasquez@example.com', 'Self-Employed', 'Singer', 'Velasquez Music', 'Married', 'Roman Catholic', 'Teresita Velasquez', 'Gerardo Velasquez', 'Ogie Alcasid', 'Member', '12-455676712-5', NULL, 'No', '2026-08-13'),
(188, 'Ogie', 'Alcasid', 'Herminio', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1987-08-27', '09907897654', 'ogie.alcasid@example.com', 'Self-Employed', 'Songwriter', 'A-Team', 'Married', 'Roman Catholic', 'Herminia Alcasid', 'Herminio Alcasid Sr.', 'Regine Velasquez', 'Dependent', '12-455676712-5', 'Regine Velasquez', 'No', '2026-08-13'),
(189, 'Sharon', 'Cuneta', 'Pangilinan', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1986-01-06', '09918908765', 'sharon.cuneta@example.com', 'Self-Employed', 'Actress', 'Mega Productions', 'Married', 'Roman Catholic', 'Elaine Gamboa', 'Pablo Cuneta', 'Kiko Pangilinan', 'Member', '12-566787823-6', NULL, 'No', '2026-08-13'),
(190, 'Kiko', 'Pangilinan', 'Castelo', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1983-08-24', '09929019876', 'kiko.pangilinan@example.com', 'Self-Employed', 'Organic Farmer', 'Kiko Farms', 'Married', 'Roman Catholic', 'Emma Castelo', 'Donato Pangilinan', 'Sharon Cuneta', 'Dependent', '12-566787823-6', 'Sharon Cuneta', 'No', '2026-08-13'),
(192, 'Chel', 'Diokno', 'Jose', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1981-02-23', '09941231098', 'chel.diokno@example.com', 'Self-Employed', 'Lawyer', 'FLAG Polangui', 'Married', 'Roman Catholicism', 'Carmen Jose', 'Jose W. Diokno', 'Divina Diokno', 'Member', '12-788909045-8', NULL, 'Yes', '2026-08-13'),
(193, 'Risa', 'Hontiveros', 'Baraquel', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1986-02-24', '09952342109', 'risa.hontiveros@example.com', 'Employed', 'Public Servant', 'LGU Health Desk', 'Widowed', 'Roman Catholic', 'Helen Baraquel', 'Ramon Hontiveros', 'Frank Baraquel', 'Member', '12-899010156-9', NULL, 'No', '2026-08-13'),
(194, 'Vico', 'Sotto', 'Nubla', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1999-06-17', '09963453210', 'vico.sotto@example.com', 'Employed', 'Government Official', 'LGU Polangui', 'Single', 'Roman Catholic', 'Coney Reyes', 'Vic Sotto', NULL, 'Member', '12-900121267-0', NULL, 'No', '2026-08-13'),
(195, 'Sandro', 'Marcos', 'Araneta', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1994-03-07', '09974564321', 'sandro.marcos@example.com', 'Employed', 'Legislative Staff', 'House Office', 'Single', 'Roman Catholic', 'Louise Araneta', 'Ferdinand Marcos Jr.', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(196, 'Mark', 'Villar', 'Aguilar', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1988-08-14', '09985675432', 'mark.villar@example.com', 'Employed', 'Civil Engineer', 'Villar Infra', 'Married', 'Roman Catholic', 'Cynthia Aguilar', 'Manny Villar', 'Emmeline Aglipay', 'Member', '12-011232378-1', NULL, 'No', '2026-08-13'),
(197, 'Cynthia', 'Villar', 'Aguilar', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1970-07-29', '09996786543', 'cynthia.villar@example.com', 'Self-Employed', 'Business Owner', 'Villar Foundation', 'Married', 'Roman Catholic', 'Lilia Aguilar', 'Vergel Aguilar', 'Manny Villar', 'Member', '12-122343489-3', NULL, 'No', '2026-08-13'),
(198, 'Manny', 'Villar', 'Bamba', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1969-12-13', '09051239876', 'manny.villar@example.com', 'Self-Employed', 'Real Estate Developer', 'Vista Land', 'Married', 'Roman Catholic', 'Curita Bamba', 'Manuel Villar Sr.', 'Cynthia Villar', 'Dependent', '12-122343489-3', 'Cynthia Villar', 'No', '2026-08-13'),
(199, 'Grace', 'Poe', 'Llamanzares', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1988-09-03', '09062348765', 'grace.poe@example.com', 'Employed', 'Policy Analyst', 'LGU Research', 'Married', 'Roman Catholic', 'Susan Roces', 'Fernando Poe Jr.', 'Teodoro Llamanzares', 'Member', '12-233454590-4', NULL, 'No', '2026-08-13'),
(201, 'Heart', 'Evangelista', 'Ongpauco', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1985-02-14', '09084566543', 'heart.e@example.com', 'Self-Employed', 'Artist/Designer', 'Heart Studio', 'Married', 'Roman Catholic', 'Cecile Ongpauco', 'Reynaldo Ongpauco', 'Chiz Escudero', 'Dependent', '12-344565601-5', 'Chiz Escudero', 'No', '2026-08-13'),
(202, 'Kryz', 'Uy', 'Young', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1990-01-28', '09095675432', 'kryz.uy@example.com', 'Self-Employed', 'Content Creator', 'Kryz Media', 'Married', 'Roman Catholic', 'Phoebe Uy', 'Francis Uy', 'Slater Young', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(203, 'Slater', 'Young', 'Tan', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1987-12-19', '09106784321', 'slater.young@example.com', 'Self-Employed', 'Civil Engineer', 'Liteblock Corp', 'Married', 'Roman Catholic', 'Lani Tan', 'John Young', 'Kryz Uy', 'Member', '12-455676712-6', NULL, 'No', '2026-08-13'),
(204, 'Nico', 'Bolzico', 'Petrak', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1983-10-03', '09117893210', 'nico.bolzico@example.com', 'Self-Employed', 'Agribusiness Owner', 'LM10 Agri', 'Married', 'Roman Catholic', 'Susana Petrak', 'Ruben Bolzico', 'Solenn Heussaff', 'Member', '12-566787823-7', NULL, 'No', '2026-08-13'),
(205, 'Solenn', 'Heussaff', 'Bolzico', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1985-07-20', '09128902109', 'solenn.h@example.com', 'Self-Employed', 'Artist/Model', 'Solenn Art', 'Married', 'Roman Catholic', 'Cynthia Adea', 'Louis Heussaff', 'Nico Bolzico', 'Dependent', '12-566787823-7', 'Nico Bolzico', 'No', '2026-08-13'),
(206, 'Erwan', 'Heussaff', 'Adea', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1987-01-10', '09139011098', 'erwan.h@example.com', 'Self-Employed', 'Chef/Vlogger', 'FEATR Media', 'Married', 'Roman Catholic', 'Cynthia Adea', 'Louis Heussaff', 'Anne Curtis', 'Member', '12-677898934-8', NULL, 'No', '2026-08-13'),
(208, 'Juswa', 'Reyes', 'Perez', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1995-06-11', '09151231098', 'juswa.reyes@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Elena Perez', 'Mario Reyes', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(209, 'Marites', 'Cruz', 'Mendoza', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1988-09-19', '09162342109', 'marites.cruz@example.com', 'Self-Employed', 'Barangay Store Owner', 'Marites Store', 'Married', 'Roman Catholic', 'Juana Mendoza', 'Pedro Cruz', 'Berto Cruz', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(211, 'Dodong', 'Alcantara', 'Serrano', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '2002-11-08', '09184564321', 'dodong.a@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Rosa Serrano', 'Felix Alcantara', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(212, 'Inday', 'Serrano', 'Alcantara', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '2004-04-14', '09195675432', 'inday.s@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Rosa Serrano', 'Felix Alcantara', NULL, 'Dependent', '12-788909045-9', 'Rosa Serrano', 'Yes', '2026-08-13'),
(217, 'Lucky', 'Casimero', 'Gado', NULL, 'Amatong Odiongan Romblon', 37, 'male', '1989-06-12', '09352673823', NULL, 'unemployed', NULL, NULL, 'single', 'Others', 'Susan Fadriquela Gado', 'Tony Fetalver Fontamillas', NULL, 'not applicable', NULL, NULL, 'no', '2026-08-13'),
(228, 'Daryl', 'Lota', '', NULL, 'Dapawan Odiongan Romblon', 21, 'Male', '2005-05-15', '09123456789', 'qwerty@gmail.com', 'Unemployed', NULL, NULL, 'Married', 'Roman Catholicism', 'iuqwyeuiyqwe oiuwqeoiuqwe', 'iqwueoiuwqeoiqwe qwpeoiopqewipoqwe', 'oqeuweuwqi opiqwieopiew', 'Not Applicable', NULL, NULL, 'Yes', '2026-09-22'),
(233, 'Paul Anthony', 'Fontamillas', 'Gado', NULL, 'Dapawan Odiongan Romblon', 25, 'Male', '2001-09-18', '09352673876', NULL, 'Unemployed', NULL, NULL, 'Single', 'Roman Catholicism', 'Gado Susan Fadriquela', 'Fontamillas Tony Fetalver', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-09-23'),
(234, 'Anthony', 'Fontamillas', 'Gado', NULL, 'Dapawan Odiongan Romblon', 36, 'Male', '1990-09-18', '09352673876', NULL, 'Unemployed', NULL, NULL, 'Single', 'Roman Catholicism', 'Gado Susan Fadriquela', 'Fontamillas Tony Fetalver', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-09-23');

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

--
-- Dumping data for table `queue_information`
--

INSERT INTO `queue_information` (`queue_info_id`, `queue_code`, `patient_id`, `service_name`, `remarks`, `server_name`, `status`, `accepted_at`, `service_duration`, `created_at`) VALUES
(172, 'A0001', 233, 'Medical Consultation', NULL, NULL, 'Completed', '2026-09-25 02:01:46', '2026-09-25 02:02:10', '2026-09-25 10:01:42'),
(173, 'A0002', 184, 'Medical Consultation', NULL, NULL, 'Completed', '2026-09-25 02:02:18', '2026-09-25 02:02:28', '2026-09-25 10:02:16'),
(178, 'A0003', 233, 'Medical Consultation', NULL, NULL, 'Completed', '2026-09-25 03:53:23', '2026-09-25 03:53:48', '2026-09-25 11:49:51'),
(181, 'A0005', 173, 'Medical Consultation', NULL, NULL, 'Completed', '2026-09-25 05:06:49', '2026-09-25 06:05:57', '2026-09-25 12:43:02'),
(183, 'A0006', 184, 'Medical Consultation', NULL, NULL, 'Completed', '2026-09-25 08:04:46', '2026-09-25 08:04:54', '2026-09-25 16:03:53');

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
(96, 233, 'A0001', 'Medical Consultation', 'qweqwe', 4, '0 days, 0 hours, 0 minutes', '2026-09-25 10:02:11'),
(97, 184, 'A0002', 'Medical Consultation', 'qweqweqew', 4, '0 days, 0 hours, 0 minutes', '2026-09-25 10:02:28'),
(98, 233, 'A0003', 'Medical Consultation', 'qweqwe', 4, '0 days, 0 hours, 0 minutes', '2026-09-25 11:53:48'),
(99, 173, 'A0005', 'Medical Consultation', 'None', 4, '0 days, 0 hours, 59 minutes', '2026-09-25 14:05:58'),
(100, 184, 'A0006', 'Medical Consultation', 'qweqwe', 10, '0 days, 0 hours, 0 minutes', '2026-09-25 16:04:54');

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
  MODIFY `appointments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `patient_auth`
--
ALTER TABLE `patient_auth`
  MODIFY `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT for table `queue_display`
--
ALTER TABLE `queue_display`
  MODIFY `queue_display_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `queue_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`);

--
-- Constraints for table `patient_auth`
--
ALTER TABLE `patient_auth`
  ADD CONSTRAINT `patient_auth_ibfk_1` FOREIGN KEY (`email`) REFERENCES `patient_records` (`patient_id`);

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
