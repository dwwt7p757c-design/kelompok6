<?php
require_once __DIR__ . '/config.php';

function checkout_cart_key(string $key): array {
    if (str_contains($key, '|')) [$id, $size] = array_pad(explode('|', $key, 2), 2, '');
    else [$id, $size] = array_pad(explode('_', $key, 2), 2, '');
    return [(int)$id, trim($size)];
}

if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) redirect('cart.php');
$error = '';
$old = ['name'=>'','email'=>'','phone'=>'','province'=>'','city'=>'','district'=>'','address'=>'','postal_code'=>'','notes'=>''];
$shippingFee = max(0, (float)setting('shipping_fee', '0'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $key => $_) $old[$key] = trim((string)($_POST[$key] ?? ''));
    if ($old['name'] === '' || mb_strlen($old['name']) > 150 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)
        || mb_strlen($old['email']) > 190 || $old['phone'] === '' || mb_strlen($old['phone']) > 40
        || $old['address'] === '' || mb_strlen($old['address']) > 5000) {
        $error = 'Lengkapi nama, email valid, nomor telepon, dan alamat pengiriman.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $orderItems = [];
            $subtotal = 0.0;
            foreach ((array)$_SESSION['cart'] as $cartKey => $rawQuantity) {
                [$productId, $size] = checkout_cart_key((string)$cartKey);
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>99]]);
                if (!$quantity) throw new RuntimeException('Jumlah produk tidak valid.');
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id=? AND active=1 FOR UPDATE');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product) throw new RuntimeException('Salah satu produk tidak lagi tersedia. Silakan periksa tas belanja.');
                if ($size !== '') {
                    $stockStmt = $pdo->prepare('SELECT stock FROM product_sizes WHERE product_id=? AND size_name=? FOR UPDATE');
                    $stockStmt->execute([$productId, $size]);
                    $stock = $stockStmt->fetchColumn();
                    if ($stock === false || (int)$stock < $quantity) throw new RuntimeException('Stok ukuran ' . $size . ' untuk ' . $product['name'] . ' tidak mencukupi.');
                    $pdo->prepare('UPDATE product_sizes SET stock=stock-? WHERE product_id=? AND size_name=?')->execute([$quantity,$productId,$size]);
                } else {
                    $sizeCount = $pdo->prepare('SELECT COUNT(*) FROM product_sizes WHERE product_id=?');
                    $sizeCount->execute([$productId]);
                    if ((int)$sizeCount->fetchColumn() > 0) throw new RuntimeException('Pilih ukuran untuk produk ' . $product['name'] . '.');
                    if ((int)$product['stock'] < $quantity) throw new RuntimeException('Stok produk ' . $product['name'] . ' tidak mencukupi.');
                }
                $price = effective_price($product);
                $lineTotal = round($price * $quantity, 2);
                $subtotal += $lineTotal;
                $orderItems[] = ['product'=>$product,'size'=>$size,'quantity'=>$quantity,'price'=>$price,'line_total'=>$lineTotal];
            }
            if (!$orderItems) throw new RuntimeException('Tas belanja kosong.');
            $total = round($subtotal + $shippingFee, 2);
            $code = 'K6-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $insert = $pdo->prepare("INSERT INTO orders (order_code,customer_name,email,phone,province,city,district,address,postal_code,notes,subtotal,discount,shipping_fee,total,payment_status,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,0,?,?, 'Awaiting Payment','Awaiting Payment')");
            $insert->execute([$code,$old['name'],$old['email'],$old['phone'],$old['province'],$old['city'],$old['district'],$old['address'],$old['postal_code'],$old['notes'],$subtotal,$shippingFee,$total]);
            $orderId = (int)$pdo->lastInsertId();
            $insertItem = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,product_sku,size_name,variant_snapshot,quantity,unit_price,line_total) VALUES (?,?,?,?,?,?,?,?,?)');
            $decrementStock = $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
            foreach ($orderItems as $item) {
                $p = $item['product'];
                if ($item['size'] === '') {
                    $decrementStock->execute([$item['quantity'],$p['id'],$item['quantity']]);
                    if ($decrementStock->rowCount() !== 1) throw new RuntimeException('Stok produk ' . $p['name'] . ' berubah. Silakan coba lagi.');
                }
                $insertItem->execute([$orderId,$p['id'],$p['name'],$p['sku'],$item['size'],$item['size'] !== '' ? 'Size: '.$item['size'] : '',$item['quantity'],$item['price'],$item['line_total']]);
            }
            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $code;
            redirect('order_success.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[KELOMPOK6] Checkout failed: ' . $exception->getMessage());
            $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Pesanan belum berhasil disimpan. Periksa stok dan coba lagi.';
        }
    }
}

$items = [];
$subtotal = 0.0;
foreach ((array)($_SESSION['cart'] ?? []) as $cartKey => $quantity) {
    [$productId,$size] = checkout_cart_key((string)$cartKey);
    $stmt = db()->prepare('SELECT * FROM products WHERE id=? AND active=1');
    $stmt->execute([$productId]);
    if ($product = $stmt->fetch()) {
        $price = effective_price($product);
        $lineTotal = $price * (int)$quantity;
        $items[] = ['product'=>$product,'size'=>$size,'quantity'=>(int)$quantity,'price'=>$price,'line_total'=>$lineTotal];
        $subtotal += $lineTotal;
    }
}
if (!$items) redirect('cart.php');
$total = $subtotal + $shippingFee;
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checkout — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head><body><?php require __DIR__.'/includes/header.php'; ?>
<main class="content-page checkout-page"><p class="eyebrow">ALMOST YOURS</p><h1>Checkout</h1><?php if ($error !== ''): ?><p class="notice error"><?=e($error)?></p><?php endif; ?>
<div class="checkout-layout"><form class="checkout-form" method="post" action="checkout.php"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><h2>Shipping information</h2>
<label>Full name<input name="name" autocomplete="name" maxlength="150" required value="<?=e($old['name'])?>"></label><label>Email address<input name="email" type="email" autocomplete="email" maxlength="190" required value="<?=e($old['email'])?>"></label><label>Phone number<input name="phone" type="tel" autocomplete="tel" maxlength="40" required value="<?=e($old['phone'])?>"></label>
<div class="form-row"><label>Province <input name="province" maxlength="120" value="<?=e($old['province'])?>"></label><label>City <input name="city" maxlength="120" value="<?=e($old['city'])?>"></label></div><div class="form-row"><label>District <input name="district" maxlength="120" value="<?=e($old['district'])?>"></label><label>Postal code <input name="postal_code" maxlength="20" autocomplete="postal-code" value="<?=e($old['postal_code'])?>"></label></div>
<label>Full delivery address<textarea name="address" rows="4" maxlength="5000" autocomplete="street-address" required><?=e($old['address'])?></textarea></label><label>Order notes (optional)<textarea name="notes" rows="3" maxlength="2000"><?=e($old['notes'])?></textarea></label><p class="checkout-note">Payment instructions will be provided after your order is placed. Your order is confirmed only after payment is verified.</p><button class="button button-dark" type="submit">PLACE ORDER</button></form>
<aside class="checkout-summary"><h2>Your order</h2><?php foreach($items as $item): ?><div class="checkout-line"><span><?=e($item['product']['name'])?> × <?=$item['quantity']?><?php if($item['size']!==''): ?> <small>(<?=e($item['size'])?>)</small><?php endif; ?></span><strong><?=e(money($item['line_total']))?></strong></div><?php endforeach; ?><div class="checkout-line"><span>Subtotal</span><strong><?=e(money($subtotal))?></strong></div><div class="checkout-line"><span>Shipping</span><strong><?=e(money($shippingFee))?></strong></div><div class="checkout-line checkout-total"><span>Total</span><strong><?=e(money($total))?></strong></div><a class="text-link" href="cart.php">EDIT BAG</a></aside></div></main><?php require __DIR__.'/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
