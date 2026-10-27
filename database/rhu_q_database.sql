-- RHU-Q Database Backup
-- Generated: 2026-10-27 16:42:16

-- Table structure for `admin_auth` --
DROP TABLE IF EXISTS `admin_auth`;
CREATE TABLE `admin_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `role` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `fullname` (`fullname`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `appointments` --
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointments_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `contact_number` varchar(11) DEFAULT NULL,
  `service` varchar(250) NOT NULL,
  `remarks` text DEFAULT NULL,
  `server_name` int(11) DEFAULT NULL,
  `status` varchar(250) NOT NULL,
  `note` text DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`appointments_id`),
  KEY `appointments_ibfk_2` (`server_name`),
  KEY `appointments_ibfk_3` (`patient_id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `logs` --
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_message` varchar(200) NOT NULL,
  `log_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=667 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `patient_auth` --
DROP TABLE IF EXISTS `patient_auth`;
CREATE TABLE `patient_auth` (
  `patient_auth_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `last_active` datetime NOT NULL,
  PRIMARY KEY (`patient_auth_id`),
  UNIQUE KEY `patient_id` (`patient_id`),
  UNIQUE KEY `account_token` (`account_token`),
  UNIQUE KEY `username` (`username`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`,`middlename`,`suffix`),
  KEY `patient_auth_ibfk_5` (`middlename`),
  KEY `patient_auth_ibfk_6` (`suffix`),
  KEY `email` (`email`),
  CONSTRAINT `patient_auth_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `patient_auth_ibfk_3` FOREIGN KEY (`firstname`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  CONSTRAINT `patient_auth_ibfk_4` FOREIGN KEY (`lastname`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  CONSTRAINT `patient_auth_ibfk_5` FOREIGN KEY (`middlename`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE,
  CONSTRAINT `patient_auth_ibfk_6` FOREIGN KEY (`suffix`) REFERENCES `patient_records` (`patient_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `patient_records`;
CREATE TABLE `patient_records` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `account_token` int(11) DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `email` (`email`),
  KEY `firstname` (`firstname`,`lastname`,`middlename`,`suffix`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `transactions` --
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `queue_code` varchar(100) NOT NULL,
  `service` varchar(100) NOT NULL,
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
  `transaction_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `transactions_ibfk_3` (`service`),
  KEY `transactions_ibfk_1` (`server_name`),
  KEY `patient_id` (`patient_id`),
  KEY `queue_code_3` (`queue_code`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`server_name`) REFERENCES `admin_auth` (`id`),
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patient_records` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
