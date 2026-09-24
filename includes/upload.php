<?php
declare(strict_types=1);

function upload_product_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || (int) ($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('آپلود تصویر ناموفق بود یا حجم فایل بیش از حد مجاز است.');
    }

    $tmp = (string) $file['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($tmp) === false) {
        throw new RuntimeException('فقط تصویر واقعی JPG، PNG یا WEBP پذیرفته می‌شود.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('پوشه تصاویر قابل نوشتن نیست.');
    }
    $destination = $dir . '/' . $name;

    if (extension_loaded('gd')) {
        $info = getimagesize($tmp);
        [$width, $height] = [$info[0], $info[1]];
        $scale = min(1, IMAGE_MAX_WIDTH / max($width, 1));
        $newW = max(1, (int) round($width * $scale));
        $newH = max(1, (int) round($height * $scale));
        $create = match ($mime) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
        };
        $src = @$create($tmp);
        if ($src !== false) {
            $canvas = imagecreatetruecolor($newW, $newH);
            if ($mime !== 'image/jpeg') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }
            imagecopyresampled($canvas, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
            $saved = match ($mime) {
                'image/jpeg' => imagejpeg($canvas, $destination, 82),
                'image/png' => imagepng($canvas, $destination, 7),
                'image/webp' => imagewebp($canvas, $destination, 82),
            };
            imagedestroy($src);
            imagedestroy($canvas);
            if ($saved) {
                return $name;
            }
        }
    }

    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('ذخیره تصویر انجام نشد.');
    }
    return $name;
}

