<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM users WHERE email=? AND role IN ('resident','leader')");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) { $error = 'No resident account found with that email.'; }
    else {
        $code    = str_pad((string)rand(0,999999), 6, '0', STR_PAD_LEFT);
        $expires = (new DateTime('now', new DateTimeZone('Asia/Manila')))->modify('+10 minutes')->format('Y-m-d H:i:s');
        $db->prepare("DELETE FROM reset_codes WHERE email=?")->execute([$email]);
        $db->prepare("INSERT INTO reset_codes (email,code,expires_at) VALUES (?,?,?)")->execute([$email,$code,$expires]);
        if (send_reset_email($email, $code)) {
            header('Location: /disbasura/verify-code.php?email='.urlencode($email)); exit;
        }
        $error = 'Failed to send email. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Forgot Password — DisBasura</title>
<link rel="stylesheet" href="/disbasura/assets/css/base.css"/>
<link rel="stylesheet" href="/disbasura/assets/css/auth.css"/>
</head>
<body>
<div class="auth-bg">
  <div class="auth-logo"><h1>DisBasura</h1><p>Password Recovery</p></div>
  <div class="auth-card">
    <h2>Forgot Password?</h2>
    <p class="sub">Enter your registered email and we'll send a 6-digit reset code.</p>
    <?php if($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="you@email.com" required/></div>
      <button type="submit" class="btn-primary">Send Reset Code</button>
    </form>
    <div class="auth-footer"><a href="/disbasura/login.php">← Back to Sign In</a></div>
  </div>
</div>
</body>
</html>
