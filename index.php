<?php
require_once __DIR__ . '/helpers/auth_helper.php';
ensure_session_started();

if (empty($_SESSION['user_id'])) {
    redirect('login.php');
}

redirect(dashboard_for_role($_SESSION['role'] ?? null));
