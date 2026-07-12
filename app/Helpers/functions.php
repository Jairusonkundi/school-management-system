<?php
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $config = require __DIR__ . '/../../config/config.php';
    $baseUrl = trim((string)($config['base_url'] ?? ''));
    if ($baseUrl === '') {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseUrl = rtrim(str_replace('/index.php', '', $script), '/');
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(\App\Core\Auth::csrfToken()) . '">';
}

function money($amount): string
{
    return 'KES ' . number_format((float)$amount, 2);
}

function cbc_level_label(?string $code): string
{
    return match ($code) {
        'EE' => 'EE - Exceeding Expectation',
        'ME' => 'ME - Meeting Expectation',
        'AE' => 'AE - Approaching Expectation',
        'BE' => 'BE - Below Expectation',
        default => (string)$code,
    };
}
