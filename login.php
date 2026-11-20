<?php
require_once __DIR__.'/../config.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $s=db()->prepare("SELECT * FROM admins WHERE username=?"); $s->execute([trim($_POST['username']??'')]); $a=$s->fetch();
  if ($a && password_verify($_POST['password']??'', $a['password_hash'])) {
    session_regenerate_id(true); $_SESSION['admin_id']=$a['id']; $_SESSION['admin_name']=$a['username']; header('Location: dashboard.php'); exit;
  } $error='Username atau password salah.';
}
?><!doctype html><html lang="id"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title><link rel="stylesheet" href="../assets/style.css"><body class="admin-body"><main class="auth-card"><p class="eyebrow">PRIVATE ACCESS</p><h1>Admin login</h1><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<form method="post"><label>Username</label><input name="username" required autocomplete="username"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button class="btn dark">Masuk dashboard</button></form><a class="muted small" href="../index.php">← Kembali ke toko</a></main></body></html>
