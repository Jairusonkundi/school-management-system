# Sunshine Kaseveni Academy School Management System

PHP/MySQL school management system for student records, attendance, examinations, fees, parent communication, self-service records, and management reports.

## Setup

1. Copy this folder to `C:\xampp\htdocs\school-management-system`.
2. Run `composer install` from the project root if `vendor/` is missing.
3. Import `database/schema.sql` in phpMyAdmin or MySQL. It creates the `sunshine_sms` database, schema, seed classes, seed subjects, and seed admin.
4. Edit `config/config.php` for local MySQL credentials, `base_url`, and SMTP credentials.
5. Start Apache and MySQL in XAMPP.
6. Open `http://localhost/school-management-system/public`.
7. Login with `admin@sunshine.local` / `password`, then change that password.

## Tests

Run these from the project root:

```bash
composer test
```

For a full PHP syntax pass:

```powershell
Get-ChildItem -Path app,public,views,tests -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Requirement Checklist

- FR1 Admin student registration and sequential admission numbers: implemented in `database/schema.sql`, `app/Models/Student.php`, `app/Controllers/StudentController.php`, `views/students/create.php`.
- FR2 Teacher attendance recording/history by class/date: implemented in `app/Models/Attendance.php`, `app/Controllers/AttendanceController.php`, `views/attendance/mark.php`.
- FR3 Marks, grade, class average, and ranking: implemented in `app/Models/Academic.php`, `app/Controllers/AcademicController.php`, `views/academics/grades.php`.
- FR4 Invoices, payments, balance updates, and overpayment rejection: implemented in `app/Models/Finance.php`, `app/Controllers/FinanceController.php`, `views/finance/invoices.php`, `views/finance/payments.php`.
- FR5 Payment receipt email: implemented in `app/Controllers/FinanceController.php`, `app/Services/NotificationService.php`.
- FR6 Unexcused absence email: implemented in `app/Controllers/AttendanceController.php`, `app/Services/NotificationService.php`.
- FR7 Parent/student read-only portal: implemented in `app/Controllers/PortalController.php`, `views/portal/index.php`, with scoped models in `app/Models/Attendance.php`, `app/Models/Academic.php`, `app/Models/Finance.php`.
- FR8 Admin reports: implemented in `app/Controllers/ReportController.php`, `views/reports/index.php`, with report queries in `app/Models/Student.php`, `app/Models/Attendance.php`, `app/Models/Academic.php`, `app/Models/Finance.php`.
- FR9 RBAC on screens/actions: implemented in `app/Core/Auth.php`, `public/index.php`, controllers, and model ownership checks.
- FR10 User create/update/deactivate: implemented in `app/Models/User.php`, `app/Controllers/UserController.php`, `views/users/index.php`.

## Non-Functional Checklist

- NFR1 Security: password hashes via `password_hash`, prepared PDO statements, CSRF tokens, HTTP-only/strict sessions, secure cookies on HTTPS, POST-only logout, active-user login checks, security headers, server-side RBAC.
- NFR2 Performance: indexed schema for login, role lookups, attendance filters, result rankings, invoices, payments, and notifications.
- NFR3 Usability: simple role-based navigation, focused forms, flash messages, responsive tables.
- NFR4 Reliability: controller-level error handling, validation before writes, transactional admission/payment/invoice operations, SMTP failures logged and recorded.
- NFR5 Maintainability: MVC-style split across `views/`, `app/Controllers/`, `app/Models/`, `app/Services/`, and `database/schema.sql`.
- NFR6 Scalability: normalized schema with scoped queries and indexes suitable for 5,000+ student records.
- NFR7 Compatibility: HTML5, CSS3, vanilla JavaScript, no frontend framework dependency; targets current Chrome, Firefox, and Edge.

## Production Notes

- Serve over HTTPS in production.
- Configure real SMTP credentials before relying on parent email delivery.
- Restrict database credentials and keep backups outside the web root.
- Change the seed admin password immediately after installation.
