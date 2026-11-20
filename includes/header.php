<?php
$storeName = setting('store_name', 'KELOMPOK 6');
?>
<div class="announcement-bar"><?=e(setting('announcement', 'CURATED FOR YOUR LIFESTYLE.'))?></div>
<header class="site-header">
  <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle><span></span><span></span></button>
  <a class="wordmark" href="index.php" aria-label="<?=$storeName?> homepage"><?=e($storeName)?></a>
  <nav class="primary-nav" data-primary-nav aria-label="Main navigation">
    <a href="index.php">HOME</a><a href="shop.php">SHOP</a><a href="shop.php?sort=featured">COLLECTION</a><a href="about.php">ABOUT</a><a href="contact.php">CONTACT</a>
  </nav>
  <div class="header-actions">
    <form class="header-search" action="shop.php" method="get"><label class="visually-hidden" for="site-search">Search products</label><input id="site-search" type="search" name="q" placeholder="Search" value="<?=e($_GET['q'] ?? '')?>"><button type="submit" aria-label="Search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg></button></form>
    <a class="cart-link" href="cart.php" aria-label="Shopping bag, <?=cart_count()?> items"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1 13H5L4 8Z"></path><path d="M9 9V6a3 3 0 0 1 6 0v3"></path></svg><span>BAG</span><span class="bag-count"><?=cart_count()?></span></a>
  </div>
</header>
