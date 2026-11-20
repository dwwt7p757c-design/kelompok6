<?php
// Run only from a trusted terminal: php admin/create_admin.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config.php';
if ((int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) { fwrite(STDERR, "An administrator already exists; refusing to create another.\n"); exit(1); }
$username = trim((string)($argv[1] ?? ''));
$email = trim((string)($argv[2] ?? ''));
if ($username === '' || !preg_match('/^[A-Za-z0-9_.-]{3,80}$/', $username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php admin/create_admin.php <username> <email>\n"); exit(1);
}
fwrite(STDOUT, 'Password: ');
if (function_exists('shell_exec')) @shell_exec('stty -echo');
$password = trim((string)fgets(STDIN));
if (function_exists('shell_exec')) @shell_exec('stty echo');
fwrite(STDOUT, "\n");
if (strlen($password) < 12) { fwrite(STDERR, "Use a password of at least 12 characters.\n"); exit(1); }
$stmt = db()->prepare('INSERT INTO admins(username,email,password_hash) VALUES(?,?,?)');
$stmt->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Administrator created. Remove this script from the public web directory if your host exposes it.\n");
