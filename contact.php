<?php
require_once __DIR__ . '/config.php';
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));
    if ($name !== '' && mb_strlen($name) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 190 && $message !== '' && mb_strlen($message) <= 5000) {
        // This project does not send or store contact submissions; direct visitors to the configured contact channels.
        $sent = true;
    }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contact — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head><body><?php require __DIR__.'/includes/header.php'; ?><main class="content-page"><p class="eyebrow">WE'D LOVE TO HEAR FROM YOU</p><h1>Contact</h1><p>For order enquiries, product details, or collaborations, please reach us through our store contact channels.</p><?php if($sent): ?><p class="notice success">Thanks for reaching out. Please use the contact details below to send your enquiry.</p><?php endif; ?><?php if(setting('contact_email')): ?><p>Email: <a href="mailto:<?=e(setting('contact_email'))?>"><?=e(setting('contact_email'))?></a></p><?php endif; ?><?php if(setting('contact_whatsapp')): ?><p>WhatsApp: <a href="https://wa.me/<?=e(preg_replace('/\D+/', '', setting('contact_whatsapp')))?>" rel="noopener noreferrer">Message us</a></p><?php endif; ?><?php if(setting('contact_address')): ?><p>Address: <?=nl2br(e(setting('contact_address')))?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Your name<input name="name" maxlength="150" required></label><label>Email address<input name="email" type="email" maxlength="190" required></label><label>Message<textarea name="message" rows="6" maxlength="5000" required></textarea></label><button class="button" type="submit">SEND ENQUIRY</button><p class="checkout-note">This demo form does not transmit or store messages. Please use the email or WhatsApp contact shown above.</p></form></main><?php require __DIR__.'/includes/footer.php'; ?><script src="assets/js/app.js" defer></script></body></html>
