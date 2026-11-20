<?php
require_once __DIR__ . '/../config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = db()->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE status='active'");
            $stmt->execute([$email]);
            flash('success', 'Thank you for subscribing.');
            redirect('newsletter.php');
        } catch (Throwable $exception) {
            error_log('[KELOMPOK6] Newsletter save failed: ' . $exception->getMessage());
            $errors[] = 'We could not save your subscription. Please try again later.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Newsletter — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="assets/css/style.css"></head><body><?php require __DIR__.'/header.php'; ?><main class="content-page"><p class="eyebrow">STAY IN THE KNOW</p><h1>Newsletter</h1><?php if ($message = flash('success')): ?><p class="notice success"><?=e($message)?></p><?php endif; ?><?php foreach($errors as $error): ?><p class="notice error"><?=e($error)?></p><?php endforeach; ?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Email address<input name="email" type="email" required maxlength="190"></label><button class="button" type="submit">SUBSCRIBE</button></form></main><?php require __DIR__.'/footer.php'; ?></body></html>
