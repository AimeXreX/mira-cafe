<?php
declare(strict_types=1);

function admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']) && is_int($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('admin/login.php');
    }
}

function client_key(): string
{
    return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

function login_status(string $key): array
{
    $stmt = db()->prepare('SELECT attempts, locked_until FROM login_attempts WHERE client_key = ?');
    $stmt->execute([$key]);
    return $stmt->fetch() ?: ['attempts' => 0, 'locked_until' => null];
}

function login_is_locked(string $key): bool
{
    $status = login_status($key);
    return !empty($status['locked_until']) && strtotime($status['locked_until']) > time();
}

function login_failure(string $key): void
{
    $status = login_status($key);
    $attempts = (int) $status['attempts'] + 1;
    $lockedUntil = $attempts >= LOGIN_MAX_ATTEMPTS
        ? date('Y-m-d H:i:s', time() + LOGIN_LOCK_SECONDS)
        : null;
    $stmt = db()->prepare(
        'INSERT INTO login_attempts (client_key, attempts, locked_until, updated_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), locked_until = VALUES(locked_until), updated_at = NOW()'
    );
    $stmt->execute([$key, $attempts, $lockedUntil]);
}

function login_clear(string $key): void
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE client_key = ?');
    $stmt->execute([$key]);
}

