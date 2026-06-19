# Sunshine Kaseveni Academy School Management System

A XAMPP-compatible PHP/MySQL School Management System for a Kenyan private school operating Junior School CBC and Secondary School streams.

## Architecture

The project uses a 3-tier, MVC-style architecture:

1. **Presentation layer**: HTML5, CSS3, vanilla JavaScript in `views/` and `public/assets/`.
2. **Application layer**: OOP PHP controllers, models, services and RBAC helpers in `app/`.
3. **Data layer**: Normalized MySQL schema in `database/schema.sql`.

## Folder Structure

```text
app/Core              Framework-like database, auth and controller base classes
app/Controllers       Request handlers for auth, dashboards, academics, finance, attendance
app/Models            OOP data access classes using prepared PDO statements
app/Services          PHPMailer notification service
config                Database, base URL and SMTP configuration
database              MySQL schema and seed data
public                XAMPP document-root entry point and static assets
views                 Frontend pages grouped by module and role
```

## Key Modules

- Authentication and role-based dashboards for Admin, Teacher, Student and Parent.
- Student admissions with collision-free admission numbers and Junior/Secondary separation.
- CBC learning-area assessments with Kenyan CBC descriptors.
- Secondary CAT and end-term grading with automatic grade letter computation.
- Daily attendance marking and dashboard analytics.
- Fee accounts, payment recording and balance summaries.
- PHPMailer SMTP service for absence, fee, performance and announcement emails.

## XAMPP Setup

1. Copy the project to `xampp/htdocs/school-management-system`.
2. Run `composer install` in the project root to install PHPMailer and generate autoload files.
3. Open phpMyAdmin and import `database/schema.sql`.
4. Edit `config/config.php` for your MySQL and SMTP credentials.
5. Visit `http://localhost/school-management-system/public`.
6. Login with `admin@sunshine.local` and password `password`.

## Production Notes

- Change the seed admin password immediately after installation.
- Configure a real SMTP mailbox before enabling automated parent notifications.
- Use HTTPS, secure cookies and off-server backups in production.
