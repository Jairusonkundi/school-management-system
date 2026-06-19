CREATE DATABASE IF NOT EXISTS sunshine_sms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sunshine_sms;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','student','parent') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  school_section ENUM('junior','secondary') NOT NULL,
  level_number TINYINT NOT NULL,
  stream VARCHAR(30) DEFAULT NULL,
  UNIQUE KEY uq_class (name, stream),
  INDEX idx_classes_section (school_section)
) ENGINE=InnoDB;

CREATE TABLE parents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  national_id VARCHAR(30) NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  alternate_phone VARCHAR(30) NULL,
  address VARCHAR(255) NULL,
  relationship VARCHAR(40) NOT NULL DEFAULT 'Parent/Guardian',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tsc_number VARCHAR(40) NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  specialization VARCHAR(120) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  admission_no VARCHAR(30) NOT NULL UNIQUE,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  gender ENUM('male','female') NOT NULL,
  date_of_birth DATE NOT NULL,
  school_section ENUM('junior','secondary') NOT NULL,
  class_id INT NOT NULL,
  parent_id INT NOT NULL,
  admission_date DATE NOT NULL,
  status ENUM('active','transferred','graduated','inactive') NOT NULL DEFAULT 'active',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (parent_id) REFERENCES parents(id),
  INDEX idx_students_section_class (school_section, class_id)
) ENGINE=InnoDB;

CREATE TABLE junior_learning_areas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  code VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  code VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE cbc_assessments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  learning_area_id INT NOT NULL,
  teacher_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year YEAR NOT NULL,
  competency VARCHAR(160) NOT NULL,
  descriptor ENUM('Exceeding Expectations','Meeting Expectations','Approaching Expectations','Below Expectations') NOT NULL,
  remarks TEXT NULL,
  assessed_at DATE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (learning_area_id) REFERENCES junior_learning_areas(id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  INDEX idx_cbc_student_term (student_id, academic_year, term)
) ENGINE=InnoDB;

CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  teacher_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year YEAR NOT NULL,
  cat_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  exam_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  total_score DECIMAL(5,2) AS (cat_score + exam_score) STORED,
  grade_letter CHAR(2) NOT NULL,
  remarks VARCHAR(255) NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  UNIQUE KEY uq_grade (student_id, subject_id, term, academic_year),
  INDEX idx_grades_ranking (academic_year, term, subject_id, total_score)
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  marked_by INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present','absent','late','excused') NOT NULL,
  note VARCHAR(255) NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (marked_by) REFERENCES users(id),
  UNIQUE KEY uq_attendance (student_id, attendance_date),
  INDEX idx_attendance_class_date (class_id, attendance_date)
) ENGINE=InnoDB;

CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year YEAR NOT NULL,
  amount_due DECIMAL(12,2) NOT NULL,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
  balance DECIMAL(12,2) AS (amount_due - amount_paid) STORED,
  due_date DATE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  UNIQUE KEY uq_fee_account (student_id, term, academic_year),
  INDEX idx_fee_balance (academic_year, term)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fee_id INT NOT NULL,
  student_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  method ENUM('cash','mpesa','bank','cheque') NOT NULL,
  reference VARCHAR(80) NOT NULL UNIQUE,
  received_by INT NOT NULL,
  paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (received_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  channel ENUM('email','system') NOT NULL DEFAULT 'email',
  subject VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notifications_status (status)
) ENGINE=InnoDB;

INSERT INTO users (name,email,password_hash,role) VALUES
('System Administrator','admin@sunshine.local','$2y$12$Mp5M/SzE0hLXSXH1jktFL.1b2LL0gE9fiWkLvWbPm6kO/6fmoaQKy','admin');
INSERT INTO classes (name, school_section, level_number, stream) VALUES
('Grade 7','junior',7,'Blue'),('Grade 8','junior',8,'Blue'),('Grade 9','junior',9,'Blue'),
('Form 1','secondary',1,'East'),('Form 2','secondary',2,'East'),('Form 3','secondary',3,'East'),('Form 4','secondary',4,'East');
INSERT INTO junior_learning_areas (name, code) VALUES
('English','ENG'),('Kiswahili','KIS'),('Mathematics','MATH'),('Integrated Science','IS'),('Social Studies','SS'),('Creative Arts and Sports','CAS'),('Pre-Technical Studies','PTS');
INSERT INTO subjects (name, code) VALUES
('English','ENG'),('Kiswahili','KIS'),('Mathematics','MATH'),('Biology','BIO'),('Chemistry','CHEM'),('Physics','PHY'),('History','HIS'),('Geography','GEO'),('Business Studies','BST');
