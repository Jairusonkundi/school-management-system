<?php
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $config = require __DIR__ . '/../../config/config.php';
    return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
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
