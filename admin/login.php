<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userOk = hash_equals((string)$config['app']['admin_user'], post('username', 100));
    $passwordOk = password_verify(post('password', 500), (string)$config['app']['admin_password_hash']);
    if ($userOk && $passwordOk) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    usleep(500000);
    $error = 'Invalid username or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>BCE Admin</title><link rel="stylesheet" href="../assets/styles.css"></head>
<body class="admin-body"><main class="login-card"><div class="brand"><span class="mark">BC</span><strong>Baltic Crew Exchange</strong></div><h1>Admin sign in</h1><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Username<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="btn primary" type="submit">Sign in</button></form></main></body></html>
