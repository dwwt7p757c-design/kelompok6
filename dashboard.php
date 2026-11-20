<?php
require_once __DIR__.'/../config.php'; admin_required();
$products=(int)db()->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders=(int)db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending=(int)db()->query("SELECT COUNT(*) FROM orders WHERE status='Menunggu'")->fetchColumn();
$revenue=(float)db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('Diproses','Dikirim','Selesai')")->fetchColumn();
?><!doctype html><html lang="id"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard Admin</title><link rel="stylesheet" href="../assets/style.css"><body class="admin-body">
<header class="admin-top"><a class="brand" href="../index.php"><?=e(setting('store_name','ATELIER'))?></a><span>ADMIN PANEL</span><a href="logout.php">Keluar</a></header><main class="admin-wrap"><p class="eyebrow">OVERVIEW</p><h1>Dashboard</h1><p class="muted">Selamat datang, <?=e($_SESSION['admin_name'])?>.</p>
<nav class="admin-nav"><a class="active" href="dashboard.php">Ringkasan</a><a href="products.php">Produk</a><a href="orders.php">Pesanan</a><a href="settings.php">Pengaturan toko</a></nav>
<section class="stats"><article><span>Total produk</span><strong><?=$products?></strong></article><article><span>Total pesanan</span><strong><?=$orders?></strong></article><article><span>Menunggu diproses</span><strong><?=$pending?></strong></article><article><span>Omzet pesanan aktif/selesai</span><strong><?=rupiah($revenue)?></strong></article></section>
<div class="panel"><h2>Mulai kelola toko</h2><p>Tambahkan produk, unggah foto, atur harga, ukuran, stok, lalu pantau pesanan pelanggan dari menu di atas.</p><a class="btn dark inline" href="products.php">Kelola produk</a></div></main></body></html>
