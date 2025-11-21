-- Full schema with all tables, foreign keys, and sample data
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Table: specializations
CREATE TABLE `specializations` (
  `specialization_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`specialization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `specializations` (`name`, `description`) VALUES
('Dentistry', 'Teeth and oral health'),
('Cardiology', 'Heart and blood vessels'),
('General Medicine', 'General physician'),
('Dermatology', 'Skin specialist');

-- Table: users
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample users (password for all is "123456")
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`) VALUES
('Admin User', 'admin@clinic.com', '$2y$10$zJ5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5u', '9876543210', 'admin'),
('Dr. Sarah Smith', 'sarah@clinic.com', '$2y$10$zJ5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5u', '9876543211', 'doctor'),
('Dr. Mike Johnson', 'mike@clinic.com', '$2y$10$zJ5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5u', '9876543212', 'doctor'),
('John Doe', 'john@example.com', '$2y$10$zJ5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5z5j5u', '9876543213', 'patient');

-- Table: doctors
CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `consultation_fee` decimal(10,2) DEFAULT 500.00,
  `profile_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`doctor_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`specialization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `doctors` (`user_id`, `specialization_id`, `qualification`, `experience_years`, `consultation_fee`) VALUES
(2, 1, 'BDS, MDS', 8, 600.00),   -- Dr. Sarah (Dentistry)
(3, 2, 'MBBS, MD Cardiology', 12, 1000.00); -- Dr. Mike (Cardiology)

-- Table: doctor_schedule
CREATE TABLE `doctor_schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL, -- 0=Sunday, 1=Monday ... 6=Saturday
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int(11) DEFAULT 30, -- in minutes
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`schedule_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dr. Sarah: Mon-Fri 9AM-5PM, Saturday 10AM-2PM
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`) VALUES
(1, 1, '09:00:00', '17:00:00', 30),
(1, 2, '09:00:00', '17:00:00', 30),
(1, 3, '09:00:00', '17:00:00', 30),
(1, 4, '09:00:00', '17:00:00', 30),
(1, 5, '09:00:00', '17:00:00', 30),
(1, 6, '10:00:00', '14:00:00', 30);

-- Dr. Mike: Mon, Wed, Fri 10AM-4PM
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(2, 1, '10:00:00', '16:00:00', 30),
(2, 3, '10:00:00', '16:00:00', 30),
(2, 5, '10:00:00', '16:00:00', 30);

-- Table: doctor_leaves
CREATE TABLE `doctor_leaves` (
  `leave_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`leave_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: appointments
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason_for_visit` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled','no-show') DEFAULT 'pending',
  `booked_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `cancelled_by` enum('patient','doctor','admin') DEFAULT NULL,
  PRIMARY KEY (`appointment_id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: notifications
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;