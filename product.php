<?php
require_once __DIR__ . '/config.php';
$slug = trim((string)($_GET['slug'] ?? ''));
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if ($slug !== '') {
    $stmt = db()->prepare('SELECT p.*,c.name AS category_name,c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.active=1 LIMIT 1');
    $stmt->execute([$slug]);
} else {
    $stmt = db()->prepare('SELECT p.*,c.name AS category_name,c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1 LIMIT 1');
    $stmt->execute([$id ?: 0]);
}
$product = $stmt->fetch();
if (!$product) { http_response_code(404); exit('Produk tidak ditemukan.'); }
$sizesStmt = db()->prepare('SELECT size_name,stock FROM product_sizes WHERE product_id=? ORDER BY FIELD(size_name,"XS","S","M","L","XL","XXL"),size_name');
$sizesStmt->execute([$product['id']]);
$sizes = $sizesStmt->fetchAll();
$imagesStmt = db()->prepare('SELECT image_path,alt_text FROM product_images WHERE product_id=? ORDER BY sort_order,id');
$imagesStmt->execute([$product['id']]);
$images = $imagesStmt->fetchAll();
$price = effective_price($product);
$hasSale = $product['sale_price'] !== null && (float)$product['sale_price'] < (float)$product['price'];
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($product['name'])?> — <?=e(setting('store_name','KELOMPOK 6'))?></title><meta name="description" content="<?=e($product['short_description'])?>"><link rel="stylesheet" href="assets/css/style.css"></head><body><?php require __DIR__.'/includes/header.php'; ?>
<main class="content-page product-detail-page"><nav class="breadcrumbs"><a href="index.php">Home</a> / <a href="shop.php">Shop</a><?php if($product['category_name']): ?> / <a href="shop.php?category=<?=urlencode((string)$product['category_slug'])?>"><?=e($product['category_name'])?></a><?php endif; ?> / <?=e($product['name'])?></nav>
<div class="product-detail"><div class="product-gallery"><?php if($images): foreach($images as $image): ?><img src="<?=e($image['image_path'])?>" alt="<?=e($image['alt_text'] ?: $product['name'])?>" loading="lazy"><?php endforeach; elseif($product['image']): ?><img src="<?=e($product['image'])?>" alt="<?=e($product['name'])?>"><?php else: ?><div class="image-placeholder">KELOMPOK 6</div><?php endif; ?></div>
<section class="product-detail-info"><p class="eyebrow"><?=e($product['category_name'] ?? 'THE COLLECTION')?></p><h1><?=e($product['name'])?></h1><p class="sku">SKU: <?=e($product['sku'])?></p><div class="product-detail-price"><?php if($hasSale): ?><del><?=e(money($product['price']))?></del><?php endif; ?><strong><?=e(money($price))?></strong></div><?php if($product['short_description']): ?><p class="product-short-description"><?=e($product['short_description'])?></p><?php endif; ?>
<?php if ($message = flash('error')): ?><p class="notice error"><?=e($message)?></p><?php endif; ?><?php if ($message = flash('success')): ?><p class="notice success"><?=e($message)?></p><?php endif; ?>
<?php if ((!$sizes && (int)$product['stock'] > 0) || ($sizes && array_sum(array_column($sizes,'stock')) > 0)): ?><form action="cart.php" method="post" class="add-to-cart-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?=(int)$product['id']?>"><?php if($sizes): ?><label for="size">Choose size</label><select name="size" id="size" required><option value="">Select a size</option><?php foreach($sizes as $size): ?><option value="<?=e($size['size_name'])?>" <?=$size['stock']<1?'disabled':''?>><?=e($size['size_name'])?><?=$size['stock']<1?' — Sold out':''?></option><?php endforeach; ?></select><?php endif; ?><button class="button button-dark" type="submit">ADD TO BAG</button></form><?php else: ?><p class="stock-status">Currently unavailable</p><?php endif; ?>
<div class="product-description"><h2>Details</h2><?=nl2br(e($product['description']))?></div><p class="stock-status"><?php if(!$sizes): ?><?=((int)$product['stock']>0?'In stock':'Out of stock')?><?php else: ?>Select an available size to check stock.<?php endif; ?></p></section></div></main><?php require __DIR__.'/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
