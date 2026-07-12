# Sunshine Kaseveni Academy School Management System

SKA-SMS is a secure web-based school management system for Sunshine Kaseveni Academy, a Kenyan CBC school serving PP1 through Grade 9. It supports Early Years Education, Lower Primary, Upper Primary, and Junior Secondary School records, attendance, CBC assessment results, fees, parent communication, notifications, and reporting.

## Tech Stack

- PHP 8.1, plain PHP with PDO
- MySQL 8.0 compatible schema
- Bootstrap 5.3 from CDN
- PHPMailer 6.6 via Composer
- HTML5, CSS3, and lightweight JavaScript

## Setup

1. Copy or clone the project into XAMPP `htdocs`. This checkout is currently at `C:\xampp\htdocs\school-management-system`, so the local URL uses `/school-management-system`.
2. Install Composer dependencies if `vendor/` is missing:
   ```powershell
   composer install
   ```
3. Start both Apache and MySQL in the XAMPP Control Panel.
4. Import the database before opening the app:
   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root -P 3307 -e "CREATE DATABASE IF NOT EXISTS ska_sms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   Get-Content database\ska_sms_db.sql | C:\xampp\mysql\bin\mysql.exe -u root -P 3307 ska_sms_db
   ```
   Adjust the port if your XAMPP MySQL uses `3306`.

   The only database this project should use is `ska_sms_db`. Do not keep duplicate test databases for this project; if you create one for verification, delete it immediately after the check.
5. Edit [config/db.php](config/db.php) for your MySQL host, port, database, user, and password.
6. Edit [config/mail.php](config/mail.php) with real Gmail SMTP app-password credentials before relying on email delivery.
7. Open the app in the browser:
   `http://localhost/school-management-system/login.php`

Apache and MySQL must both be running before this URL will work, and the `database/ska_sms_db.sql` import must complete first.

## Local Testing Accounts

The database seeds this local-testing-only administrator account:

- Email: `admin@ska.ac.ke`
- Password: `password`

Change this password immediately after first login.

Additional seeded demo accounts use the same local-testing-only password, `password`:

- Teacher: `teacher@ska.ac.ke`
- Parent: `parent@ska.ac.ke`
- Student: `student@ska.ac.ke`

## Modules

- Auth and RBAC: [login.php](login.php), [logout.php](logout.php), [helpers/auth_helper.php](helpers/auth_helper.php), and [modules/auth](modules/auth)
- Student Records: [modules/students](modules/students), including CBC PP1-Grade 9 registration, admission numbers, profiles, editing, and discipline records
- Academic Management: [modules/academic](modules/academic), including attendance, subjects, marks entry, mark sheets, and report cards
- Level-Aware CBC Assessment:
  - Early Years Education and Lower Primary use descriptive competency-level assessment per learning area. Teachers select EE, ME, AE, or BE directly and may add a comment. No numeric marks, averages, or ranks are shown.
  - Upper Primary and Junior Secondary School use numeric marks from 0-100. [helpers/grade_helper.php](helpers/grade_helper.php) calculates the CBC level automatically:
  - EE: 80-100, Exceeding Expectation
  - ME: 50-79, Meeting Expectation
  - AE: 30-49, Approaching Expectation
  - BE: 0-29, Below Expectation
- Financial Management: [modules/finance](modules/finance), including fee structures, invoices, payments, receipts, arrears alerts, and reports
- Notifications: [modules/notifications](modules/notifications) and [helpers/notify_helper.php](helpers/notify_helper.php)
- Dashboards: [dashboards](dashboards) for admin, teacher, parent, and student roles
- Shared Layout: [includes](includes), [assets/css/style.css](assets/css/style.css), and [assets/js/app.js](assets/js/app.js)

## Seeded Learning Areas

- Early Years Education: Language Activities; Mathematical Activities; Environmental Activities; Psychomotor and Creative Activities; Religious Education Activities.
- Lower Primary: English Language Activities; Kiswahili / Kenya Sign Language Activities; Mathematical Activities; Environmental Activities; Hygiene and Nutrition Activities; Religious Education Activities; Movement and Creative Activities.
- Upper Primary: English; Kiswahili / Kenya Sign Language; Mathematics; Science and Technology; Agriculture; Social Studies; Religious Education; Creative Arts; Physical Education and Sports.
- Junior Secondary School: English; Kiswahili / Kenya Sign Language; Mathematics; Integrated Science; Health Education; Pre-Technical Studies; Social Studies; Religious Education; Business Studies; Agriculture and Nutrition; Life Skills Education; Physical Education and Sports; Creative Arts; Foreign or Indigenous Language.

## Brand Palette

The interface uses a green primary school palette with warm accents:

- Primary green: `#1F7A4D`
- Dark green: `#145C38`
- Light green: `#E7F5EC`
- Amber accent: `#F5A623`
- Coral accent: `#FF6B4A`

Use green for primary actions and navigation, amber for warm highlights, and coral sparingly for warnings or below-expectation states.

## Known Limitations and Next Steps

- Email delivery depends on valid SMTP credentials in [config/mail.php](config/mail.php).
- The schema keeps legacy `section` columns nullable to preserve existing data during migration, but new screens use CBC education levels and grade names.
- Stream names are optional; duplicate legacy Grade 7-9 rows with no linked records are consolidated during migration.
- Religious Education variants are seeded separately so the Administrator can keep only the variants the school offers.
