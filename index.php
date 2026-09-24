<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$settings = settings_all();
$showUnavailable = ($settings['show_unavailable'] ?? '1') === '1';

try {
    $categories = db()->query('SELECT id, name_fa, name_en, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    $sql = 'SELECT p.*, c.name_fa AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON c.id = p.category_id
            WHERE p.is_visible = 1 AND c.is_active = 1';
    if (!$showUnavailable) {
        $sql .= ' AND p.is_available = 1';
    }
    $sql .= ' ORDER BY c.sort_order, c.id, p.sort_order, p.id';
    $products = db()->query($sql)->fetchAll();
} catch (Throwable $e) {
    error_log('Menu query error: ' . $e->getMessage());
    $categories = $products = [];
}

$byCategory = [];
foreach ($products as $product) {
    $byCategory[(int) $product['category_id']][] = $product;
}
$cafeName = $settings['cafe_name'] ?? 'میرا کافه';
$pageTitle = $settings['page_title'] ?? 'منوی میرا کافه';
$description = $settings['meta_description'] ?? 'منوی دیجیتال میرا کافه؛ نوشیدنی‌های تازه، کیک و غذای دوست‌داشتنی.';
$featured = array_values(array_filter($products, fn($p) => !empty($p['is_featured']) && !empty($p['is_available'])));
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <meta name="theme-color" content="#F5F1EB">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="fa_IR">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= url('assets/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=2.0.0">
  <script src="<?= url('assets/js/app.js') ?>?v=1.0.0" defer></script>
</head>
<body>
  <a class="skip-link" href="#menu">رفتن به منو</a>
  <header class="hero">
    <div class="hero__inner">
      <div class="hero__top">
        <img class="hero__logo" src="<?= url('assets/images/logo.svg') ?>" alt="لوگوی میرا کافه" width="180" height="70">
        <span class="hero__edition" lang="en" dir="ltr">DIGITAL MENU · <?= date('Y') ?></span>
      </div>
      <div class="hero__content">
        <div class="hero__copy">
          <p class="eyebrow" lang="en" dir="ltr">GOOD DRINKS • GOOD VIBES • BETTER DAYS</p>
          <h1>هر جرعه،<br><em>یک حالِ خوب.</em></h1>
          <p>منوی تازه‌ی <?= e($cafeName) ?></p>
        </div>
        <figure class="hero__visual">
          <img src="<?= url('uploads/a3f5d9c80b2741e6aa91d8f0435c72be.webp') ?>" alt="لاته و کیک پسته در میرا کافه" width="900" height="900">
          <figcaption><span>پیشنهاد میرا</span><b>لاته و کیک</b></figcaption>
        </figure>
      </div>
      <div class="hero__rule"><span></span><p>زندگی اینجا خوش‌طعم‌تره</p><span></span></div>
    </div>
  </header>

  <main id="menu">
    <section class="menu-tools" aria-label="ابزارهای منو">
      <div class="search-wrap">
        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m21 21-4.4-4.4m2.4-5.1a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
        <label class="sr-only" for="menu-search">جستجو در منو</label>
        <input id="menu-search" type="search" inputmode="search" placeholder="مثلاً لاته یا کیک…" autocomplete="off">
        <button class="search-clear" type="button" aria-label="پاک کردن جستجو" hidden>×</button>
      </div>
      <nav class="chips" aria-label="دسته‌بندی‌های منو">
        <button class="chip is-active" type="button" data-filter="all" aria-pressed="true">همه</button>
        <?php foreach ($categories as $category): ?>
          <?php if (!empty($byCategory[(int) $category['id']])): ?>
            <button class="chip" type="button" data-filter="<?= e($category['slug']) ?>" aria-pressed="false"><?= e($category['name_fa']) ?></button>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
    </section>

    <div class="menu-shell">
      <div class="menu-status" aria-live="polite"><span id="result-count"><?= fa_digits(count($products)) ?></span> انتخاب خوش‌مزه</div>
      <?php if ($featured): ?><section class="featured" aria-labelledby="featured-title"><div class="featured__heading"><span>انتخاب امروز</span><h2 id="featured-title">پیشنهاد میرا</h2></div><div class="featured__list"><?php foreach(array_slice($featured,0,3) as $item): ?><a href="#product-<?= (int)$item['id'] ?>" data-product-link="<?= (int)$item['id'] ?>"><span>پیشنهاد امروز</span><strong><?= e($item['name_fa']) ?></strong><small><?= format_price($item['price']===null?null:(int)$item['price']) ?></small></a><?php endforeach; ?></div></section><?php endif; ?>
      <?php if (!$products): ?>
        <section class="empty-state"><h2>منو به‌زودی آماده می‌شود</h2><p>لطفاً کمی بعد دوباره سر بزنید.</p></section>
      <?php endif; ?>

      <?php foreach ($categories as $category): ?>
        <?php $items = $byCategory[(int) $category['id']] ?? []; if (!$items) continue; ?>
        <section class="menu-section" id="cat-<?= e($category['slug']) ?>" data-category="<?= e($category['slug']) ?>">
          <div class="section-heading"><div class="section-heading__icon"><?= category_icon($category['slug']) ?></div><div><h2><?= e($category['name_fa']) ?></h2><?php if(!empty($category['name_en'])):?><p lang="en" dir="ltr"><?=e($category['name_en'])?></p><?php endif;?></div><span></span><small><?= fa_digits(count($items)) ?> انتخاب</small></div>
          <div class="product-grid">
            <?php foreach ($items as $product): ?>
              <?php $search = trim($product['name_fa'] . ' ' . $product['name_en'] . ' ' . $product['description'] . ' ' . $product['ingredients']); ?>
              <article id="product-<?= (int)$product['id'] ?>" class="product-card<?= !$product['is_available'] ? ' is-unavailable' : '' ?><?= $product['is_featured'] ? ' is-featured' : '' ?>" data-product-id="<?= (int)$product['id'] ?>" data-search="<?= e(mb_strtolower($search, 'UTF-8')) ?>">
                <?php if ($product['image']): ?>
                  <div class="product-card__image">
                    <img src="<?= url('uploads/' . rawurlencode($product['image'])) ?>" alt="<?= e($product['name_fa']) ?>" loading="lazy" width="480" height="360">
                  </div>
                <?php endif; ?>
                <div class="product-card__body">
                  <div class="product-card__top">
                    <div><h3><?= e($product['name_fa']) ?></h3><?php if ($product['name_en']): ?><p class="en" lang="en" dir="ltr"><?= e($product['name_en']) ?></p><?php endif; ?></div>
                    <?php if (!$product['is_available']): ?><span class="sold-out">ناموجود</span><?php endif; ?>
                  </div>
                  <?php if ($product['description']): ?><p class="description"><?= e($product['description']) ?></p><?php endif; ?>
                  <p class="price"><?= format_price($product['price'] === null ? null : (int) $product['price']) ?></p>
                  <?php if ($product['badge_vegan'] || $product['badge_sugar_free'] || $product['badge_diet'] || $product['caffeine_level']): ?><div class="product-badges" aria-label="ویژگی‌های محصول"><?php if($product['badge_vegan']):?><span title="کاملاً گیاهی">◌ وگان</span><?php endif;?><?php if($product['badge_sugar_free']):?><span title="بدون شکر افزوده">◇ بدون شکر</span><?php endif;?><?php if($product['badge_diet']):?><span>○ رژیمی</span><?php endif;?><?php if($product['caffeine_level']):?><span class="caffeine" title="میزان کافئین">کافئین <?php for($i=1;$i<=3;$i++):?><i class="<?=$i<=$product['caffeine_level']?'on':''?>"></i><?php endfor;?></span><?php endif;?></div><?php endif; ?>
                  <?php if (!empty($product['ingredients'])): ?><div class="ingredients"><button type="button" class="ingredients__toggle" aria-expanded="false"><span>ترکیبات</span><i aria-hidden="true">+</i></button><div class="ingredients__content" hidden><p><?= e($product['ingredients']) ?></p></div></div><?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
      <section class="no-results" hidden><span>چیزی پیدا نشد</span><p>نام دیگری را جستجو کنید یا یک دسته دیگر را ببینید.</p></section>
    </div>
  </main>

  <footer>
    <div class="footer-mark">MIRA <i></i> CAFE</div>
    <p><?= e($settings['footer_text'] ?? 'با عشق برای شما ☕️') ?></p>
    <div class="footer-links">
      <?php if (!empty($settings['instagram'])): ?><a href="https://instagram.com/<?= e(ltrim($settings['instagram'], '@')) ?>" target="_blank" rel="noopener">اینستاگرام</a><?php endif; ?>
      <?php if (!empty($settings['phone'])): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $settings['phone'])) ?>">تماس</a><?php endif; ?>
    </div>
    <?php if (!empty($settings['address'])): ?><address><?= e($settings['address']) ?></address><?php endif; ?>
    <small>© <?= fa_digits(date('Y')) ?> <?= e($cafeName) ?></small>
  </footer>
</body>
</html>
