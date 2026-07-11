CREATE DATABASE IF NOT EXISTS sunshine_sms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sunshine_sms;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS fee_invoices;
DROP TABLE IF EXISTS exam_results;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS teacher_assignments;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admission_sequences;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admission_sequences (
  id TINYINT PRIMARY KEY,
  next_number INT NOT NULL,
  CONSTRAINT chk_admission_next_number CHECK (next_number > 0)
) ENGINE=InnoDB;

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  stream VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_class_name_stream (name, stream),
  INDEX idx_classes_name (name)
) ENGINE=InnoDB;

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_subject_name (name)
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','parent','student') NOT NULL,
  linked_student_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  INDEX idx_users_role_active (role, is_active),
  INDEX idx_users_linked_student (linked_student_id),
  CONSTRAINT chk_users_email_not_blank CHECK (email <> ''),
  CONSTRAINT chk_users_password_hash_not_blank CHECK (password_hash <> '')
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admission_no VARCHAR(30) NOT NULL,
  name VARCHAR(160) NOT NULL,
  class_id INT NOT NULL,
  guardian_user_id INT NULL,
  medical_notes TEXT NULL,
  discipline_notes TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_students_admission_no (admission_no),
  INDEX idx_students_class_active (class_id, is_active),
  INDEX idx_students_guardian (guardian_user_id),
  CONSTRAINT chk_students_name_not_blank CHECK (name <> ''),
  CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_students_guardian FOREIGN KEY (guardian_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE users
  ADD CONSTRAINT fk_users_linked_student FOREIGN KEY (linked_student_id) REFERENCES students(id) ON DELETE SET NULL;

CREATE TABLE teacher_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_user_id INT NOT NULL,
  class_id INT NOT NULL,
  subject_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_teacher_assignment (teacher_user_id, class_id, subject_id),
  INDEX idx_teacher_assignments_scope (class_id, subject_id),
  CONSTRAINT fk_teacher_assignments_teacher FOREIGN KEY (teacher_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_assignments_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_assignments_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('present','absent','excused') NOT NULL,
  recorded_by INT NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_attendance_student_date (student_id, date),
  INDEX idx_attendance_date_status (date, status),
  INDEX idx_attendance_student_date (student_id, date),
  CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE exam_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  term VARCHAR(30) NOT NULL,
  marks DECIMAL(5,2) NOT NULL,
  grade VARCHAR(3) NOT NULL,
  recorded_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_exam_result (student_id, subject_id, term),
  INDEX idx_exam_results_subject_term_marks (subject_id, term, marks),
  INDEX idx_exam_results_student_term (student_id, term),
  CONSTRAINT chk_exam_results_marks CHECK (marks >= 0 AND marks <= 100),
  CONSTRAINT chk_exam_results_term_not_blank CHECK (term <> ''),
  CONSTRAINT fk_exam_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_exam_results_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_exam_results_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE fee_invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  term VARCHAR(30) NOT NULL,
  amount_due DECIMAL(12,2) NOT NULL,
  balance DECIMAL(12,2) NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fee_invoice_student_term (student_id, term),
  INDEX idx_fee_invoices_term_balance (term, balance),
  CONSTRAINT chk_fee_invoices_amount_due CHECK (amount_due > 0),
  CONSTRAINT chk_fee_invoices_balance CHECK (balance >= 0 AND balance <= amount_due),
  CONSTRAINT chk_fee_invoices_term_not_blank CHECK (term <> ''),
  CONSTRAINT fk_fee_invoices_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_fee_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  date DATE NOT NULL,
  method ENUM('cash','mpesa','bank','cheque') NOT NULL,
  reference VARCHAR(80) NOT NULL,
  recorded_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_payments_reference (reference),
  INDEX idx_payments_invoice_date (invoice_id, date),
  INDEX idx_payments_date (date),
  CONSTRAINT chk_payments_amount CHECK (amount > 0),
  CONSTRAINT chk_payments_reference_not_blank CHECK (reference <> ''),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES fee_invoices(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_user_id INT NOT NULL,
  type ENUM('payment_receipt','absence_alert','system') NOT NULL,
  message TEXT NOT NULL,
  sent_at DATETIME NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notifications_recipient (recipient_user_id, created_at),
  INDEX idx_notifications_status (status),
  CONSTRAINT fk_notifications_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO admission_sequences (id, next_number) VALUES (1, 1);

INSERT INTO users (name, email, password_hash, role) VALUES
('System Administrator', 'admin@sunshine.local', '$2y$12$Mp5M/SzE0hLXSXH1jktFL.1b2LL0gE9fiWkLvWbPm6kO/6fmoaQKy', 'admin');

INSERT INTO classes (name, stream) VALUES
('Grade 7', 'Blue'), ('Grade 8', 'Blue'), ('Grade 9', 'Blue'),
('Form 1', 'East'), ('Form 2', 'East'), ('Form 3', 'East'), ('Form 4', 'East');

INSERT INTO subjects (name) VALUES
('English'), ('Kiswahili'), ('Mathematics'), ('Biology'), ('Chemistry'), ('Physics'), ('History'), ('Geography'), ('Business Studies');
