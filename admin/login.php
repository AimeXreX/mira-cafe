<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (admin_logged_in()) redirect('admin/');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $key = client_key();
    if (login_is_locked($key)) {
        $error = 'تلاش‌های ناموفق زیاد بوده است. ۱۵ دقیقه بعد دوباره امتحان کنید.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $stmt = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            login_clear($key);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $update = db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
            $update->execute([$admin['id']]);
            redirect('admin/');
        }
        login_failure($key);
        usleep(400000);
        $error = 'نام کاربری یا رمز عبور درست نیست.';
    }
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>ورود مدیریت | میرا کافه</title><link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>"></head><body class="auth-page"><main class="auth-card"><div class="auth-logo"><b>MIRA</b><span>CAFE</span></div><p class="kicker">پنل مدیریت</p><h1>خوش آمدید</h1><p class="muted">برای مدیریت منوی کافه وارد شوید.</p><?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_field() ?><label>نام کاربری<input name="username" autocomplete="username" required autofocus></label><label>رمز عبور<input type="password" name="password" autocomplete="current-password" required></label><button class="btn primary full" type="submit">ورود امن</button></form><a class="back-link" href="<?= url('') ?>">بازگشت به منو</a></main></body></html>

