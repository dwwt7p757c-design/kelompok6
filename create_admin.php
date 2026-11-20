<?php
require_once __DIR__.'/../config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($username) < 4 || strlen($password) < 10) {
        $message = 'Username minimal 4 karakter dan password minimal 10 karakter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            db()->prepare("INSERT INTO admins(username,password_hash) VALUES(?,?)")->execute([$username,$hash]);
            $message = 'Admin berhasil dibuat. Hapus file create_admin.php sekarang, lalu login.';
        } catch (Throwable $e) { $message = 'Gagal membuat admin. Pastikan database sudah diimpor dan username belum dipakai.'; }
    }
}
?>
<!doctype html><html lang="id"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Buat Admin</title>
<link rel="stylesheet" href="../assets/style.css"><body class="admin-body"><main class="auth-card"><p class="eyebrow">INITIAL SETUP</p><h1>Buat akun admin</h1>
<?php if($message): ?><p class="notice"><?=e($message)?></p><?php endif; ?>
<form method="post"><label>Username</label><input name="username" required minlength="4"><label>Password</label><input type="password" name="password" required minlength="10"><button class="btn dark">Buat Admin</button></form>
<p class="muted small">Setelah berhasil, hapus file admin/create_admin.php dari hosting.</p></main></body></html>
