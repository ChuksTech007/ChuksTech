<?php
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /iPortfolio/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (attemptLogin($user, $pass)) {
        header('Location: /iPortfolio/admin/index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — CC. Portfolio</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#050b18;--bg2:#0a1628;--card:#0d1f3c;--border:#1e3a5f;--accent:#38bdf8;--accent2:#818cf8;--text:#e2e8f0;--muted:#94a3b8;--danger:#f87171;--grad:linear-gradient(135deg,#38bdf8,#818cf8)}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:48px 44px;width:100%;max-width:420px;box-shadow:0 25px 60px rgba(0,0,0,.5)}
.brand{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-align:center;margin-bottom:6px}
.subtitle{text-align:center;color:var(--muted);font-size:13px;margin-bottom:36px}
label{display:block;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px}
input[type=text],input[type=password]{width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:12px 16px;color:var(--text);font-family:inherit;font-size:14px;outline:none;transition:border-color .2s}
input:focus{border-color:var(--accent)}
.field{margin-bottom:20px}
.btn-login{width:100%;background:var(--grad);border:none;border-radius:10px;padding:13px;color:#fff;font-family:inherit;font-size:15px;font-weight:600;cursor:pointer;transition:opacity .2s,transform .15s;margin-top:8px}
.btn-login:hover{opacity:.88;transform:translateY(-1px)}
.error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:20px;text-align:center}
.back{display:block;text-align:center;margin-top:20px;font-size:13px;color:var(--muted);text-decoration:none}
.back:hover{color:var(--accent)}
</style>
</head>
<body>
<div class="login-card">
  <div class="brand">CC.</div>
  <div class="subtitle">Portfolio Admin Panel</div>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif ?>

  <form method="POST">
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autocomplete="username" placeholder="admin">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
    </div>
    <button type="submit" class="btn-login">Sign In</button>
  </form>
  <a href="/iPortfolio/index.php" class="back">← Back to Portfolio</a>
</div>
</body>
</html>
