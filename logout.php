<?php
require_once __DIR__ . '/config/session.php';

// Figure out who is logging out
$is_admin     = isset($_SESSION['admin_id']);
$is_collector = isset($_SESSION['collector_id']);
$is_resident  = isset($_SESSION['user_id']);

if ($is_admin) {
    // Only clear admin keys — resident/collector sessions untouched
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_name'],
        $_SESSION['admin_user'],
        $_SESSION['admin_role']
    );
    header('Location: /disbasura/admin/login.php'); exit;
}

if ($is_collector) {
    unset(
        $_SESSION['collector_id'],
        $_SESSION['collector_name']
    );
    header('Location: /disbasura/collector/login.php'); exit;
}

if ($is_resident) {
    unset(
        $_SESSION['user_id'],
        $_SESSION['full_name'],
        $_SESSION['username'],
        $_SESSION['role'],
        $_SESSION['sitio']
    );
    header('Location: /disbasura/login.php'); exit;
}

// Fallback
session_destroy();
header('Location: /disbasura/login.php'); exit;
