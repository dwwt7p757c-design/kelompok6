<?php
require_once __DIR__ . '/../config.php';
if (!empty($_SESSION['admin_id'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!empty($_SESSION['admin_login_after']) && time() < (int)$_SESSION['admin_login_after']) {
        $error = 'Terlalu banyak percobaan. Coba lagi beberapa menit.';
    } elseif ($username === '' || $password === '') {
        $error = 'Masukkan username dan password.';
    } else {
        try {
            $stmt = db()->prepare('SELECT id,username,password_hash,failed_attempts,locked_until FROM admins WHERE username=? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            $locked = $admin && $admin['locked_until'] && strtotime($admin['locked_until']) > time();
            if (!$admin || $locked || !password_verify($password, $admin['password_hash'])) {
                if ($admin && !$locked) {
                    $attempts = (int)$admin['failed_attempts'] + 1;
                    $until = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                    db()->prepare('UPDATE admins SET failed_attempts=?,locked_until=? WHERE id=?')->execute([$attempts,$until,$admin['id']]);
                }
                $_SESSION['admin_login_after'] = time() + 2;
                $error = 'Username atau password tidak valid.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                unset($_SESSION['admin_login_after']);
                db()->prepare('UPDATE admins SET failed_attempts=0,locked_until=NULL,last_login_at=NOW() WHERE id=?')->execute([$admin['id']]);
                redirect('index.php');
            }
        } catch (Throwable $exception) {
            error_log('[KELOMPOK6] Admin login failed: '.$exception->getMessage());
            $error = 'Login belum dapat diproses. Pastikan database sudah terpasang.';
        }
    }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login — <?=e(setting('store_name','KELOMPOK 6'))?></title><link rel="stylesheet" href="../assets/css/style.css"></head><body class="admin-body"><main class="admin-login"><p class="eyebrow">STORE MANAGEMENT</p><h1>Administrator login</h1><?php if($error): ?><p class="notice error"><?=e($error)?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Username<input name="username" autocomplete="username" required maxlength="80"></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="button button-dark" type="submit">SIGN IN</button></form><a href="../index.php">Return to store</a></main></body></html>
