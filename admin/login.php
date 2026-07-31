<?php
require __DIR__ . '/bootstrap.php';

if (adminIsLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $csrf = (string) ($_POST['csrf_token'] ?? '');

    if (!adminVerifyCsrf($csrf)) {
        $error = 'Your session expired. Please try again.';
    } elseif (($adminConfig['admin_password'] ?? '') === '') {
        $error = 'Admin password is not configured on the server.';
    } else {
        $validUsername = hash_equals((string) $adminConfig['admin_username'], $username);
        $validPassword = hash_equals((string) $adminConfig['admin_password'], $password);

        if ($validUsername && $validPassword) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }

        usleep(500000);
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Admin Login | Vdesiconnect</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3edef;font-family:Arial,sans-serif;color:#2d2527}
    .login-card{width:min(420px,calc(100% - 32px));background:#fff;border:1px solid #ddd;border-radius:12px;padding:30px;box-shadow:0 12px 35px rgba(70,20,35,.1)}
    .login-logo{display:block;max-width:180px;max-height:72px;margin:0 auto 22px}h1{margin:0;color:#7b1831;text-align:center}p{color:#70676a;text-align:center}label{display:block;font-weight:bold;margin:17px 0 7px}
    input{width:100%;padding:12px;border:1px solid #aaa;border-radius:6px;font-size:16px}button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:6px;background:#7b1831;color:#fff;font-size:16px;cursor:pointer}
    .error{background:#fde7e7;color:#8e1717;padding:12px;border-radius:6px;margin-top:18px}
  </style>
</head>
<body>
  <main class="login-card">
    <img class="login-logo" src="../img/logo.png" alt="Vdesiconnect logo">
    <h1>Admin Login</h1>
    <p>Sign in to manage customer orders.</p>
    <?php if ($error !== ''): ?><div class="error"><?= adminEscape($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Sign in</button>
    </form>
  </main>
</body>
</html>
