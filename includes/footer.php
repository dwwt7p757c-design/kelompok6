<?php $footerName = setting('store_name', 'KELOMPOK 6'); ?>
<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand"><a class="wordmark" href="index.php"><?=e($footerName)?></a><p><?=e(setting('store_description', 'A considered collection for modern living.'))?></p></div>
    <div><h2>EXPLORE</h2><a href="shop.php">Shop all</a><a href="about.php">Our story</a><a href="contact.php">Contact</a></div>
    <div><h2>INFORMATION</h2><a href="privacy.php">Privacy policy</a><a href="terms.php">Terms &amp; conditions</a><a href="admin/login.php">Administrator</a></div>
    <div><h2>GET IN TOUCH</h2><?php if (setting('contact_email')): ?><a href="mailto:<?=e(setting('contact_email'))?>"><?=e(setting('contact_email'))?></a><?php endif; ?><?php if (setting('contact_whatsapp')): ?><a href="https://wa.me/<?=e(preg_replace('/\D+/', '', setting('contact_whatsapp')))?>" rel="noopener">WhatsApp</a><?php endif; ?><?php if (setting('instagram_url')): ?><a href="<?=e(setting('instagram_url'))?>" target="_blank" rel="noopener noreferrer">Instagram</a><?php endif; ?><?php if (setting('tiktok_url')): ?><a href="<?=e(setting('tiktok_url'))?>" target="_blank" rel="noopener noreferrer">TikTok</a><?php endif; ?></div>
  </div>
  <div class="footer-bottom"><span>© <?=date('Y')?> <?=e($footerName)?>. ALL RIGHTS RESERVED.</span><span>CURATED FOR YOUR LIFESTYLE.</span></div>
</footer>
