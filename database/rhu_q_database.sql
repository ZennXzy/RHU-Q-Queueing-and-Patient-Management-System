-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2026 at 08:13 PM
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
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_auth`
--

INSERT INTO `admin_auth` (`id`, `username`, `password_hash`, `fullname`, `email`, `status`, `created_at`, `last_active`) VALUES
(4, 'paul', '$2y$12$cyX4s6IqAzDcbVhL7VTO5OEhjfvWZBaFSp14jvWvMHxg73jmpcetC', NULL, NULL, 'Active', '2026-08-09 00:14:45', '2026-08-09 00:14:45');

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
(118, 'Andres', 'Bonifacio', 'Castro', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1990-11-30', '09203334444', 'andres.b@example.com', 'Employed', 'Store Clerk', 'Fressmart', 'Married', 'Roman Catholic', 'Catalina de Castro', 'Santiago Bonifacio', 'Gregoria de Jesus', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(119, 'Melchora', 'Aquino', 'Ramos', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1952-01-06', '09215556666', 'melchora.a@example.com', 'Unemployed', NULL, NULL, 'Widowed', 'Roman Catholic', 'Valentina de Aquino', 'Juan Ramos', 'Fulgence Ramos', 'Member', '12-112233445-5', NULL, 'Yes', '2026-08-13'),
(120, 'Apolinario', 'Mabini', 'Maranan', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1978-07-23', '09227778888', 'apolinario.m@example.com', 'Self-Employed', 'Legal Consultant', 'Mabini Law', 'Single', 'Aglipayan', 'Inocencia Maranan', 'Inigo Mabini', NULL, 'Member', '12-998877665-4', NULL, 'No', '2026-08-13'),
(121, 'Gabriela', 'Silang', 'Caniño', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1988-03-19', '09239990000', 'gabriela.s@example.com', 'Employed', 'Nurse', 'RHU Clinic', 'Widowed', 'Roman Catholic', 'Monika Caniño', 'Anselmo Caniño', 'Diego Silang', 'Member', '12-334455667-8', NULL, 'No', '2026-08-13'),
(122, 'Emilio', 'Aguinaldo', 'Famy', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1969-03-22', '09241239876', 'emilio.a@example.com', 'Employed', 'Barangay Official', 'LGU Polangui', 'Married', 'Roman Catholic', 'Trinidad Famy', 'Carlos Aguinaldo', 'Hilaria del Rosario', 'Member', '12-445566778-9', NULL, 'No', '2026-08-13'),
(123, 'Marcelo', 'Del Pilar', 'Hilario', 'Sr.', 'Zone 1, Polangui, Albay', 0, 'Male', '1975-08-30', '09252348765', 'marcelo.dp@example.com', 'Self-Employed', 'Writer', 'Bicol Press', 'Married', 'Roman Catholic', 'Blasa Hilario', 'Julian del Pilar', 'Marciana Valenzuela', 'Member', '12-556677889-0', NULL, 'No', '2026-08-13'),
(124, 'Juan Luna', 'Novicio', 'San Pedro', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1982-10-23', '09263457654', 'juan.luna@example.com', 'Self-Employed', 'Painter', 'Luna Arts Studio', 'Married', 'Roman Catholic', 'Laureana Novicio', 'Joaquin Luna', 'Paz Pardo de Tavera', 'Member', '12-667788990-1', NULL, 'No', '2026-08-13'),
(125, 'Antonio', 'Luna', 'Novicio', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1984-10-29', '09274566543', 'antonio.luna@example.com', 'Employed', 'Pharmacist', 'Polangui Pharmacy', 'Single', 'Roman Catholic', 'Laureana Novicio', 'Joaquin Luna', NULL, 'Member', '12-778899001-2', NULL, 'No', '2026-08-13'),
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
(137, 'Artemio', 'Ricarte', 'Garcia', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1966-10-20', '09396786543', 'artemio.r@example.com', 'Unemployed', NULL, NULL, 'Married', 'Roman Catholic', 'Francia Garcia', 'Esteban Ricarte', 'Agueda Esteban', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(138, 'Vicente', 'Lukban', 'Rilles', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1971-02-11', '09407897654', 'vicente.l@example.com', 'Employed', 'Civil Engineer', 'DPWH', 'Married', 'Roman Catholic', 'Luz Rilles', 'Agustin Lukban', 'Sofia Surtida', 'Member', '12-607988934-1', NULL, 'No', '2026-08-13'),
(139, 'Marina', 'Dizon', 'Santiago', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1986-07-18', '09418908765', 'marina.dizon@example.com', 'Employed', 'Social Worker', 'DSWD', 'Married', 'Roman Catholic', 'Roberta Santiago', 'Jose Dizon', 'Jose Turiano', 'Member', '12-708999045-2', NULL, 'No', '2026-08-13'),
(140, 'Agueda', 'Kahabagan', 'Inocencio', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1980-05-26', '09429019876', 'agueda.k@example.com', 'Self-Employed', 'Vendor', 'Polangui Public Market', 'Single', 'Roman Catholic', 'Victoria Inocencio', 'Quintin Kahabagan', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(141, 'Trinidad', 'Tecson', 'Perez', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1978-11-18', '09430120987', 'trinidad.t@example.com', 'Employed', 'Red Cross Staff', 'Philippine Red Cross', 'Separated', 'Roman Catholic', 'Monica Perez', 'Rafael Tecson', NULL, 'Member', '12-809010156-3', NULL, 'No', '2026-08-13'),
(142, 'Gliceria', 'Marella', 'Villavicencio', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1962-05-13', '09441231098', 'gliceria.v@example.com', 'Self-Employed', 'Real Estate Agent', 'Self', 'Widowed', 'Roman Catholic', 'Mariana Villavicencio', 'Vicente Marella', 'Eulalio Villavicencio', 'Member', '12-910121267-4', NULL, 'No', '2026-08-13'),
(143, 'Clemencia', 'Lopez', 'Nolasco', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1987-01-16', '09452342109', 'clemencia.l@example.com', 'Employed', 'Bank Teller', 'BDO Polangui', 'Single', 'Roman Catholic', 'Gaudencia Nolasco', 'Natalio Lopez', NULL, 'Member', '12-011232378-5', NULL, 'No', '2026-08-13'),
(144, 'Esperanza', 'Limjap', 'Escolar', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1992-09-08', '09463453210', 'esperanza.l@example.com', 'Employed', 'HR Officer', 'Polangui Mall', 'Married', 'Roman Catholic', 'Francisca Escolar', 'Mariano Limjap', 'Sergio Osmeña', 'Dependent', '12-122343489-6', 'Sergio Osmeña', 'No', '2026-08-13'),
(145, 'Carlos', 'P. Garcia', 'Polestico', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1968-11-04', '09474564321', 'carlos.garcia@example.com', 'Employed', 'Municipal Staff', 'LGU Polangui', 'Married', 'Roman Catholic', 'Protasia Polestico', 'Policarpio Garcia', 'Leonila Dimataga', 'Member', '12-233454590-7', NULL, 'No', '2026-08-13'),
(146, 'Diosdado', 'Macapagal', 'Pangan', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1970-09-28', '09485675432', 'diosdado.m@example.com', 'Employed', 'Economist', 'LGU Planning', 'Married', 'Roman Catholic', 'Romana Pangan', 'Urbano Macapagal', 'Evangelina Macaraeg', 'Member', '12-344565601-8', NULL, 'No', '2026-08-13'),
(147, 'Ramon', 'Magsaysay', 'Del Fierro', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1967-08-31', '09496786543', 'ramon.m@example.com', 'Self-Employed', 'Mechanic', 'Magsaysay Auto Repair', 'Married', 'Roman Catholic', 'Perfecta Del Fierro', 'Ezequiel Magsaysay', 'Luz Banzon', 'Member', '12-455676712-9', NULL, 'No', '2026-08-13'),
(148, 'Manuel', 'Roxas', 'Acuña', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1965-01-01', '09507897654', 'manuel.roxas@example.com', 'Employed', 'Auditor', 'COA Bicol', 'Married', 'Roman Catholic', 'Rosario Acuña', 'Gerardo Roxas', 'Trinidad de Leon', 'Member', '12-566787823-0', NULL, 'No', '2026-08-13'),
(149, 'Manuel', 'Quezon', 'Molina', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1978-08-19', '09518908765', 'manuel.quezon@example.com', 'Employed', 'Government Staff', 'Provincial Capitol', 'Married', 'Roman Catholic', 'Maria Molina', 'Lucio Quezon', 'Aurora Aragon', 'Member', '12-677898934-1', NULL, 'No', '2026-08-13'),
(150, 'Sergio', 'Osmeña', 'Suico', 'Sr.', 'Zone 1, Polangui, Albay', 0, 'Male', '1978-09-09', '09529019876', 'sergio.osmena@example.com', 'Employed', 'Administrator', 'Polangui College', 'Married', 'Roman Catholic', 'Juana Suico', 'Pedro Osmeña', 'Esperanza Limjap', 'Member', '12-122343489-6', NULL, 'No', '2026-08-13'),
(151, 'Jose', 'Laurel', 'Paciano', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1981-03-09', '09530120987', 'jose.laurel@example.com', 'Self-Employed', 'Judge/Lawyer', 'Laurel Office', 'Married', 'Roman Catholic', 'Jacoba Paciano', 'Sotero Laurel', 'Paciencia Hidalgo', 'Member', '12-788909045-2', NULL, 'No', '2026-08-13'),
(152, 'Elpidio', 'Quirino', 'Rivera', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1970-11-16', '09541231098', 'elpidio.q@example.com', 'Employed', 'Tax Consultant', 'BIR Polangui', 'Widowed', 'Roman Catholic', 'Gregoria Rivera', 'Mariano Quirino', 'Alicia Syquia', 'Member', '12-899010156-3', NULL, 'No', '2026-08-13'),
(153, 'Corazon', 'Aquino', 'Cojuangco', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1973-01-25', '09552342109', 'cory.aquino@example.com', 'Unemployed', NULL, NULL, 'Widowed', 'Roman Catholic', 'Demetria Sumulong', 'Jose Cojuangco', 'Benigno Aquino Jr.', 'Member', '12-900121267-4', NULL, 'No', '2026-08-13'),
(154, 'Fidel', 'Ramos', 'Valdez', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1968-03-18', '09563453210', 'fidel.ramos@example.com', 'Employed', 'Retired Military', 'AFP', 'Married', 'Protestant', 'Angela Valdez', 'Narciso Ramos', 'Amelita Martinez', 'Member', '12-011232378-5', NULL, 'No', '2026-08-13'),
(155, 'Gloria', 'Arroyo', 'Macapagal', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1987-04-05', '09574564321', 'gloria.arroyo@example.com', 'Employed', 'Professor', 'BU Polangui', 'Married', 'Roman Catholic', 'Evangelina Macaraeg', 'Diosdado Macapagal', 'Jose Miguel Arroyo', 'Member', '12-122343489-0', NULL, 'No', '2026-08-13'),
(156, 'Benigno', 'Aquino', 'Cojuangco', 'III', 'Zone 2, Polangui, Albay', 0, 'Male', '1980-02-08', '09585675432', 'noynoy.aquino@example.com', 'Employed', 'Executive', 'Private Firm', 'Single', 'Roman Catholic', 'Corazon Cojuangco', 'Benigno Aquino Jr.', NULL, 'Member', '12-233454590-1', NULL, 'No', '2026-08-13'),
(157, 'Rodrigo', 'Duterte', 'Roa', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1965-03-28', '09596786543', 'rodrigo.duterte@example.com', 'Self-Employed', 'Consultant', 'Roa Services', 'Separated', 'Roman Catholic', 'Soledad Roa', 'Vicente Duterte', NULL, 'Member', '12-344565601-2', NULL, 'No', '2026-08-13'),
(158, 'Ferdinand', 'Marcos', 'Edralin', 'Jr.', 'Zone 4, Polangui, Albay', 0, 'Male', '1977-09-13', '09607897654', 'bongbong.marcos@example.com', 'Employed', 'Civil Servant', 'Provincial Office', 'Married', 'Roman Catholic', 'Imelda Romualdez', 'Ferdinand Marcos Sr.', 'Louise Araneta', 'Member', '12-455676712-3', NULL, 'No', '2026-08-13'),
(159, 'Leni', 'Robredo', 'Gerona', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1985-04-23', '09618908765', 'leni.robredo@example.com', 'Self-Employed', 'Human Rights Lawyer', 'Free Legal Assistance', 'Widowed', 'Roman Catholic', 'Salvacion Santo Tomas', 'Antonio Gerona', 'Jesse Robredo', 'Member', '12-566787823-4', NULL, 'No', '2026-08-13'),
(160, 'Manny', 'Pacquiao', 'Dapidran', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1988-12-17', '09629019876', 'manny.pacquiao@example.com', 'Self-Employed', 'Athlete/Coach', 'Pacquiao Gym', 'Married', 'Evangelical', 'Dionisia Dapidran', 'Rosalio Pacquiao', 'Jinkee Jamora', 'Member', '12-677898934-5', NULL, 'No', '2026-08-13'),
(161, 'Pia', 'Wurtzbach', 'Alonzo', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1999-09-24', '09630120987', 'pia.wurtzbach@example.com', 'Self-Employed', 'Model/Host', 'Self', 'Married', 'Roman Catholic', 'Cheryl Alonzo', 'Uwe Wurtzbach', 'Jeremy Jauncey', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(162, 'Catriona', 'Gray', 'Magnayon', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '2001-01-06', '09641231098', 'catriona.gray@example.com', 'Employed', 'Brand Ambassador', 'Gray Media', 'Single', 'Protestant', 'Normita Magnayon', 'Ian Gray', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(163, 'Lea', 'Salonga', 'Imutan', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1991-02-22', '09652342109', 'lea.salonga@example.com', 'Self-Employed', 'Performer', 'Salonga Productions', 'Married', 'Roman Catholic', 'Ligaya Imutan', 'Feliciano Salonga', 'Robert Chien', 'Member', '12-788909045-6', NULL, 'No', '2026-08-13'),
(164, 'Efren', 'Reyes', 'Manalang', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1974-08-26', '09663453210', 'efren.reyes@example.com', 'Self-Employed', 'Sports Instructor', 'Bata Sports Center', 'Married', 'Roman Catholic', 'Romana Manalang', 'Pedro Reyes', 'Susan Reyes', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(165, 'Carlos', 'Yulo', 'Edriel', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '2000-02-16', '09674564321', 'carlos.yulo@example.com', 'Employed', 'Gymnast Coach', 'Polangui Sports Complex', 'Single', 'Roman Catholic', 'Angelica Yulo', 'Mark Andrew Yulo', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
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
(179, 'Dolphy', 'Quizon', 'Rotor', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1968-07-25', '09818908765', 'dolphy.quizon@example.com', 'Self-Employed', 'Comedian', 'Comedy Realm', 'Single', 'Roman Catholic', 'Salud Rotor', 'Melencio Quizon', NULL, 'Member', '12-899010156-8', NULL, 'No', '2026-08-13'),
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
(191, 'Bam', 'Aquino', 'Aguirre', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1997-05-07', '09930120987', 'bam.aquino@example.com', 'Employed', 'Social Entrepreneur', 'MicroVentures', 'Married', 'Roman Catholic', 'Melanie Aguirre', 'Paul Aquino', 'Timi Gomez', 'Member', '12-677898934-7', NULL, 'No', '2026-08-13'),
(192, 'Chel', 'Diokno', 'Jose', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1981-02-23', '09941231098', 'chel.diokno@example.com', 'Self-Employed', 'Lawyer', 'FLAG Polangui', 'Married', 'Roman Catholic', 'Carmen Jose', 'Jose W. Diokno', 'Divina Diokno', 'Member', '12-788909045-8', NULL, 'No', '2026-08-13'),
(193, 'Risa', 'Hontiveros', 'Baraquel', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1986-02-24', '09952342109', 'risa.hontiveros@example.com', 'Employed', 'Public Servant', 'LGU Health Desk', 'Widowed', 'Roman Catholic', 'Helen Baraquel', 'Ramon Hontiveros', 'Frank Baraquel', 'Member', '12-899010156-9', NULL, 'No', '2026-08-13'),
(194, 'Vico', 'Sotto', 'Nubla', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1999-06-17', '09963453210', 'vico.sotto@example.com', 'Employed', 'Government Official', 'LGU Polangui', 'Single', 'Roman Catholic', 'Coney Reyes', 'Vic Sotto', NULL, 'Member', '12-900121267-0', NULL, 'No', '2026-08-13'),
(195, 'Sandro', 'Marcos', 'Araneta', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1994-03-07', '09974564321', 'sandro.marcos@example.com', 'Employed', 'Legislative Staff', 'House Office', 'Single', 'Roman Catholic', 'Louise Araneta', 'Ferdinand Marcos Jr.', NULL, 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(196, 'Mark', 'Villar', 'Aguilar', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1988-08-14', '09985675432', 'mark.villar@example.com', 'Employed', 'Civil Engineer', 'Villar Infra', 'Married', 'Roman Catholic', 'Cynthia Aguilar', 'Manny Villar', 'Emmeline Aglipay', 'Member', '12-011232378-1', NULL, 'No', '2026-08-13'),
(197, 'Cynthia', 'Villar', 'Aguilar', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1970-07-29', '09996786543', 'cynthia.villar@example.com', 'Self-Employed', 'Business Owner', 'Villar Foundation', 'Married', 'Roman Catholic', 'Lilia Aguilar', 'Vergel Aguilar', 'Manny Villar', 'Member', '12-122343489-3', NULL, 'No', '2026-08-13'),
(198, 'Manny', 'Villar', 'Bamba', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1969-12-13', '09051239876', 'manny.villar@example.com', 'Self-Employed', 'Real Estate Developer', 'Vista Land', 'Married', 'Roman Catholic', 'Curita Bamba', 'Manuel Villar Sr.', 'Cynthia Villar', 'Dependent', '12-122343489-3', 'Cynthia Villar', 'No', '2026-08-13'),
(199, 'Grace', 'Poe', 'Llamanzares', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1988-09-03', '09062348765', 'grace.poe@example.com', 'Employed', 'Policy Analyst', 'LGU Research', 'Married', 'Roman Catholic', 'Susan Roces', 'Fernando Poe Jr.', 'Teodoro Llamanzares', 'Member', '12-233454590-4', NULL, 'No', '2026-08-13'),
(200, 'Chiz', 'Escudero', 'Evangelista', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '1979-10-10', '09073457654', 'chiz.escudero@example.com', 'Employed', 'Public Administrator', 'Sorsogon LGU', 'Married', 'Roman Catholic', 'Evelina Escudero', 'Salvador Escudero', 'Heart Evangelista', 'Member', '12-344565601-5', NULL, 'No', '2026-08-13'),
(201, 'Heart', 'Evangelista', 'Ongpauco', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '1985-02-14', '09084566543', 'heart.e@example.com', 'Self-Employed', 'Artist/Designer', 'Heart Studio', 'Married', 'Roman Catholic', 'Cecile Ongpauco', 'Reynaldo Ongpauco', 'Chiz Escudero', 'Dependent', '12-344565601-5', 'Chiz Escudero', 'No', '2026-08-13'),
(202, 'Kryz', 'Uy', 'Young', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1990-01-28', '09095675432', 'kryz.uy@example.com', 'Self-Employed', 'Content Creator', 'Kryz Media', 'Married', 'Roman Catholic', 'Phoebe Uy', 'Francis Uy', 'Slater Young', 'Not Applicable', NULL, NULL, 'No', '2026-08-13'),
(203, 'Slater', 'Young', 'Tan', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1987-12-19', '09106784321', 'slater.young@example.com', 'Self-Employed', 'Civil Engineer', 'Liteblock Corp', 'Married', 'Roman Catholic', 'Lani Tan', 'John Young', 'Kryz Uy', 'Member', '12-455676712-6', NULL, 'No', '2026-08-13'),
(204, 'Nico', 'Bolzico', 'Petrak', NULL, 'Zone 2, Polangui, Albay', 0, 'Male', '1983-10-03', '09117893210', 'nico.bolzico@example.com', 'Self-Employed', 'Agribusiness Owner', 'LM10 Agri', 'Married', 'Roman Catholic', 'Susana Petrak', 'Ruben Bolzico', 'Solenn Heussaff', 'Member', '12-566787823-7', NULL, 'No', '2026-08-13'),
(205, 'Solenn', 'Heussaff', 'Bolzico', NULL, 'Zone 2, Polangui, Albay', 0, 'Female', '1985-07-20', '09128902109', 'solenn.h@example.com', 'Self-Employed', 'Artist/Model', 'Solenn Art', 'Married', 'Roman Catholic', 'Cynthia Adea', 'Louis Heussaff', 'Nico Bolzico', 'Dependent', '12-566787823-7', 'Nico Bolzico', 'No', '2026-08-13'),
(206, 'Erwan', 'Heussaff', 'Adea', NULL, 'Zone 3, Polangui, Albay', 0, 'Male', '1987-01-10', '09139011098', 'erwan.h@example.com', 'Self-Employed', 'Chef/Vlogger', 'FEATR Media', 'Married', 'Roman Catholic', 'Cynthia Adea', 'Louis Heussaff', 'Anne Curtis', 'Member', '12-677898934-8', NULL, 'No', '2026-08-13'),
(207, 'Anne', 'Curtis', 'Smith', NULL, 'Zone 3, Polangui, Albay', 0, 'Female', '1985-02-17', '09140120187', 'anne.curtis@example.com', 'Self-Employed', 'Actress/Host', 'Viva Entertainment', 'Married', 'Roman Catholic', 'Carmen Ojales', 'James Smith', 'Erwan Heussaff', 'Dependent', '12-677898934-8', 'Erwan Heussaff', 'No', '2026-08-13'),
(208, 'Juswa', 'Reyes', 'Perez', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1995-06-11', '09151231098', 'juswa.reyes@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Elena Perez', 'Mario Reyes', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(209, 'Marites', 'Cruz', 'Mendoza', NULL, 'Zone 4, Polangui, Albay', 0, 'Female', '1988-09-19', '09162342109', 'marites.cruz@example.com', 'Self-Employed', 'Barangay Store Owner', 'Marites Store', 'Married', 'Roman Catholic', 'Juana Mendoza', 'Pedro Cruz', 'Berto Cruz', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(210, 'Berto', 'Cruz', 'Bautista', NULL, 'Zone 4, Polangui, Albay', 0, 'Male', '1985-03-30', '09173453210', 'berto.cruz@example.com', 'Self-Employed', 'Tricycle Driver', 'Polangui TODA', 'Married', 'Roman Catholic', 'Maria Bautista', 'Lito Cruz', 'Marites Cruz', 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(211, 'Dodong', 'Alcantara', 'Serrano', NULL, 'Zone 5, Polangui, Albay', 0, 'Male', '2002-11-08', '09184564321', 'dodong.a@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Rosa Serrano', 'Felix Alcantara', NULL, 'Not Applicable', NULL, NULL, 'Yes', '2026-08-13'),
(212, 'Inday', 'Serrano', 'Alcantara', NULL, 'Zone 5, Polangui, Albay', 0, 'Female', '2004-04-14', '09195675432', 'inday.s@example.com', 'Unemployed', NULL, NULL, 'Single', 'Roman Catholic', 'Rosa Serrano', 'Felix Alcantara', NULL, 'Dependent', '12-788909045-9', 'Rosa Serrano', 'Yes', '2026-08-13'),
(213, 'Cardo', 'Dalisay', 'Probo', NULL, 'Zone 1, Polangui, Albay', 0, 'Male', '1989-10-10', '09206786543', 'cardo.dalisay@example.com', 'Employed', 'Police Officer', 'PNP Polangui', 'Married', 'Roman Catholic', 'Flora Borja', 'Pablo Dalisay', 'Alyana Arevalo', 'Member', '12-788909045-9', NULL, 'No', '2026-08-13'),
(214, 'Alyana', 'Arevalo', 'Dalisay', NULL, 'Zone 1, Polangui, Albay', 0, 'Female', '1992-01-15', '09217897654', 'alyana.dalisay@example.com', 'Employed', 'Journalist', 'Polangui News', 'Married', 'Roman Catholic', 'Virgie Arevalo', 'Teddy Arevalo', 'Cardo Dalisay', 'Dependent', '12-788909045-9', 'Cardo Dalisay', 'No', '2026-08-13'),
(217, 'Lucky', 'Casimero', 'Gado', NULL, 'Amatong Odiongan Romblon', 37, 'male', '1989-06-12', '09352673823', NULL, 'unemployed', NULL, NULL, 'single', 'Others', 'Susan Fadriquela Gado', 'Tony Fetalver Fontamillas', NULL, 'not applicable', NULL, NULL, 'no', '2026-08-13'),
(218, 'Daryl', 'Lota', 'Gado', NULL, 'Amatong Odiongan Romblon', 21, 'male', '2005-03-13', '09876543219', NULL, 'unemployed', NULL, NULL, 'single', 'Others', 'Darlyn Fadriquela Lota', 'Darwin Fetalver Lota', NULL, 'not applicable', NULL, NULL, 'no', '2026-08-21'),
(219, 'Paul Anthony', 'Fontamillas', 'Gado', NULL, 'Dapawan Odiongan Romblon', 24, 'Male', '2001-09-18', '09352673843', NULL, 'unemployed', NULL, NULL, 'Single', 'Others', 'Susan Fadriquela Gado', 'Tony Fetalver Fontamillas', NULL, 'not applicable', NULL, NULL, 'yes', '2026-08-21');

-- --------------------------------------------------------

--
-- Table structure for table `pending_appointments`
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
-- Table structure for table `queue_information`
--

CREATE TABLE `queue_information` (
  `id` int(11) NOT NULL,
  `queue_code` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('Waiting','In-Progress','Completed','Cancelled') DEFAULT 'Waiting',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`) VALUES
(2, 'Dental'),
(4, 'Laboratory'),
(1, 'Medical'),
(3, 'Vaccine');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_queue_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `vw_queue_dashboard` (
`service_id` int(11)
,`service_name` varchar(100)
,`waiting_count` bigint(21)
,`in_progress_count` bigint(21)
,`completed_count` bigint(21)
,`cancelled_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_queue_dashboard`
--
DROP TABLE IF EXISTS `vw_queue_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_queue_dashboard`  AS SELECT `s`.`id` AS `service_id`, `s`.`service_name` AS `service_name`, count(case when `q`.`status` = 'Waiting' then 1 end) AS `waiting_count`, count(case when `q`.`status` = 'In-Progress' then 1 end) AS `in_progress_count`, count(case when `q`.`status` = 'Completed' then 1 end) AS `completed_count`, count(case when `q`.`status` = 'Cancelled' then 1 end) AS `cancelled_count` FROM (`services` `s` left join `queue_information` `q` on(`s`.`id` = `q`.`service_id` and cast(`q`.`created_at` as date) = curdate())) GROUP BY `s`.`id`, `s`.`service_name` ;

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
-- Indexes for table `queue_information`
--
ALTER TABLE `queue_information`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `queue_id` (`queue_id`),
  ADD KEY `service_id` (`service_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_auth`
--
ALTER TABLE `admin_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `patient_records`
--
ALTER TABLE `patient_records`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `pending_appointments`
--
ALTER TABLE `pending_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_information`
--
ALTER TABLE `queue_information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `queue_information_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `queue_information_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`queue_id`) REFERENCES `queue_information` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
