<?php
require_once __DIR__ . '/auth_helper.php';

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_input(): string
{
    ensure_session_started();
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf_token']) . '">';
}

function current_academic_year(): string
{
    return date('Y');
}

function current_term(): int
{
    $month = (int)date('n');
    if ($month <= 4) {
        return 1;
    }
    if ($month <= 8) {
        return 2;
    }
    return 3;
}

function money_fmt($amount): string
{
    return 'KES ' . number_format((float)$amount, 2);
}

function fetch_classes(PDO $pdo, ?string $gradeName = null, ?int $teacherId = null, ?int $levelId = null): array
{
    $sql = 'SELECT c.*, e.level_name, e.level_order, u.full_name AS teacher_name
            FROM classes c
            LEFT JOIN education_levels e ON e.level_id = c.level_id
            LEFT JOIN users u ON u.user_id = c.teacher_id
            WHERE 1=1';
    $params = [];
    if ($gradeName !== null && $gradeName !== '') {
        $sql .= ' AND c.grade_name = ?';
        $params[] = $gradeName;
    }
    if ($levelId !== null && $levelId > 0) {
        $sql .= ' AND c.level_id = ?';
        $params[] = $levelId;
    }
    if ($teacherId !== null) {
        $sql .= ' AND c.teacher_id = ?';
        $params[] = $teacherId;
    }
    $sql .= ' ORDER BY e.level_order, FIELD(c.grade_name, "PP1","PP2","Grade 1","Grade 2","Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8","Grade 9"), c.stream_name, c.class_name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function class_label(array $class): string
{
    $stream = trim((string)($class['stream_name'] ?? ''));
    return trim((string)($class['grade_name'] ?? $class['grade_level'] ?? $class['class_name'] ?? '') . ($stream !== '' ? ' ' . $stream : ''));
}

function grade_levels(): array
{
    return ['PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9'];
}

function education_levels(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM education_levels ORDER BY level_order');
    return $stmt->fetchAll();
}

function grade_level_name(string $gradeName): string
{
    return match ($gradeName) {
        'PP1', 'PP2' => 'Early Years Education',
        'Grade 1', 'Grade 2', 'Grade 3' => 'Lower Primary',
        'Grade 4', 'Grade 5', 'Grade 6' => 'Upper Primary',
        default => 'Junior Secondary School',
    };
}

function level_id_for_grade(PDO $pdo, string $gradeName): int
{
    $stmt = $pdo->prepare('SELECT level_id FROM education_levels WHERE level_name = ?');
    $stmt->execute([grade_level_name($gradeName)]);
    return (int)$stmt->fetchColumn();
}

function cbc_grade_label(?string $code): string
{
    return match ($code) {
        'EE' => 'EE - Exceeding Expectation',
        'ME' => 'ME - Meeting Expectation',
        'AE' => 'AE - Approaching Expectation',
        'BE' => 'BE - Below Expectation',
        default => '',
    };
}

function cbc_badge_class(?string $code): string
{
    return match ($code) {
        'EE' => 'badge-cbc-ee',
        'ME' => 'badge-cbc-me',
        'AE' => 'badge-cbc-ae',
        'BE' => 'badge-cbc-be',
        default => 'text-bg-secondary',
    };
}

function selected_attr($left, $right): string
{
    return (string)$left === (string)$right ? ' selected' : '';
}

function checked_attr($left, $right): string
{
    return (string)$left === (string)$right ? ' checked' : '';
}
