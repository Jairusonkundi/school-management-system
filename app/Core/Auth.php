<?php
namespace App\Core;

class Auth
{
    private const ROLES = ['admin', 'teacher', 'parent', 'student'];

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', '1');
            }
            session_start();
        }
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function requireRole(array $roles): void
    {
        self::start();
        if (!self::check() || !in_array($_SESSION['user']['role'], $roles, true)) {
            self::flash('error', 'You do not have permission to access that page.');
            header('Location: index.php?route=login');
            exit;
        }
    }

    public static function requireLogin(): void
    {
        self::start();
        if (!self::check()) {
            self::flash('error', 'Please log in first.');
            header('Location: index.php?route=login');
            exit;
        }
    }

    public static function hasRole(array $roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    public static function roles(): array
    {
        return self::ROLES;
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'linked_student_id' => $user['linked_student_id'] ?? null,
        ];
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): void
    {
        self::start();
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            self::flash('error', 'Your session expired. Please try again.');
            header('Location: index.php?route=dashboard');
            exit;
        }
    }

    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }

    public static function consumeFlash(): array
    {
        self::start();
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}
