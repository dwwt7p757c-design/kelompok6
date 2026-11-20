<?php
require_once __DIR__.'/config.php';

$categories = db()->query("SELECT * FROM categories WHERE active=1 ORDER BY sort_order, name")->fetchAll();
$categorySlug = trim((string)($_GET['category'] ?? ''));
$search = trim((string)($_GET['q'] ?? ''));
$sort = trim((string)($_GET['sort'] ?? 'newest'));

$where = ['p.active = 1'];
$params = [];
if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    $term = '%'.$search.'%';
    array_push($params, $term, $term, $term, $term, $term);
}
if (isset($_GET['min']) && $_GET['min'] !== '') {
    $where[] = 'COALESCE(p.sale_price,p.price) >= ?';
    $params[] = max(0, (float)$_GET['min']);
}
if (isset($_GET['max']) && $_GET['max'] !== '') {
    $where[] = 'COALESCE(p.sale_price,p.price) <= ?';
    $params[] = max(0, (float)$_GET['max']);
}

$orderBy = 'p.created_at DESC';
if ($sort === 'price-low') $orderBy = 'COALESCE(p.sale_price,p.price) ASC';
if ($sort === 'price-high') $orderBy = 'COALESCE(p.sale_price,p.price) DESC';
if ($sort === 'name') $orderBy = 'p.name ASC';
if ($sort === 'featured') $orderBy = 'p.featured DESC, p.created_at DESC';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;
$sql = 'FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE '.implode(' AND ', $where);
$count = db()->prepare('SELECT COUNT(*) '.$sql);
$count->execute($params);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$query = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug $sql ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$query->execute($params);
$products = $query->fetchAll();
?><!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shop — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head>
<body><?php require __DIR__.'/includes/header.php'; ?>
<main class="catalog-page">
  <header class="catalog-header"><p class="eyebrow">THE COLLECTION</p><h1><?= $search !== '' ? 'Search: '.e($search) : 'Shop all' ?></h1></header>
  <form class="catalog-filters" method="get">
    <input type="search" name="q" value="<?=e($search)?>" placeholder="Search products or SKU">
    <select name="category" onchange="this.form.submit()"><option value="">All categories</option><?php foreach($categories as $c): ?><option value="<?=e($c['slug'])?>" <?=$categorySlug=== $c['slug']?'selected':''?>><?=e($c['name'])?></option><?php endforeach; ?></select>
    <input type="number" name="min" min="0" value="<?=e($_GET['min']??'')?>" placeholder="Minimum price">
    <input type="number" name="max" min="0" value="<?=e($_GET['max']??'')?>" placeholder="Maximum price">
    <select name="sort"><option value="newest" <?=$sort==='newest'?'selected':''?>>Newest</option><option value="price-low" <?=$sort==='price-low'?'selected':''?>>Price: Low to High</option><option value="price-high" <?=$sort==='price-high'?'selected':''?>>Price: High to Low</option><option value="name" <?=$sort==='name'?'selected':''?>>Name: A–Z</option><option value="featured" <?=$sort==='featured'?'selected':''?>>Featured</option></select>
    <button class="button" type="submit">APPLY</button>
  </form>
  <?php if (!$products): ?><div class="empty-state"><h2>No products found</h2><p>Try changing the filters or search terms.</p><a class="button" href="shop.php">CLEAR FILTERS</a></div>
  <?php else: ?><?php require __DIR__.'/includes/product-grid.php'; ?><nav class="pagination" aria-label="Pagination"><?php for($i=1;$i<=min($pages,20);$i++): $paramsUrl=$_GET;$paramsUrl['page']=$i; ?><a class="<?=$i=== $page?'active':''?>" href="shop.php?<?=e(http_build_query($paramsUrl))?>"><?=$i?></a><?php endfor; ?></nav><?php endif; ?>
</main><?php require __DIR__.'/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
