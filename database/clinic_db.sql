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
('Dermatology', 'Skin specialist'),
('Pediatrics', 'Child healthcare'),
('Orthopedics', 'Bone and joint specialist'),
('Neurology', 'Brain and nervous system'),
('Ophthalmology', 'Eye specialist'),
('Gynecology', 'Women health specialist'),
('Psychiatry', 'Mental health specialist'),
('ENT', 'Ear, Nose and Throat specialist');

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

-- Sample users (password for all is "123456" in plain text)
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`) VALUES
('Admin User', 'admin@clinic.com', '123456', '9876543210', 'admin'),
('Sarah Smith', 'sarah@clinic.com', '123456', '9876543211', 'doctor'),
('Mike Johnson', 'mike@clinic.com', '123456', '9876543212', 'doctor'),
('John Doe', 'john@example.com', '123456', '9876543213', 'patient'),
('Emily Davis', 'emily@example.com', '123456', '9876543214', 'patient'),
('Robert Wilson', 'robert@example.com', '123456', '9876543215', 'patient'),
('Lisa Brown', 'lisa@example.com', '123456', '9876543216', 'patient'),
('David Miller', 'david@example.com', '123456', '9876543217', 'patient'),
('Jennifer Taylor', 'jennifer@example.com', '123456', '9876543218', 'patient'),
('Michael Anderson', 'michael@example.com', '123456', '9876543219', 'patient'),
('Jessica Thomas', 'jessica@example.com', '123456', '9876543220', 'patient'),
('William Martinez', 'william@example.com', '123456', '9876543221', 'patient'),
('Amanda Clark', 'amanda@example.com', '123456', '9876543222', 'patient'),
('Christopher Rodriguez', 'chris@example.com', '123456', '9876543223', 'patient'),
('Karen Lewis', 'karen@example.com', '123456', '9876543224', 'patient'),
('James Walker', 'james@example.com', '123456', '9876543225', 'doctor'),
('Patricia Hall', 'patricia@clinic.com', '123456', '9876543226', 'doctor'),
('Daniel Allen', 'daniel@clinic.com', '123456', '9876543227', 'doctor'),
('Susan Young', 'susan@clinic.com', '123456', '9876543228', 'doctor'),
('Matthew King', 'matthew@clinic.com', '123456', '9876543229', 'doctor'),
('Nancy Wright', 'nancy@clinic.com', '123456', '9876543230', 'doctor'),
('Kevin Scott', 'kevin@clinic.com', '123456', '9876543231', 'doctor'),
('Betty Green', 'betty@clinic.com', '123456', '9876543232', 'doctor'),
('Steven Adams', 'steven@clinic.com', '123456', '9876543233', 'doctor');

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
(2, 1, 'BDS, MDS', 8, 600.00),
(3, 2, 'MBBS, MD Cardiology', 12, 1000.00),
(16, 3, 'MBBS, MD Medicine', 10, 700.00),
(17, 4, 'MBBS, MD Dermatology', 6, 800.00),
(18, 5, 'MBBS, MD Pediatrics', 15, 900.00),
(19, 6, 'MBBS, MS Orthopedics', 11, 1200.00),
(20, 7, 'MBBS, DM Neurology', 14, 1500.00),
(21, 8, 'MBBS, MS Ophthalmology', 9, 850.00),
(22, 9, 'MBBS, MD Gynecology', 13, 950.00),
(23, 10, 'MBBS, MD Psychiatry', 7, 1100.00),
(24, 11, 'MBBS, MS ENT', 8, 750.00);

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

-- Doctor 1: Sarah Smith (Dentistry)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`) VALUES
(1, 1, '09:00:00', '17:00:00', 30),
(1, 2, '09:00:00', '17:00:00', 30),
(1, 3, '09:00:00', '17:00:00', 30),
(1, 4, '09:00:00', '17:00:00', 30),
(1, 5, '09:00:00', '17:00:00', 30),
(1, 6, '10:00:00', '14:00:00', 30);

-- Doctor 2: Mike Johnson (Cardiology)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(2, 1, '10:00:00', '16:00:00', 30),
(2, 3, '10:00:00', '16:00:00', 30),
(2, 5, '10:00:00', '16:00:00', 30);

-- Doctor 3: James Walker (General Medicine)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(3, 1, '08:00:00', '16:00:00', 30),
(3, 2, '08:00:00', '16:00:00', 30),
(3, 3, '08:00:00', '16:00:00', 30),
(3, 4, '08:00:00', '16:00:00', 30),
(3, 5, '08:00:00', '16:00:00', 30);

-- Doctor 4: Patricia Hall (Dermatology)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(4, 2, '09:00:00', '15:00:00', 30),
(4, 4, '09:00:00', '15:00:00', 30),
(4, 6, '09:00:00', '13:00:00', 30);

-- Doctor 5: Daniel Allen (Pediatrics)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(5, 1, '10:00:00', '17:00:00', 30),
(5, 3, '10:00:00', '17:00:00', 30),
(5, 5, '10:00:00', '17:00:00', 30);

-- Doctor 6: Susan Young (Orthopedics)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(6, 2, '08:00:00', '14:00:00', 30),
(6, 4, '08:00:00', '14:00:00', 30),
(6, 6, '08:00:00', '12:00:00', 30);

-- Doctor 7: Matthew King (Neurology)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(7, 1, '11:00:00', '17:00:00', 30),
(7, 3, '11:00:00', '17:00:00', 30),
(7, 5, '11:00:00', '17:00:00', 30);

-- Doctor 8: Nancy Wright (Ophthalmology)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(8, 2, '09:00:00', '16:00:00', 30),
(8, 4, '09:00:00', '16:00:00', 30),
(8, 6, '09:00:00', '13:00:00', 30);

-- Doctor 9: Kevin Scott (Gynecology)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(9, 1, '08:30:00', '15:30:00', 30),
(9, 3, '08:30:00', '15:30:00', 30),
(9, 5, '08:30:00', '15:30:00', 30);

-- Doctor 10: Betty Green (Psychiatry)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(10, 2, '10:00:00', '16:00:00', 45),
(10, 4, '10:00:00', '16:00:00', 45);

-- Doctor 11: Steven Adams (ENT)
INSERT INTO `doctor_schedule` (`doctor_id`, `day_of_week`, `start_time`, `end_time`,`slot_duration`) VALUES
(11, 1, '09:00:00', '17:00:00', 30),
(11, 2, '09:00:00', '17:00:00', 30),
(11, 3, '09:00:00', '17:00:00', 30),
(11, 4, '09:00:00', '17:00:00', 30),
(11, 5, '09:00:00', '17:00:00', 30);

-- Table: doctor_leaves
CREATE TABLE `doctor_leaves` (
  `leave_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`leave_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `doctor_leaves` (`doctor_id`, `leave_date`, `reason`) VALUES
(1, '2024-01-15', 'Personal leave'),
(2, '2024-01-20', 'Medical checkup'),
(3, '2024-01-25', 'Family function'),
(4, '2024-02-01', 'Vacation'),
(5, '2024-02-05', 'Conference'),
(6, '2024-02-10', 'Sick leave'),
(7, '2024-02-15', 'Personal reasons'),
(8, '2024-02-20', 'Training program'),
(9, '2024-02-25', 'Wedding ceremony'),
(10, '2024-03-01', 'Medical appointment'),
(11, '2024-03-05', 'Family emergency'),
(1, '2024-03-10', 'Holiday'),
(2, '2024-03-15', 'Personal work'),
(3, '2024-03-20', 'Vacation');

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

INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason_for_visit`, `status`, `cancelled_by`) VALUES
(4, 1, '2024-01-10', '10:00:00', 'Regular dental checkup', 'completed', NULL),
(5, 2, '2024-01-11', '11:00:00', 'Chest pain and discomfort', 'completed', NULL),
(6, 3, '2024-01-12', '09:30:00', 'Fever and cold', 'completed', NULL),
(7, 4, '2024-01-13', '14:00:00', 'Skin rash treatment', 'completed', NULL),
(8, 5, '2024-01-14', '10:30:00', 'Child vaccination', 'completed', NULL),
(9, 6, '2024-01-15', '11:30:00', 'Knee pain consultation', 'completed', NULL),
(10, 7, '2024-01-16', '15:00:00', 'Headache and migraine', 'completed', NULL),
(11, 8, '2024-01-17', '13:00:00', 'Eye checkup', 'completed', NULL),
(12, 9, '2024-01-18', '10:00:00', 'Regular gynecology checkup', 'completed', NULL),
(13, 10, '2024-01-19', '14:30:00', 'Anxiety and stress', 'completed', NULL),
(14, 11, '2024-01-20', '11:00:00', 'Ear infection', 'pending', NULL),
(15, 1, '2024-01-21', '15:30:00', 'Tooth pain', 'pending', NULL),
(4, 2, '2024-01-22', '12:00:00', 'Heart checkup followup', 'pending', NULL),
(5, 3, '2024-01-23', '09:00:00', 'General health checkup', 'pending', NULL),
(6, 4, '2024-01-24', '14:00:00', 'Acne treatment', 'cancelled', 'patient'),
(7, 5, '2024-01-25', '11:00:00', 'Child fever', 'cancelled', 'doctor'),
(8, 6, '2024-01-26', '10:30:00', 'Back pain consultation', 'no-show', NULL),
(9, 7, '2024-01-27', '13:30:00', 'Neurology followup', 'pending', NULL),
(10, 8, '2024-01-28', '15:00:00', 'Vision problem', 'pending', NULL);

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

INSERT INTO `notifications` (`user_id`, `message`, `is_read`) VALUES
(4, 'Your appointment with Sarah Smith has been confirmed for 2024-01-10 at 10:00 AM', 1),
(5, 'Your appointment with Mike Johnson has been confirmed for 2024-01-11 at 11:00 AM', 1),
(6, 'Your appointment with James Walker has been confirmed for 2024-01-12 at 09:30 AM', 1),
(7, 'Your appointment with Patricia Hall has been confirmed for 2024-01-13 at 02:00 PM', 1),
(8, 'Your appointment with Daniel Allen has been confirmed for 2024-01-14 at 10:30 AM', 1),
(9, 'Your appointment with Susan Young has been confirmed for 2024-01-15 at 11:30 AM', 1),
(10, 'Your appointment with Matthew King has been confirmed for 2024-01-16 at 03:00 PM', 1),
(11, 'Your appointment with Nancy Wright has been confirmed for 2024-01-17 at 01:00 PM', 1),
(12, 'Your appointment with Kevin Scott has been confirmed for 2024-01-18 at 10:00 AM', 1),
(13, 'Your appointment with Betty Green has been confirmed for 2024-01-19 at 02:30 PM', 1),
(14, 'Your appointment with Steven Adams has been confirmed for 2024-01-20 at 11:00 AM', 0),
(15, 'Your appointment with Sarah Smith has been confirmed for 2024-01-21 at 03:30 PM', 0),
(2, 'You have a new appointment scheduled for 2024-01-21 at 03:30 PM', 0),
(3, 'You have a new appointment scheduled for 2024-01-22 at 12:00 PM', 0),
(16, 'You have a new appointment scheduled for 2024-01-23 at 09:00 AM', 0);

COMMIT;