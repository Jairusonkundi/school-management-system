<?php
function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function app_base_path(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $parts = explode('/', trim($script, '/'));
    return isset($parts[0]) && $parts[0] !== '' ? '/' . $parts[0] : '';
}

function dashboard_for_role(?string $role): string
{
    $base = app_base_path();
    return match ($role) {
        'admin' => $base . '/dashboards/admin.php',
        'teacher' => $base . '/dashboards/teacher.php',
        'parent' => $base . '/dashboards/parent.php',
        'student' => $base . '/dashboards/student.php',
        default => $base . '/login.php',
    };
}

function require_role(string $role): void
{
    require_any_role([$role]);
}

function require_any_role(array $roles): void
{
    ensure_session_started();
    if (empty($_SESSION['user_id'])) {
        redirect(app_base_path() . '/login.php');
    }
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        redirect(dashboard_for_role($_SESSION['role'] ?? null));
    }
}

function get_current_user_id(): int
{
    ensure_session_started();
    return (int)($_SESSION['user_id'] ?? 0);
}

function get_current_role(): string
{
    ensure_session_started();
    return (string)($_SESSION['role'] ?? '');
}

function verify_csrf(): void
{
    ensure_session_started();
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid session token. Please go back and try again.');
    }
}
