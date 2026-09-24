<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function fa_digits(string|int $value): string
{
    return strtr((string) $value, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
}

function format_price(?int $price): string
{
    if ($price === null) {
        return 'استعلام قیمت';
    }
    return fa_digits(number_format($price)) . ' تومان';
}

function settings_all(): array
{
    try {
        $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    } catch (Throwable $e) {
        error_log('Settings error: ' . $e->getMessage());
        return [];
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function old(string $key, mixed $default = ''): string
{
    return e((string) ($_POST[$key] ?? $default));
}

function request_bool(string $key): int
{
    return isset($_POST[$key]) ? 1 : 0;
}

function category_icon(string $slug): string
{
    $paths = [
        'coffee-bar' => '<path d="M5 8h11v6a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5V8Z"/><path d="M16 10h1.5a3 3 0 0 1 0 6H16M8 4v2m4-2v2"/>',
        'hot-drinks' => '<path d="M6 9h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5V9Z"/><path d="M17 11h1a2.5 2.5 0 0 1 0 5h-1M9 3c0 2 2 2 2 4m3-4c0 2 2 2 2 4"/>',
        'shakes' => '<path d="M7 7h10l-1 13H8L7 7Zm-1-3h12M14 4l2-2"/><path d="M9 11h6"/>',
        'smoothies' => '<path d="M7 8h10l-1 12H8L7 8Zm-1-3h12M12 5l3-3"/><path d="M9 12c2-2 4 2 6 0"/>',
        'mocktails' => '<path d="M5 4h14l-6 7v8m-4 1h8M8 7h8"/><path d="M17 3c2 0 3 1 3 3"/>',
        'cold-bar' => '<path d="M12 3v18M5 7l14 10M19 7 5 17M8 5l4 3 4-3M8 19l4-3 4 3"/>',
        'cake-dessert' => '<path d="M5 11h14v9H5v-9Zm1-3h12l1 3H5l1-3Z"/><path d="M8 8c0-2 2-2 2-4m4 4c0-2 2-2 2-4"/>',
        'snacks' => '<path d="M5 12h14v7H5v-7Zm1-3c3-4 9-4 12 0H6Z"/><path d="M5 12c3-2 4 2 7 0s4 2 7 0"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($paths[$slug] ?? '<circle cx="12" cy="12" r="8"/>') . '</svg>';
}

function delete_image(?string $filename): void
{
    if (!$filename || !preg_match('/^[a-f0-9]{32}\.(?:jpe?g|png|webp)$/i', $filename)) {
        return;
    }
    $path = dirname(__DIR__) . '/uploads/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}
