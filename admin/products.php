<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? '');
    if (!$id) { flash('error','محصول نامعتبر است.'); redirect('admin/products.php'); }
    if ($action === 'delete') {
        $stmt = db()->prepare('SELECT image FROM products WHERE id=?'); $stmt->execute([$id]); $row=$stmt->fetch();
        if ($row) { db()->prepare('DELETE FROM products WHERE id=?')->execute([$id]); delete_image($row['image']); flash('success','محصول حذف شد.'); }
    } elseif ($action === 'toggle') {
        db()->prepare('UPDATE products SET is_available=1-is_available WHERE id=?')->execute([$id]); flash('success','وضعیت موجودی تغییر کرد.');
    }
    redirect('admin/products.php');
}
$products = db()->query('SELECT p.id,p.name_fa,p.price,p.is_available,p.is_visible,p.sort_order,c.name_fa category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY c.sort_order,p.sort_order,p.id')->fetchAll();
$adminTitle='محصولات'; require __DIR__.'/_header.php';
?><div class="toolbar"><p><?= fa_digits(count($products)) ?> محصول ثبت شده</p><a class="btn primary" href="<?= url('admin/product-edit.php') ?>">+ افزودن محصول</a></div><div class="panel table-wrap"><table><thead><tr><th>نام</th><th>دسته</th><th>قیمت</th><th>وضعیت</th><th>ترتیب</th><th>عملیات</th></tr></thead><tbody><?php foreach($products as $p): ?><tr><td data-label="نام"><strong><?= e($p['name_fa']) ?></strong><?php if(!$p['is_visible']): ?><small class="badge muted-badge">مخفی</small><?php endif; ?></td><td data-label="دسته"><?= e($p['category_name']) ?></td><td data-label="قیمت"><?= format_price($p['price']===null?null:(int)$p['price']) ?></td><td data-label="وضعیت"><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="status <?= $p['is_available']?'on':'off' ?>" type="submit"><?= $p['is_available']?'موجود':'ناموجود' ?></button></form></td><td data-label="ترتیب"><?= fa_digits($p['sort_order']) ?></td><td data-label="عملیات" class="row-actions"><a class="btn small" href="<?= url('admin/product-edit.php?id='.(int)$p['id']) ?>">ویرایش</a><form method="post" class="inline" data-confirm="این محصول حذف شود؟"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn small danger" type="submit">حذف</button></form></td></tr><?php endforeach; ?></tbody></table><?php if(!$products): ?><div class="empty">هنوز محصولی ثبت نشده است.</div><?php endif; ?></div><?php require __DIR__.'/_footer.php'; ?>
