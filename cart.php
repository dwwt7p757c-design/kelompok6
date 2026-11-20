<?php
require_once __DIR__ . '/config.php';

function cart_key(int $productId, string $size = ''): string {
    return $productId . '|' . $size;
}

function cart_parse_key(string $key): array {
    if (str_contains($key, '|')) {
        [$id, $size] = array_pad(explode('|', $key, 2), 2, '');
    } else { // Support the initial starter-cart format.
        [$id, $size] = array_pad(explode('_', $key, 2), 2, '');
    }
    return [(int)$id, trim($size)];
}

function cart_stock(PDO $pdo, int $productId, string $size): int {
    if ($size !== '') {
        $stmt = $pdo->prepare('SELECT stock FROM product_sizes WHERE product_id=? AND size_name=? LIMIT 1');
        $stmt->execute([$productId, $size]);
        $stock = $stmt->fetchColumn();
        return $stock === false ? 0 : (int)$stock;
    }
    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id=? AND active=1');
    $stmt->execute([$productId]);
    return (int)($stmt->fetchColumn() ?: 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'add') {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $size = trim((string)($_POST['size'] ?? ''));
        if (!$productId) {
            flash('error', 'Produk tidak valid.');
            redirect('shop.php');
        }
        $stmt = db()->prepare('SELECT id FROM products WHERE id=? AND active=1');
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            flash('error', 'Produk tidak tersedia.');
            redirect('shop.php');
        }
        $hasSizes = db()->prepare('SELECT COUNT(*) FROM product_sizes WHERE product_id=?');
        $hasSizes->execute([$productId]);
        if ((int)$hasSizes->fetchColumn() > 0 && $size === '') {
            flash('error', 'Pilih ukuran terlebih dahulu.');
            redirect('product.php?id=' . (int)$productId);
        }
        $key = cart_key((int)$productId, $size);
        $current = (int)($_SESSION['cart'][$key] ?? 0);
        if (cart_stock(db(), (int)$productId, $size) <= $current) {
            flash('error', 'Stok tidak mencukupi.');
        } else {
            $_SESSION['cart'][$key] = min(99, $current + 1);
            flash('success', 'Produk ditambahkan ke tas.');
        }
        redirect('cart.php');
    }
    if ($action === 'update') {
        $updated = [];
        foreach ((array)($_POST['qty'] ?? []) as $key => $quantity) {
            if (!is_string($key) || !preg_match('/^\d+(?:\|[^|]*)?$/', $key)) continue;
            [$productId, $size] = cart_parse_key($key);
            $quantity = max(0, min(99, (int)$quantity));
            if ($quantity === 0) continue;
            $stock = cart_stock(db(), $productId, $size);
            if ($stock > 0) $updated[$key] = min($quantity, $stock);
        }
        $_SESSION['cart'] = $updated;
        flash('success', 'Tas belanja diperbarui.');
        redirect('cart.php');
    }
    if ($action === 'remove') {
        $key = (string)($_POST['key'] ?? '');
        unset($_SESSION['cart'][$key]);
        redirect('cart.php');
    }
}

$items = [];
$subtotal = 0.0;
foreach ((array)($_SESSION['cart'] ?? []) as $key => $quantity) {
    [$productId, $size] = cart_parse_key((string)$key);
    $quantity = max(0, min(99, (int)$quantity));
    $stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    $available = $product ? cart_stock(db(), $productId, $size) : 0;
    if (!$product || $available < 1 || $quantity < 1) {
        unset($_SESSION['cart'][$key]);
        continue;
    }
    $quantity = min($quantity, $available);
    $_SESSION['cart'][$key] = $quantity;
    $price = effective_price($product);
    $lineTotal = $price * $quantity;
    $subtotal += $lineTotal;
    $items[] = compact('key', 'product', 'size', 'quantity', 'price', 'lineTotal', 'available');
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Your bag — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head>
<body><?php require __DIR__ . '/includes/header.php'; ?>
<main class="content-page cart-page"><p class="eyebrow">YOUR SELECTION</p><h1>Your bag <span>(<?=count($items)?>)</span></h1>
<?php if ($message = flash('success')): ?><p class="notice success"><?=e($message)?></p><?php endif; ?><?php if ($message = flash('error')): ?><p class="notice error"><?=e($message)?></p><?php endif; ?>
<?php if (!$items): ?><div class="empty-state"><h2>Your bag is waiting.</h2><p>Explore the collection and find something you love.</p><a class="button" href="shop.php">CONTINUE SHOPPING</a></div>
<?php else: ?><form action="cart.php" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="update"><div class="cart-layout"><div class="cart-items">
<?php foreach ($items as $item): ?><article class="cart-item"><a href="product.php?id=<?=(int)$item['product']['id']?>"><?php if ($item['product']['image']): ?><img src="<?=e($item['product']['image'])?>" alt="<?=e($item['product']['name'])?>"><?php else: ?><span class="image-placeholder">KELOMPOK 6</span><?php endif; ?></a><div class="cart-item-details"><h2><a href="product.php?id=<?=(int)$item['product']['id']?>"><?=e($item['product']['name'])?></a></h2><?php if ($item['size'] !== ''): ?><p>Size: <?=e($item['size'])?></p><?php endif; ?><p><?=e(money($item['price']))?></p><label>Qty <input type="number" min="0" max="<?=min(99,$item['available'])?>" name="qty[<?=e($item['key'])?>]" value="<?=$item['quantity']?>"></label><p class="cart-line-total"><?=e(money($item['lineTotal']))?></p></div><button class="text-button" type="submit" form="remove-<?=md5($item['key'])?>">Remove</button></article><?php endforeach; ?>
</div><aside class="cart-summary"><h2>Order summary</h2><p><span>Subtotal</span><strong><?=e(money($subtotal))?></strong></p><p><span>Shipping</span><span>Calculated at checkout</span></p><button class="button" type="submit">UPDATE BAG</button><a class="button button-dark" href="checkout.php">PROCEED TO CHECKOUT</a><a class="text-link" href="shop.php">CONTINUE SHOPPING</a></aside></div></form>
<?php foreach ($items as $item): ?><form id="remove-<?=md5($item['key'])?>" action="cart.php" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="key" value="<?=e($item['key'])?>"></form><?php endforeach; ?><?php endif; ?>
</main><?php require __DIR__ . '/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
