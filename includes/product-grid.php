<?php if (!isset($products)) $products = []; ?>
<div class="product-grid">
<?php foreach ($products as $product):
  $price = effective_price($product);
  $hasSale = isset($product['sale_price']) && $product['sale_price'] !== null && (float)$product['sale_price'] < (float)$product['price'];
  $productUrl = 'product.php?slug=' . rawurlencode((string)$product['slug']);
?>
  <article class="product-card">
    <a class="product-card-image" href="<?=e($productUrl)?>">
      <?php if (!empty($product['image'])): ?><img src="<?=e($product['image'])?>" alt="<?=e($product['name'])?>" loading="lazy" decoding="async"><?php else: ?><span class="image-placeholder" aria-label="Product image coming soon">KELOMPOK 6</span><?php endif; ?>
      <?php if ($hasSale): ?><span class="product-badge">SALE</span><?php elseif (!empty($product['featured'])): ?><span class="product-badge">CURATED</span><?php endif; ?>
    </a>
    <div class="product-card-meta"><div><p class="product-category"><?=e($product['category_name'] ?? '')?></p><h3><a href="<?=e($productUrl)?>"><?=e($product['name'])?></a></h3></div><div class="product-price"><?php if ($hasSale): ?><del><?=e(money($product['price']))?></del><?php endif; ?><span><?=e(money($price))?></span></div></div>
    <a class="product-card-cta" href="<?=e($productUrl)?>">VIEW PRODUCT <span aria-hidden="true">↗</span></a>
  </article>
<?php endforeach; ?>
</div>
