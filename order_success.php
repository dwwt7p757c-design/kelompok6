<?php
require_once __DIR__ . '/config.php';
$code = trim((string)($_SESSION['last_order'] ?? ''));
$order = null;
$items = [];
if ($code !== '') {
    $stmt = db()->prepare('SELECT order_code,customer_name,total,status,payment_status,created_at FROM orders WHERE order_code=? LIMIT 1');
    $stmt->execute([$code]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt = db()->prepare('SELECT product_name,variant_snapshot,quantity,unit_price,line_total FROM order_items WHERE order_id=(SELECT id FROM orders WHERE order_code=? LIMIT 1) ORDER BY id');
        $stmt->execute([$code]);
        $items = $stmt->fetchAll();
    }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Order received — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head><body><?php require __DIR__.'/includes/header.php'; ?><main class="content-page order-success-page"><p class="eyebrow">THANK YOU</p><?php if($order): ?><h1>Your order is received.</h1><p>Thank you, <?=e($order['customer_name'])?>. Your order reference is <strong><?=e($order['order_code'])?></strong>.</p><p>Order status: <?=e($order['status'])?> · Payment: <?=e($order['payment_status'])?></p><div class="order-receipt"><?php foreach($items as $item): ?><p><span><?=e($item['product_name'])?> × <?=(int)$item['quantity']?> <?=e($item['variant_snapshot'])?></span><strong><?=e(money($item['line_total']))?></strong></p><?php endforeach; ?><p class="checkout-total"><span>Total</span><strong><?=e(money($order['total']))?></strong></p></div><p>Keep your order reference for any questions. Payment details will be shared by the store.</p><?php else: ?><h1>No recent order found.</h1><p>Place an order to see its confirmation here.</p><?php endif; ?><a class="button" href="shop.php">CONTINUE SHOPPING</a></main><?php require __DIR__.'/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
