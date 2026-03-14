


DROP TABLE IF EXISTS `attendance`;
CREATE TABLE IF NOT EXISTS `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Late') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  KEY `enrollment_id` (`enrollment_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE IF NOT EXISTS `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) DEFAULT NULL,
  `lecturer_id` int DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'e.g., 2024-2025',
  `semester` enum('Semester 1','Semester 2') NOT NULL,
  PRIMARY KEY (`class_id`),
  KEY `course_code` (`course_code`),
  KEY `lecturer_id` (`lecturer_id`)
);

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `course_code`, `lecturer_id`, `academic_year`, `semester`) VALUES
(2, 'ICT101', 2, '2025-2026', 'Semester 1');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `credits` int NOT NULL,
  `semester_level` int NOT NULL COMMENT 'e.g., 1 to 8',
  `description` text,
  PRIMARY KEY (`course_code`)
);

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`, `credits`, `semester_level`, `description`) VALUES
('ICT101', 'Intro to Programming', 3, 1, NULL),
('ICT205', 'Database Management Systems', 4, 3, NULL),
('ICT301', 'Web Application Development', 3, 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `enrolled_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`enrollment_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
);

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `class_id`, `enrolled_date`) VALUES
(1, 3, 2, '2026-03-08 17:30:41');

-- --------------------------------------------------------

--
-- Table structure for table `fyp_projects`
--

DROP TABLE IF EXISTS `fyp_projects`;
CREATE TABLE IF NOT EXISTS `fyp_projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `student_id` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `submission_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `supervisor_id` (`supervisor_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedules`
--

DROP TABLE IF EXISTS `lab_schedules`;
CREATE TABLE IF NOT EXISTS `lab_schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `lab_name` varchar(50) NOT NULL COMMENT 'e.g., Networking Lab, Lab A',
  `class_id` int DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `class_id` (`class_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
CREATE TABLE IF NOT EXISTS `materials` (
  `material_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`material_id`),
  KEY `class_id` (`class_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

DROP TABLE IF EXISTS `notices`;
CREATE TABLE IF NOT EXISTS `notices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('General','Exam','Event','Lab') DEFAULT 'General',
  `posted_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `posted_by` (`posted_by`)
);

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `message`, `category`, `posted_by`, `created_at`) VALUES
(1, 'test', 'test', 'General', 1, '2026-03-08 15:59:10'),
(2, 'test', 'test', 'Exam', 1, '2026-03-08 16:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

DROP TABLE IF EXISTS `results`;
CREATE TABLE IF NOT EXISTS `results` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int DEFAULT NULL,
  `assessment_type` varchar(50) DEFAULT NULL COMMENT 'e.g., Midterm, Final, Assignment 1',
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `total_marks` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`result_id`),
  KEY `enrollment_id` (`enrollment_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `theme` varchar(10) DEFAULT 'light',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `role`, `phone`, `profile_pic`, `created_at`, `theme`) VALUES
(1, 'System Admin', 'hatheesha6504@gmail.com', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'admin', '0701207991', '1772981927.png', '2026-03-08 06:30:14', 'light'),
(2, 'John Doe', 'lecturer@ict.edu', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'lecturer', '0701207991', '1772979850.jpg', '2026-03-08 06:30:14', 'light'),
(3, 'Nethmi Nuwanthika', 'student@ict.edu', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'student', '', '1772984767.png', '2026-03-08 06:30:14', 'light');


