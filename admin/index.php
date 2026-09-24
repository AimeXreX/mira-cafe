<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$allowedActions=['toggle_available'];
if($_SERVER['REQUEST_METHOD']==='POST'){require_admin();csrf_verify();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);if($id&&in_array($_POST['action']??'',$allowedActions,true)){db()->prepare('UPDATE products SET is_available=1-is_available WHERE id=?')->execute([$id]);flash('success','موجودی محصول به‌روز شد.');}redirect('admin/');}
$adminTitle = 'پیشخوان';
$stats = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'available' => (int) db()->query('SELECT COUNT(*) FROM products WHERE is_available=1 AND is_visible=1')->fetchColumn(),
    'categories' => (int) db()->query('SELECT COUNT(*) FROM categories WHERE is_active=1')->fetchColumn(),
    'hidden' => (int) db()->query('SELECT COUNT(*) FROM products WHERE is_visible=0')->fetchColumn(),
];
$quickProducts=db()->query('SELECT id,name_fa,is_available,is_featured FROM products WHERE is_visible=1 ORDER BY is_featured DESC,updated_at DESC LIMIT 12')->fetchAll();
$todayViews=(int)db()->query("SELECT COALESCE(SUM(event_count),0) FROM analytics_daily WHERE event_date=CURDATE() AND event_type='view'")->fetchColumn();
require __DIR__ . '/_header.php';
?><section class="stats"><article><span>محصول‌ها</span><strong><?= fa_digits($stats['products']) ?></strong></article><article><span>موجود و نمایان</span><strong><?= fa_digits($stats['available']) ?></strong></article><article><span>بازدید امروز</span><strong><?= fa_digits($todayViews) ?></strong></article><article><span>دسته فعال</span><strong><?= fa_digits($stats['categories']) ?></strong></article></section><section class="panel welcome"><div><h2>مدیریت سریع منو</h2><p>وضعیت محصولات را بدون ورود به صفحه ویرایش تغییر دهید.</p></div><div class="actions"><a class="btn primary" href="<?= url('admin/product-edit.php') ?>">+ محصول جدید</a><a class="btn" href="<?= url('admin/analytics.php') ?>">گزارش بازدید</a></div></section><section class="panel quick-stock"><div class="panel-title"><div><h2>موجودی سریع</h2><p>محصولات پیشنهادی و تازه‌ویرایش‌شده در اولویت‌اند.</p></div><a href="<?=url('admin/products.php')?>">همه محصولات</a></div><div class="quick-grid"><?php foreach($quickProducts as $p):?><form method="post" class="quick-item"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><input type="hidden" name="action" value="toggle_available"><span><?php if($p['is_featured']):?><i>پیشنهاد</i><?php endif;?><strong><?=e($p['name_fa'])?></strong></span><button class="status <?=$p['is_available']?'on':'off'?>" type="submit"><?=$p['is_available']?'موجود':'ناموجود'?></button></form><?php endforeach;?></div></section><?php require __DIR__ . '/_footer.php'; ?>
