-- 1. Create ENUM types first
CREATE TYPE attendance_status AS ENUM ('Present', 'Absent', 'Late');
CREATE TYPE semester_type AS ENUM ('Semester 1', 'Semester 2');
CREATE TYPE fyp_status AS ENUM ('pending', 'approved', 'rejected', 'completed');
CREATE TYPE day_of_week_enum AS ENUM ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
CREATE TYPE notice_category_enum AS ENUM ('General', 'Exam', 'Event', 'Lab');
CREATE TYPE user_role_enum AS ENUM ('admin', 'lecturer', 'student');

-- --------------------------------------------------------

-- Table: users
DROP TABLE IF EXISTS "users" CASCADE;
CREATE TABLE "users" (
  "user_id" SERIAL PRIMARY KEY,
  "full_name" varchar(100) NOT NULL,
  "email" varchar(100) NOT NULL UNIQUE,
  "password_hash" varchar(255) NOT NULL,
  "role" user_role_enum NOT NULL,
  "phone" varchar(20) DEFAULT NULL,
  "profile_pic" varchar(255) DEFAULT 'default.png',
  "created_at" timestamp DEFAULT CURRENT_TIMESTAMP,
  "theme" varchar(10) DEFAULT 'light'
);

-- Table: courses
DROP TABLE IF EXISTS "courses" CASCADE;
CREATE TABLE "courses" (
  "course_code" varchar(20) PRIMARY KEY,
  "course_name" varchar(150) NOT NULL,
  "credits" int NOT NULL,
  "semester_level" int NOT NULL,
  "description" text
);
COMMENT ON COLUMN "courses"."semester_level" IS 'e.g., 1 to 8';

-- Table: classes
DROP TABLE IF EXISTS "classes" CASCADE;
CREATE TABLE "classes" (
  "class_id" SERIAL PRIMARY KEY,
  "course_code" varchar(20) REFERENCES "courses"("course_code"),
  "lecturer_id" int REFERENCES "users"("user_id"),
  "academic_year" varchar(20) DEFAULT NULL,
  "semester" semester_type NOT NULL
);
COMMENT ON COLUMN "classes"."academic_year" IS 'e.g., 2024-2025';

-- Table: enrollments
DROP TABLE IF EXISTS "enrollments" CASCADE;
CREATE TABLE "enrollments" (
  "enrollment_id" SERIAL PRIMARY KEY,
  "student_id" int REFERENCES "users"("user_id"),
  "class_id" int REFERENCES "classes"("class_id"),
  "enrolled_date" timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table: attendance
DROP TABLE IF EXISTS "attendance" CASCADE;
CREATE TABLE "attendance" (
  "attendance_id" SERIAL PRIMARY KEY,
  "enrollment_id" int NOT NULL REFERENCES "enrollments"("enrollment_id"),
  "attendance_date" date NOT NULL,
  "status" attendance_status NOT NULL,
  "remarks" varchar(255) DEFAULT NULL
);

-- Table: fyp_projects
DROP TABLE IF EXISTS "fyp_projects" CASCADE;
CREATE TABLE "fyp_projects" (
  "project_id" SERIAL PRIMARY KEY,
  "title" varchar(200) NOT NULL,
  "description" text,
  "student_id" int UNIQUE REFERENCES "users"("user_id"),
  "supervisor_id" int REFERENCES "users"("user_id"),
  "status" fyp_status DEFAULT 'pending',
  "submission_file" varchar(255) DEFAULT NULL
);

-- Table: lab_schedules
DROP TABLE IF EXISTS "lab_schedules" CASCADE;
CREATE TABLE "lab_schedules" (
  "schedule_id" SERIAL PRIMARY KEY,
  "lab_name" varchar(50) NOT NULL,
  "class_id" int REFERENCES "classes"("class_id"),
  "day_of_week" day_of_week_enum DEFAULT NULL,
  "start_time" time NOT NULL,
  "end_time" time NOT NULL
);
COMMENT ON COLUMN "lab_schedules"."lab_name" IS 'e.g., Networking Lab, Lab A';

-- Table: materials
DROP TABLE IF EXISTS "materials" CASCADE;
CREATE TABLE "materials" (
  "material_id" SERIAL PRIMARY KEY,
  "class_id" int REFERENCES "classes"("class_id"),
  "title" varchar(150) NOT NULL,
  "file_path" varchar(255) NOT NULL,
  "upload_date" timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table: notices
DROP TABLE IF EXISTS "notices" CASCADE;
CREATE TABLE "notices" (
  "id" SERIAL PRIMARY KEY,
  "title" varchar(255) NOT NULL,
  "message" text NOT NULL,
  "category" notice_category_enum DEFAULT 'General',
  "posted_by" int NOT NULL REFERENCES "users"("user_id"),
  "created_at" timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table: results
DROP TABLE IF EXISTS "results" CASCADE;
CREATE TABLE "results" (
  "result_id" SERIAL PRIMARY KEY,
  "enrollment_id" int REFERENCES "enrollments"("enrollment_id"),
  "assessment_type" varchar(50) DEFAULT NULL,
  "marks_obtained" decimal(5,2) DEFAULT NULL,
  "total_marks" decimal(5,2) DEFAULT NULL
);
COMMENT ON COLUMN "results"."assessment_type" IS 'e.g., Midterm, Final, Assignment 1';

-- --------------------------------------------------------
-- Data Insertion (Dumping data)
-- --------------------------------------------------------

INSERT INTO "users" ("user_id", "full_name", "email", "password_hash", "role", "phone", "profile_pic", "created_at", "theme") VALUES
(1, 'System Admin', 'hatheesha6504@gmail.com', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'admin', '0701207991', '1772981927.png', '2026-03-08 06:30:14', 'light'),
(2, 'John Doe', 'lecturer@ict.edu', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'lecturer', '0701207991', '1772979850.jpg', '2026-03-08 06:30:14', 'light'),
(3, 'Nethmi Nuwanthika', 'student@ict.edu', '$2y$10$Vl3ML/If5SCccTcC3L8wC.HzEBXpNTkmePxQJnjzLEzVsV7GEacIS', 'student', '', '1772984767.png', '2026-03-08 06:30:14', 'light');

-- Reset Serial sequences after manual ID insertion
SELECT setval(pg_get_serial_sequence('users', 'user_id'), coalesce(max(user_id), 1)) FROM users;

INSERT INTO "courses" ("course_code", "course_name", "credits", "semester_level", "description") VALUES
('ICT101', 'Intro to Programming', 3, 1, NULL),
('ICT205', 'Database Management Systems', 4, 3, NULL),
('ICT301', 'Web Application Development', 3, 5, NULL);

INSERT INTO "classes" ("class_id", "course_code", "lecturer_id", "academic_year", "semester") VALUES
(2, 'ICT101', 2, '2025-2026', 'Semester 1');

INSERT INTO "enrollments" ("enrollment_id", "student_id", "class_id", "enrolled_date") VALUES
(1, 3, 2, '2026-03-08 17:30:41');

INSERT INTO "notices" ("id", "title", "message", "category", "posted_by", "created_at") VALUES
(1, 'test', 'test', 'General', 1, '2026-03-08 15:59:10'),
(2, 'test', 'test', 'Exam', 1, '2026-03-08 16:01:13');

-- --------------------------------------------------------
-- Indexes (In Postgres, we create them separately)
-- --------------------------------------------------------
CREATE INDEX idx_attendance_enrollment ON "attendance" ("enrollment_id");
CREATE INDEX idx_classes_course ON "classes" ("course_code");
CREATE INDEX idx_classes_lecturer ON "classes" ("lecturer_id");
CREATE INDEX idx_enrollments_student ON "enrollments" ("student_id");
CREATE INDEX idx_enrollments_class ON "enrollments" ("class_id");
CREATE INDEX idx_fyp_supervisor ON "fyp_projects" ("supervisor_id");
CREATE INDEX idx_lab_class ON "lab_schedules" ("class_id");
CREATE INDEX idx_materials_class ON "materials" ("class_id");
CREATE INDEX idx_notices_posted_by ON "notices" ("posted_by");
CREATE INDEX idx_results_enrollment ON "results" ("enrollment_id");