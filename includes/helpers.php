<?php
// ============================================================
//  DisBasura — Shared Helper Functions (Updated v2)
// ============================================================

// db.php defines get_db(), now_pht(), fmt_date() — require_once is safe even
// if middleware already included it; it will NOT run twice.
require_once __DIR__ . '/../config/db.php';

function get_sitios(): array {
    return get_db()->query("SELECT name FROM sitios ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
}

function get_unread_count(int $user_id): int {
    $s = get_db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $s->execute([$user_id]);
    return (int)$s->fetchColumn();
}

function notify_user(PDO $db, int $user_id, string $message): void {
    $db->prepare("INSERT INTO notifications (user_id,message,created_at) VALUES (?,?,?)")
       ->execute([$user_id, $message, now_pht()]);
}

function notify_all_sitio(PDO $db, string $sitio, string $message): void {
    $stmt = $db->prepare("SELECT id FROM users WHERE sitio=? AND role IN ('resident','leader')");
    $stmt->execute([$sitio]);
    $ins = $db->prepare("INSERT INTO notifications (user_id,message,created_at) VALUES (?,?,?)");
    foreach ($stmt->fetchAll() as $r) {
        $ins->execute([$r['id'], $message, now_pht()]);
    }
    // SMS notify
    if (file_exists(__DIR__.'/../config/sms.php')) {
        require_once __DIR__.'/../config/sms.php';
        send_sms_to_sitio($sitio, strip_tags($message));
    }
}

function notify_all_admins(PDO $db, string $message): void {
    $admins = $db->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
    $ins = $db->prepare("INSERT INTO notifications (user_id,message,created_at) VALUES (?,?,?)");
    foreach ($admins as $a) $ins->execute([$a['id'], $message, now_pht()]);
}

// ── Activity Log ─────────────────────────────────────────────
function log_activity(int $admin_id, string $action, string $details = ''): void {
    try {
        get_db()->prepare("INSERT INTO activity_log (admin_id,action,details,created_at) VALUES (?,?,?,?)")
                ->execute([$admin_id, $action, $details, now_pht()]);
    } catch (Exception $e) {}
}

// ── Auto-Flag Missed Collections ─────────────────────────────
function auto_flag_missed(PDO $db): void {
    $now = now_pht();
    $stmt = $db->prepare("
        UPDATE schedules
        SET status='unverified'
        WHERE status IN ('scheduled','assigned')
        AND scheduled_at < ?
    ");
    $stmt->execute([$now]);
    $flagged = $stmt->rowCount();
    if ($flagged > 0) {
        notify_all_admins($db, "⚠️ $flagged schedule(s) passed their time without being marked as completed. Please verify.");
    }
}

// ── User Preferences ─────────────────────────────────────────
function get_preferences(int $user_id): array {
    try {
        $s = get_db()->prepare("SELECT * FROM user_preferences WHERE user_id=?");
        $s->execute([$user_id]);
        return $s->fetch() ?: ['dark_mode' => 0, 'language' => 'en'];
    } catch (Exception $e) {
        return ['dark_mode' => 0, 'language' => 'en'];
    }
}

function save_preferences(int $user_id, int $dark_mode, string $language): void {
    get_db()->prepare("INSERT INTO user_preferences (user_id,dark_mode,language) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE dark_mode=VALUES(dark_mode), language=VALUES(language), updated_at=NOW()")
        ->execute([$user_id, $dark_mode, $language]);
}

// ── Language ─────────────────────────────────────────────────
function get_lang(int $user_id = 0): array {
    $lang = 'en';
    if ($user_id) {
        $p = get_preferences($user_id);
        $lang = $p['language'] ?? 'en';
    }
    $file = __DIR__.'/../lang/'.$lang.'.php';
    if (!file_exists($file)) $file = __DIR__.'/../lang/en.php';
    return require $file;
}

// ── File Upload ───────────────────────────────────────────────
function save_upload(array $file, string $prefix): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['png','jpg','jpeg','webp','gif'])) return null;
    $fname = $prefix.'_'.time().'.'.$ext;
    $dest  = __DIR__.'/../uploads/'.$fname;
    return move_uploaded_file($file['tmp_name'], $dest) ? $fname : null;
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
