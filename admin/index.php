<?php
require_once __DIR__ . '/includes.php';
$stats = [
 'products'=>(int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
 'active'=>(int)db()->query('SELECT COUNT(*) FROM products WHERE active=1')->fetchColumn(),
 'orders'=>(int)db()->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Completed','Cancelled')")->fetchColumn(),
 'revenue'=>(float)db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='Paid'")->fetchColumn(),
];
$recent = db()->query('SELECT order_code,customer_name,total,status,created_at FROM orders ORDER BY created_at DESC LIMIT 10')->fetchAll();
admin_header('Dashboard');
?><div class="admin-stat-grid"><article><span>Products</span><strong><?=$stats['products']?></strong><small><?=$stats['active']?> active</small></article><article><span>Open orders</span><strong><?=$stats['orders']?></strong></article><article><span>Paid revenue</span><strong><?=e(money($stats['revenue']))?></strong></article></div><section class="admin-panel"><div class="section-heading"><h2>Recent orders</h2><a href="orders.php">All orders</a></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($recent as $order): ?><tr><td><?=e($order['order_code'])?></td><td><?=e($order['customer_name'])?></td><td><?=e(money($order['total']))?></td><td><?=e($order['status'])?></td><td><?=e($order['created_at'])?></td></tr><?php endforeach; ?><?php if(!$recent): ?><tr><td colspan="5">No orders yet.</td></tr><?php endif; ?></tbody></table></div></section><?php admin_footer(); ?>
