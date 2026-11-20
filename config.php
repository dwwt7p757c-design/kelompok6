<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'luxury_store');
define('DB_USER', 'root');
define('DB_PASS', '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rupiah($n): string { return 'Rp '.number_format((float)$n, 0, ',', '.'); }
function admin_required(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}
function setting(string $key, string $fallback=''): string {
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key=?");
    $stmt->execute([$key]); $v = $stmt->fetchColumn();
    return $v === false ? $fallback : (string)$v;
}
function cart_count(): int {
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}
