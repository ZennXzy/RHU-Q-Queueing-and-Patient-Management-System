-- ========================================================
-- RHU-Q: Queuing and Patient Management System Database Schema
-- Target Engine: MySQL / MariaDB
-- ========================================================

CREATE DATABASE IF NOT EXISTS `rhu_q_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `rhu_q_db`;

-- --------------------------------------------------------
-- 1. Admin Authentication Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_auth`;
CREATE TABLE `admin_auth` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_active` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 2. Services Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 3. Patient Records Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patient_records`;
CREATE TABLE `patient_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firstname` VARCHAR(50) NOT NULL,
  `lastname` VARCHAR(50) NOT NULL,
  `middlename` VARCHAR(50) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL UNIQUE,
  
  -- Employment Conditional Fields
  `employment_status` ENUM('Employed', 'Self-Employed', 'Unemployed') NOT NULL,
  `occupation` VARCHAR(100) DEFAULT NULL,
  `business_name` VARCHAR(100) DEFAULT NULL,
  
  -- Civil & Personal Details
  `civil_status` ENUM('Single', 'Married', 'Widowed', 'Separated') NOT NULL,
  `religion` VARCHAR(50) DEFAULT NULL,
  `mothers_name` VARCHAR(100) DEFAULT NULL,
  `fathers_name` VARCHAR(100) DEFAULT NULL,
  `spouses_name` VARCHAR(100) DEFAULT NULL,
  
  -- PhilHealth Conditional Fields
  `phil_status` ENUM('Member', 'Dependent', 'Not Applicable') DEFAULT 'Not Applicable',
  `phil_number` VARCHAR(30) DEFAULT NULL,
  `principal_member` VARCHAR(100) DEFAULT NULL,
  
  -- NHTS Conditional Fields
  `nhts_member` ENUM('Yes', 'No') DEFAULT 'No',
  `nhts_number` VARCHAR(30) DEFAULT NULL,
  
  `date_registered` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_patient_name` (`lastname`, `firstname`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 4. Queue Information Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `queue_information`;
CREATE TABLE `queue_information` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `queue_code` VARCHAR(20) NOT NULL,
  `patient_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `status` ENUM('Waiting', 'In-Progress', 'Completed', 'Cancelled') DEFAULT 'Waiting',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patient_records`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 5. Dynamic Queue Dashboard View
-- (Replaces static queue-dashboard table)
-- --------------------------------------------------------
CREATE OR REPLACE VIEW `vw_queue_dashboard` AS
SELECT 
    s.id AS service_id,
    s.service_name,
    COUNT(CASE WHEN q.status = 'Waiting' THEN 1 END) AS waiting_count,
    COUNT(CASE WHEN q.status = 'In-Progress' THEN 1 END) AS in_progress_count,
    COUNT(CASE WHEN q.status = 'Completed' THEN 1 END) AS completed_count,
    COUNT(CASE WHEN q.status = 'Cancelled' THEN 1 END) AS cancelled_count
FROM `services` s
LEFT JOIN `queue_information` q ON s.id = q.service_id AND DATE(q.created_at) = CURRENT_DATE()
GROUP BY s.id, s.service_name;

-- --------------------------------------------------------
-- 6. Transactions Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `queue_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `transaction_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patient_records`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`queue_id`) REFERENCES `queue_information`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 7. Pending Appointments Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pending_appointments`;
CREATE TABLE `pending_appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `description` TEXT NOT NULL,
  `requested_date` DATE NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patient_records`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 8. Approved Appointments Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `approved_appointments`;
CREATE TABLE `approved_appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pending_appointment_id` INT NOT NULL UNIQUE,
  `admin_id` INT NOT NULL,
  `scheduled_date` DATETIME NOT NULL,
  `approved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pending_appointment_id`) REFERENCES `pending_appointments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admin_auth`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 9. Appointment Messages Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `appointment_messages`;
CREATE TABLE `appointment_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pending_appointment_id` INT NOT NULL,
  `sender_type` ENUM('Admin', 'Patient') NOT NULL,
  `admin_id` INT DEFAULT NULL,
  `message` TEXT NOT NULL,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pending_appointment_id`) REFERENCES `pending_appointments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admin_auth`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
