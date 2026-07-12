SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','parent','student') NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS education_levels (
  level_id INT AUTO_INCREMENT PRIMARY KEY,
  level_name VARCHAR(50) NOT NULL UNIQUE,
  level_order TINYINT NOT NULL,
  UNIQUE KEY unique_level_order (level_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS classes (
  class_id INT AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(100) NOT NULL,
  level_id INT NOT NULL,
  grade_name VARCHAR(20) NULL,
  grade_level ENUM('PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9') NOT NULL,
  stream_name VARCHAR(60) NOT NULL DEFAULT '',
  section ENUM('Junior','Secondary') NULL,
  teacher_id INT NULL,
  academic_year VARCHAR(10) NOT NULL,
  UNIQUE KEY unique_class_grade_year_stream (grade_level, academic_year, stream_name),
  INDEX idx_classes_grade (grade_level),
  INDEX idx_classes_level_grade (level_id, grade_name),
  INDEX idx_classes_teacher (teacher_id),
  CONSTRAINT fk_classes_level FOREIGN KEY (level_id) REFERENCES education_levels(level_id),
  CONSTRAINT fk_classes_teacher FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  admission_no VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  date_of_birth DATE NULL,
  gender ENUM('Male','Female','Other') NULL,
  nationality VARCHAR(80) NULL,
  grade_level ENUM('PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9') NOT NULL,
  section ENUM('Junior','Secondary') NULL,
  class_id INT NOT NULL,
  guardian_id INT NULL,
  guardian_phone VARCHAR(40) NULL,
  emergency_contact_name VARCHAR(150) NULL,
  emergency_contact_phone VARCHAR(40) NULL,
  date_of_admission DATE NOT NULL,
  medical_notes TEXT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_students_user (user_id),
  INDEX idx_students_class_active (class_id, is_active),
  INDEX idx_students_guardian (guardian_id),
  INDEX idx_students_grade (grade_level),
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(class_id),
  CONSTRAINT fk_students_guardian FOREIGN KEY (guardian_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
  subject_id INT AUTO_INCREMENT PRIMARY KEY,
  subject_name VARCHAR(120) NOT NULL,
  offered_grades SET('Grade 7','Grade 8','Grade 9') NOT NULL DEFAULT 'Grade 7,Grade 8,Grade 9',
  section ENUM('Junior','Secondary','Both') NULL,
  class_id INT NULL,
  UNIQUE KEY unique_subject_name (subject_name),
  INDEX idx_subjects_class (class_id),
  CONSTRAINT fk_subjects_class FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subject_levels (
  subject_id INT NOT NULL,
  level_id INT NOT NULL,
  PRIMARY KEY (subject_id, level_id),
  CONSTRAINT fk_subject_levels_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
  CONSTRAINT fk_subject_levels_level FOREIGN KEY (level_id) REFERENCES education_levels(level_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_assignments (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_user_id INT NOT NULL,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_teacher_assignment (teacher_user_id, class_id, subject_id),
  INDEX idx_teacher_assignments_class_subject (class_id, subject_id),
  CONSTRAINT fk_teacher_assignments_teacher FOREIGN KEY (teacher_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_assignments_class FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_assignments_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
  attendance_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('Present','Absent','Late') NOT NULL DEFAULT 'Present',
  recorded_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_attendance (student_id, date),
  INDEX idx_attendance_class_date (class_id, date),
  INDEX idx_attendance_status (status),
  CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_class FOREIGN KEY (class_id) REFERENCES classes(class_id),
  CONSTRAINT fk_attendance_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_results (
  result_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  class_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year VARCHAR(10) NOT NULL,
  marks DECIMAL(5,2) NULL,
  grade VARCHAR(5) NULL,
  comment TEXT NULL,
  entered_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_result (student_id, subject_id, term, academic_year),
  INDEX idx_exam_results_class_term_year (class_id, term, academic_year),
  CONSTRAINT chk_exam_marks CHECK (marks >= 0 AND marks <= 100),
  CONSTRAINT chk_exam_term CHECK (term IN (1,2,3)),
  CONSTRAINT fk_results_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  CONSTRAINT fk_results_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
  CONSTRAINT fk_results_class FOREIGN KEY (class_id) REFERENCES classes(class_id),
  CONSTRAINT fk_results_entered_by FOREIGN KEY (entered_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fee_structures (
  fee_structure_id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year VARCHAR(10) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  UNIQUE KEY unique_fee_structure (class_id, term, academic_year),
  CONSTRAINT chk_fee_structure_amount CHECK (amount > 0),
  CONSTRAINT chk_fee_structure_term CHECK (term IN (1,2,3)),
  CONSTRAINT fk_fee_structures_class FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fee_invoices (
  invoice_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  term TINYINT NOT NULL,
  academic_year VARCHAR(10) NOT NULL,
  amount_due DECIMAL(10,2) NOT NULL,
  amount_paid DECIMAL(10,2) DEFAULT 0.00,
  balance DECIMAL(10,2) NOT NULL,
  due_date DATE NULL,
  status ENUM('Unpaid','Partial','Paid') DEFAULT 'Unpaid',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_invoice (student_id, term, academic_year),
  INDEX idx_invoices_term_year_status (term, academic_year, status),
  CONSTRAINT chk_invoice_amounts CHECK (amount_due >= 0 AND amount_paid >= 0 AND balance >= 0),
  CONSTRAINT chk_invoice_term CHECK (term IN (1,2,3)),
  CONSTRAINT fk_invoices_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL,
  payment_date DATE NOT NULL,
  payment_method ENUM('Cash','Bank Transfer','Mobile Money','Cheque') NOT NULL,
  receipt_no VARCHAR(30) NOT NULL UNIQUE,
  recorded_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payments_invoice_date (invoice_id, payment_date),
  CONSTRAINT chk_payment_amount CHECK (amount_paid > 0),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES fee_invoices(invoice_id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_id INT NOT NULL,
  subject VARCHAR(255) NULL,
  message TEXT NOT NULL,
  type ENUM('Absence','Payment','Fee Arrears','Announcement','Disciplinary') NOT NULL,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Sent','Failed','Pending') DEFAULT 'Pending',
  UNIQUE KEY unique_notification_seed (recipient_id, type, subject, sent_at),
  INDEX idx_notifications_recipient (recipient_id, sent_at),
  INDEX idx_notifications_type_status (type, status),
  CONSTRAINT fk_notifications_recipient FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS disciplinary_records (
  record_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  incident_date DATE NOT NULL,
  description TEXT NOT NULL,
  action_taken TEXT NULL,
  recorded_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_discipline_seed (student_id, incident_date, recorded_by),
  INDEX idx_discipline_student_date (student_id, incident_date),
  CONSTRAINT fk_discipline_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  CONSTRAINT fk_discipline_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admission_sequences (
  id TINYINT PRIMARY KEY,
  next_number INT NOT NULL,
  CONSTRAINT chk_admission_next_number CHECK (next_number > 0)
) ENGINE=InnoDB;

INSERT IGNORE INTO education_levels (level_id, level_name, level_order) VALUES
(1, 'Early Years Education', 1),
(2, 'Lower Primary', 2),
(3, 'Upper Primary', 3),
(4, 'Junior Secondary School', 4);

INSERT IGNORE INTO admission_sequences (id, next_number) VALUES (1, 14);

INSERT IGNORE INTO users (full_name, email, password_hash, role) VALUES
('System Administrator', 'admin@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Demo Teacher', 'teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Demo Parent', 'parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Demo Student', 'student@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Mary Wanjiku Mwangi', 'mary.mwangi.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Peter Otieno Okello', 'peter.okello.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Faith Naliaka Wekesa', 'faith.wekesa.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Joseph Kamau Njoroge', 'joseph.njoroge.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Amina Ali Hassan', 'amina.hassan.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Daniel Kiptoo Kibet', 'daniel.kibet.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Grace Muthoni Njenga', 'grace.njenga.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Samuel Mutiso Musyoka', 'samuel.musyoka.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Esther Achieng Odhiambo', 'esther.odhiambo.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Pauline Njeri Kariuki', 'pauline.kariuki.parent@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent'),
('Teacher Language Activities', 'language.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Mathematical Activities', 'mathematical.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Environmental Activities', 'environmental.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Psychomotor Creative', 'psychomotor.creative.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Religious Education Activities', 'religious.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher English Language Activities', 'english.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Kiswahili Activities', 'kiswahili.activities.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Hygiene Nutrition', 'hygiene.nutrition.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Movement Creative', 'movement.creative.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher English', 'english.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Kiswahili', 'kiswahili.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Mathematics', 'mathematics.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Science Technology', 'science.technology.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Agriculture', 'agriculture.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Social Studies', 'social.studies.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Religious Education', 'religious.education.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Creative Arts', 'creative.arts.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher PE Sports', 'pe.sports.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Integrated Science', 'integrated.science.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Health Education', 'health.education.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Pre Technical', 'pretechnical.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Business Studies', 'business.studies.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Agriculture Nutrition', 'agriculture.nutrition.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Life Skills', 'life.skills.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Teacher Foreign Language', 'foreign.language.teacher@ska.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

INSERT IGNORE INTO classes (class_name, level_id, grade_name, grade_level, stream_name, academic_year)
SELECT grade_name, level_id, grade_name, grade_name, '', '2026'
FROM (
  SELECT 'PP1' grade_name, 1 level_id UNION ALL SELECT 'PP2', 1
  UNION ALL SELECT 'Grade 1', 2 UNION ALL SELECT 'Grade 2', 2 UNION ALL SELECT 'Grade 3', 2
  UNION ALL SELECT 'Grade 4', 3 UNION ALL SELECT 'Grade 5', 3 UNION ALL SELECT 'Grade 6', 3
  UNION ALL SELECT 'Grade 7', 4 UNION ALL SELECT 'Grade 8', 4 UNION ALL SELECT 'Grade 9', 4
) grades;

UPDATE classes c
JOIN users u ON u.email = 'teacher@ska.ac.ke'
SET c.teacher_id = u.user_id
WHERE c.academic_year = '2026' AND c.teacher_id IS NULL;

INSERT IGNORE INTO subjects (subject_name, offered_grades, section) VALUES
('Language Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Mathematical Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Environmental Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Psychomotor and Creative Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Religious Education Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('English Language Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Kiswahili / Kenya Sign Language Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Hygiene and Nutrition Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('Movement and Creative Activities', 'Grade 7,Grade 8,Grade 9', NULL),
('English', 'Grade 7,Grade 8,Grade 9', NULL),
('Kiswahili / Kenya Sign Language', 'Grade 7,Grade 8,Grade 9', NULL),
('Mathematics', 'Grade 7,Grade 8,Grade 9', NULL),
('Science and Technology', 'Grade 7,Grade 8,Grade 9', NULL),
('Agriculture', 'Grade 7,Grade 8,Grade 9', NULL),
('Social Studies', 'Grade 7,Grade 8,Grade 9', NULL),
('Religious Education', 'Grade 7,Grade 8,Grade 9', NULL),
('Creative Arts', 'Grade 7,Grade 8,Grade 9', NULL),
('Physical Education and Sports', 'Grade 7,Grade 8,Grade 9', NULL),
('Integrated Science', 'Grade 7,Grade 8,Grade 9', NULL),
('Health Education', 'Grade 7,Grade 8,Grade 9', NULL),
('Pre-Technical Studies', 'Grade 7,Grade 8,Grade 9', NULL),
('Business Studies', 'Grade 7,Grade 8,Grade 9', NULL),
('Agriculture and Nutrition', 'Grade 7,Grade 8,Grade 9', NULL),
('Life Skills Education', 'Grade 7,Grade 8,Grade 9', NULL),
('Foreign or Indigenous Language', 'Grade 7,Grade 8,Grade 9', NULL);

INSERT IGNORE INTO subject_levels (subject_id, level_id)
SELECT s.subject_id, e.level_id FROM subjects s JOIN education_levels e ON e.level_name = 'Early Years Education'
WHERE s.subject_name IN ('Language Activities','Mathematical Activities','Environmental Activities','Psychomotor and Creative Activities','Religious Education Activities');

INSERT IGNORE INTO subject_levels (subject_id, level_id)
SELECT s.subject_id, e.level_id FROM subjects s JOIN education_levels e ON e.level_name = 'Lower Primary'
WHERE s.subject_name IN ('English Language Activities','Kiswahili / Kenya Sign Language Activities','Mathematical Activities','Environmental Activities','Hygiene and Nutrition Activities','Religious Education Activities','Movement and Creative Activities');

INSERT IGNORE INTO subject_levels (subject_id, level_id)
SELECT s.subject_id, e.level_id FROM subjects s JOIN education_levels e ON e.level_name = 'Upper Primary'
WHERE s.subject_name IN ('English','Kiswahili / Kenya Sign Language','Mathematics','Science and Technology','Agriculture','Social Studies','Religious Education','Creative Arts','Physical Education and Sports');

INSERT IGNORE INTO subject_levels (subject_id, level_id)
SELECT s.subject_id, e.level_id FROM subjects s JOIN education_levels e ON e.level_name = 'Junior Secondary School'
WHERE s.subject_name IN ('English','Kiswahili / Kenya Sign Language','Mathematics','Integrated Science','Health Education','Pre-Technical Studies','Social Studies','Religious Education','Business Studies','Agriculture and Nutrition','Life Skills Education','Physical Education and Sports','Creative Arts','Foreign or Indigenous Language');

INSERT IGNORE INTO teacher_assignments (teacher_user_id, class_id, subject_id)
SELECT u.user_id, c.class_id, s.subject_id
FROM classes c
JOIN subject_levels sl ON sl.level_id = c.level_id
JOIN subjects s ON s.subject_id = sl.subject_id
JOIN users u ON u.email = CASE s.subject_name
  WHEN 'Language Activities' THEN 'language.activities.teacher@ska.ac.ke'
  WHEN 'Mathematical Activities' THEN 'mathematical.activities.teacher@ska.ac.ke'
  WHEN 'Environmental Activities' THEN 'environmental.activities.teacher@ska.ac.ke'
  WHEN 'Psychomotor and Creative Activities' THEN 'psychomotor.creative.teacher@ska.ac.ke'
  WHEN 'Religious Education Activities' THEN 'religious.activities.teacher@ska.ac.ke'
  WHEN 'English Language Activities' THEN 'english.activities.teacher@ska.ac.ke'
  WHEN 'Kiswahili / Kenya Sign Language Activities' THEN 'kiswahili.activities.teacher@ska.ac.ke'
  WHEN 'Hygiene and Nutrition Activities' THEN 'hygiene.nutrition.teacher@ska.ac.ke'
  WHEN 'Movement and Creative Activities' THEN 'movement.creative.teacher@ska.ac.ke'
  WHEN 'English' THEN 'english.teacher@ska.ac.ke'
  WHEN 'Kiswahili / Kenya Sign Language' THEN 'kiswahili.teacher@ska.ac.ke'
  WHEN 'Mathematics' THEN 'mathematics.teacher@ska.ac.ke'
  WHEN 'Science and Technology' THEN 'science.technology.teacher@ska.ac.ke'
  WHEN 'Agriculture' THEN 'agriculture.teacher@ska.ac.ke'
  WHEN 'Social Studies' THEN 'social.studies.teacher@ska.ac.ke'
  WHEN 'Religious Education' THEN 'religious.education.teacher@ska.ac.ke'
  WHEN 'Creative Arts' THEN 'creative.arts.teacher@ska.ac.ke'
  WHEN 'Physical Education and Sports' THEN 'pe.sports.teacher@ska.ac.ke'
  WHEN 'Integrated Science' THEN 'integrated.science.teacher@ska.ac.ke'
  WHEN 'Health Education' THEN 'health.education.teacher@ska.ac.ke'
  WHEN 'Pre-Technical Studies' THEN 'pretechnical.teacher@ska.ac.ke'
  WHEN 'Business Studies' THEN 'business.studies.teacher@ska.ac.ke'
  WHEN 'Agriculture and Nutrition' THEN 'agriculture.nutrition.teacher@ska.ac.ke'
  WHEN 'Life Skills Education' THEN 'life.skills.teacher@ska.ac.ke'
  WHEN 'Foreign or Indigenous Language' THEN 'foreign.language.teacher@ska.ac.ke'
END
WHERE c.academic_year = '2026';

INSERT IGNORE INTO students (user_id, admission_no, full_name, date_of_birth, gender, nationality, grade_level, class_id, guardian_id, guardian_phone, emergency_contact_name, emergency_contact_phone, date_of_admission, medical_notes)
SELECT su.user_id, seed.admission_no, seed.full_name, seed.date_of_birth, seed.gender, 'Kenyan', seed.grade_level, c.class_id, gu.user_id, seed.guardian_phone, seed.emergency_name, seed.emergency_phone, '2026-01-08', seed.medical_notes
FROM (
  SELECT 'SKA-2026-00001' admission_no, 'Amani Wanjiku Mwangi' full_name, '2020-05-14' date_of_birth, 'Female' gender, 'PP1' grade_level, 'parent@ska.ac.ke' guardian_email, '0712000001' guardian_phone, 'Demo Parent' emergency_name, '0712000001' emergency_phone, 'Peanut allergy noted.' medical_notes, 'student@ska.ac.ke' student_email
  UNION ALL SELECT 'SKA-2026-00002','Baraka Otieno Okello','2019-03-21','Male','PP2','peter.okello.parent@ska.ac.ke','0712000002','Peter Otieno Okello','0712000002','', NULL
  UNION ALL SELECT 'SKA-2026-00003','Neema Naliaka Wekesa','2018-07-10','Female','Grade 1','faith.wekesa.parent@ska.ac.ke','0712000003','Faith Naliaka Wekesa','0712000003','Uses reading glasses.', NULL
  UNION ALL SELECT 'SKA-2026-00004','Jabali Kamau Njoroge','2017-09-02','Male','Grade 2','joseph.njoroge.parent@ska.ac.ke','0712000004','Joseph Kamau Njoroge','0712000004','', NULL
  UNION ALL SELECT 'SKA-2026-00005','Imani Amina Hassan','2016-11-18','Female','Grade 3','amina.hassan.parent@ska.ac.ke','0712000005','Amina Ali Hassan','0712000005','Asthma inhaler kept by class teacher.', NULL
  UNION ALL SELECT 'SKA-2026-00006','Chebet Kiptoo Kibet','2015-06-26','Female','Grade 4','daniel.kibet.parent@ska.ac.ke','0712000006','Daniel Kiptoo Kibet','0712000006','', NULL
  UNION ALL SELECT 'SKA-2026-00007','Kiprono Kiptoo Kibet','2015-12-04','Male','Grade 4','daniel.kibet.parent@ska.ac.ke','0712000006','Daniel Kiptoo Kibet','0712000006','Sibling pair for parent portal testing.', NULL
  UNION ALL SELECT 'SKA-2026-00008','Muthoni Grace Njenga','2014-08-15','Female','Grade 5','grace.njenga.parent@ska.ac.ke','0712000007','Grace Muthoni Njenga','0712000007','', NULL
  UNION ALL SELECT 'SKA-2026-00009','Mutiso Samuel Musyoka','2013-04-23','Male','Grade 6','samuel.musyoka.parent@ska.ac.ke','0712000008','Samuel Mutiso Musyoka','0712000008','', NULL
  UNION ALL SELECT 'SKA-2026-00010','Achieng Esther Odhiambo','2012-02-12','Female','Grade 7','esther.odhiambo.parent@ska.ac.ke','0712000009','Esther Achieng Odhiambo','0712000009','', NULL
  UNION ALL SELECT 'SKA-2026-00011','Odhiambo Brian Otieno','2012-10-01','Male','Grade 7','esther.odhiambo.parent@ska.ac.ke','0712000009','Esther Achieng Odhiambo','0712000009','Sibling pair for JSS ranking.', NULL
  UNION ALL SELECT 'SKA-2026-00012','Njeri Pauline Kariuki','2011-01-30','Female','Grade 8','pauline.kariuki.parent@ska.ac.ke','0712000010','Pauline Njeri Kariuki','0712000010','', NULL
  UNION ALL SELECT 'SKA-2026-00013','Lemayian Saitoti Ole','2010-06-09','Male','Grade 9','mary.mwangi.parent@ska.ac.ke','0712000011','Mary Wanjiku Mwangi','0712000011','', NULL
) seed
JOIN classes c ON c.grade_level = seed.grade_level AND c.academic_year = '2026'
JOIN users gu ON gu.email = seed.guardian_email
LEFT JOIN users su ON su.email = seed.student_email;

INSERT IGNORE INTO attendance (student_id, class_id, date, status, recorded_by)
SELECT s.student_id, s.class_id, d.date_value,
  CASE
    WHEN s.admission_no IN ('SKA-2026-00003','SKA-2026-00010') AND d.date_value = '2026-02-05' THEN 'Absent'
    WHEN s.admission_no IN ('SKA-2026-00006','SKA-2026-00012') AND d.date_value = '2026-02-06' THEN 'Late'
    ELSE 'Present'
  END,
  u.user_id
FROM students s
JOIN users u ON u.email = 'teacher@ska.ac.ke'
JOIN (
  SELECT DATE('2026-02-04') date_value UNION ALL SELECT DATE('2026-02-05') UNION ALL SELECT DATE('2026-02-06')
) d;

INSERT IGNORE INTO exam_results (student_id, subject_id, class_id, term, academic_year, marks, grade, comment, entered_by)
SELECT s.student_id, sub.subject_id, s.class_id, 1, '2026',
  CASE WHEN el.level_name IN ('Upper Primary','Junior Secondary School')
    THEN 45 + MOD(s.student_id * 7 + sub.subject_id * 5, 51)
    ELSE NULL
  END AS marks,
  CASE
    WHEN el.level_name IN ('Early Years Education','Lower Primary') THEN ELT(1 + MOD(s.student_id + sub.subject_id, 4), 'EE','ME','AE','BE')
    WHEN 45 + MOD(s.student_id * 7 + sub.subject_id * 5, 51) >= 80 THEN 'EE'
    WHEN 45 + MOD(s.student_id * 7 + sub.subject_id * 5, 51) >= 50 THEN 'ME'
    WHEN 45 + MOD(s.student_id * 7 + sub.subject_id * 5, 51) >= 30 THEN 'AE'
    ELSE 'BE'
  END AS grade,
  CASE WHEN el.level_name IN ('Upper Primary','Junior Secondary School') THEN 'Term 1 continuous assessment sample.' ELSE 'Competency observation recorded for Term 1.' END,
  ta.teacher_user_id
FROM students s
JOIN classes c ON c.class_id = s.class_id
JOIN education_levels el ON el.level_id = c.level_id
JOIN subject_levels sl ON sl.level_id = c.level_id
JOIN subjects sub ON sub.subject_id = sl.subject_id
JOIN teacher_assignments ta ON ta.class_id = c.class_id AND ta.subject_id = sub.subject_id;

INSERT IGNORE INTO fee_structures (class_id, term, academic_year, amount)
SELECT c.class_id, t.term, '2026',
  CASE c.grade_level
    WHEN 'PP1' THEN 12000 WHEN 'PP2' THEN 12500
    WHEN 'Grade 1' THEN 14000 WHEN 'Grade 2' THEN 14500 WHEN 'Grade 3' THEN 15000
    WHEN 'Grade 4' THEN 17000 WHEN 'Grade 5' THEN 17500 WHEN 'Grade 6' THEN 18000
    WHEN 'Grade 7' THEN 22000 WHEN 'Grade 8' THEN 23000 ELSE 24000
  END
FROM classes c
JOIN (SELECT 1 term UNION ALL SELECT 2 UNION ALL SELECT 3) t
WHERE c.academic_year = '2026';

INSERT IGNORE INTO fee_invoices (student_id, term, academic_year, amount_due, amount_paid, balance, due_date, status)
SELECT s.student_id, 1, '2026', fs.amount,
  CASE s.admission_no
    WHEN 'SKA-2026-00001' THEN fs.amount
    WHEN 'SKA-2026-00006' THEN 8000
    WHEN 'SKA-2026-00010' THEN 12000
    WHEN 'SKA-2026-00012' THEN 5000
    ELSE 0
  END AS amount_paid,
  fs.amount - CASE s.admission_no
    WHEN 'SKA-2026-00001' THEN fs.amount
    WHEN 'SKA-2026-00006' THEN 8000
    WHEN 'SKA-2026-00010' THEN 12000
    WHEN 'SKA-2026-00012' THEN 5000
    ELSE 0
  END AS balance,
  CASE WHEN s.admission_no IN ('SKA-2026-00003','SKA-2026-00009','SKA-2026-00013') THEN '2026-01-31' ELSE '2026-03-15' END,
  CASE
    WHEN s.admission_no = 'SKA-2026-00001' THEN 'Paid'
    WHEN s.admission_no IN ('SKA-2026-00006','SKA-2026-00010','SKA-2026-00012') THEN 'Partial'
    ELSE 'Unpaid'
  END
FROM students s
JOIN fee_structures fs ON fs.class_id = s.class_id AND fs.term = 1 AND fs.academic_year = '2026';

INSERT IGNORE INTO payments (invoice_id, amount_paid, payment_date, payment_method, receipt_no, recorded_by)
SELECT fi.invoice_id, p.amount_paid, p.payment_date, p.payment_method, p.receipt_no, u.user_id
FROM (
  SELECT 'SKA-2026-00001' admission_no, 12000 amount_paid, DATE('2026-01-15') payment_date, 'Mobile Money' payment_method, 'SKA-2026-T1-00001' receipt_no
  UNION ALL SELECT 'SKA-2026-00006', 8000, DATE('2026-01-22'), 'Bank Transfer', 'SKA-2026-T1-00002'
  UNION ALL SELECT 'SKA-2026-00010', 12000, DATE('2026-01-25'), 'Mobile Money', 'SKA-2026-T1-00003'
  UNION ALL SELECT 'SKA-2026-00012', 5000, DATE('2026-02-02'), 'Cash', 'SKA-2026-T1-00004'
) p
JOIN students s ON s.admission_no = p.admission_no
JOIN fee_invoices fi ON fi.student_id = s.student_id AND fi.term = 1 AND fi.academic_year = '2026'
JOIN users u ON u.email = 'admin@ska.ac.ke';

INSERT IGNORE INTO notifications (recipient_id, subject, message, type, sent_at, status)
SELECT gu.user_id, 'Absence alert', CONCAT(s.full_name, ' was marked absent without excuse on 2026-02-05.'), 'Absence', '2026-02-05 16:30:00', 'Sent'
FROM students s JOIN users gu ON gu.user_id = s.guardian_id
WHERE s.admission_no IN ('SKA-2026-00003','SKA-2026-00010');

INSERT IGNORE INTO notifications (recipient_id, subject, message, type, sent_at, status)
SELECT gu.user_id, 'Payment received', CONCAT('Payment received for ', s.full_name, '. Receipt ', p.receipt_no, '.'), 'Payment', CONCAT(p.payment_date, ' 10:00:00'), 'Sent'
FROM payments p
JOIN fee_invoices fi ON fi.invoice_id = p.invoice_id
JOIN students s ON s.student_id = fi.student_id
JOIN users gu ON gu.user_id = s.guardian_id;

INSERT IGNORE INTO notifications (recipient_id, subject, message, type, sent_at, status)
SELECT gu.user_id, 'Fee arrears', CONCAT('Term 1 2026 fee balance for ', s.full_name, ' is KES ', FORMAT(fi.balance, 2), '.'), 'Fee Arrears', '2026-02-10 09:00:00', 'Pending'
FROM fee_invoices fi
JOIN students s ON s.student_id = fi.student_id
JOIN users gu ON gu.user_id = s.guardian_id
WHERE fi.balance > 0 AND fi.due_date < '2026-02-10';

INSERT IGNORE INTO disciplinary_records (student_id, incident_date, description, action_taken, recorded_by)
SELECT s.student_id, '2026-02-07', 'Minor lateness after lunch break.', 'Guidance conversation and parent note recorded.', u.user_id
FROM students s
JOIN users u ON u.email = 'teacher@ska.ac.ke'
WHERE s.admission_no = 'SKA-2026-00012';
