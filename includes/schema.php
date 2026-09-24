<?php
declare(strict_types=1);

function ensure_schema(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    $columns = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products'")->fetchAll(PDO::FETCH_COLUMN);
    $add = [
        'ingredients' => "ALTER TABLE products ADD ingredients VARCHAR(700) NULL AFTER description",
        'is_featured' => "ALTER TABLE products ADD is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER image",
        'badge_vegan' => "ALTER TABLE products ADD badge_vegan TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured",
        'badge_sugar_free' => "ALTER TABLE products ADD badge_sugar_free TINYINT(1) NOT NULL DEFAULT 0 AFTER badge_vegan",
        'badge_diet' => "ALTER TABLE products ADD badge_diet TINYINT(1) NOT NULL DEFAULT 0 AFTER badge_sugar_free",
        'caffeine_level' => "ALTER TABLE products ADD caffeine_level TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER badge_diet",
    ];
    foreach ($add as $name => $sql) if (!in_array($name, $columns, true)) $pdo->exec($sql);
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics_daily (
      event_date DATE NOT NULL, event_type ENUM('view','product') NOT NULL,
      product_id INT UNSIGNED NOT NULL DEFAULT 0, event_count INT UNSIGNED NOT NULL DEFAULT 0,
      PRIMARY KEY(event_date,event_type,product_id), INDEX idx_analytics_date(event_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
