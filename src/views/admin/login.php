<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Login Page
 *
 * Farsi / RTL login form for the admin panel.
 * Default credentials: admin / admin
 */

// Redirect to dashboard if already logged in
require_once __DIR__ . '/../../auth.php';
adminStartSession();
if (isAdminLoggedIn()) {
    header('Location: /admin/dashboard', true, 302);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (adminLogin($username, $password)) {
        setAdminLoggedIn($username);
        header('Location: /admin/dashboard', true, 302);
        exit;
    }
    $error = 'نام کاربری یا رمز عبور اشتباه است.';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ورودی به پنل مدیریت | درفکی</title>
  <meta name="robots" content="noindex, nofollow" />

  <!-- Vazirmatn Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap"
    rel="stylesheet" />

  <!-- Built Tailwind CSS -->
  <link href="/css/output.css" rel="stylesheet" />

  <!-- Admin custom styles -->
  <link href="/css/admin.css" rel="stylesheet" />

  <!-- jQuery (local copy from node_modules) -->
  <script src="/js/jquery.min.js"></script>
</head>

<body class="admin-app">
  <div class="admin-login">
    <div class="admin-login-card">
      <div class="flex items-center justify-center mb-2">
        <div class="w-10 h-10 bg-gradient-to-br from-primary-600 to-secondary-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">🏢</span>
        </div>
      </div>
      <h1 class="admin-login-title">ورود به پنل مدیریت</h1>
      <p class="admin-login-subtitle">لطفاً مشخصات کاربری خود را وارد کنید.</p>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form id="adminLoginForm" method="POST" action="" novalidate>
        <div class="form-group">
          <label for="username">نام کاربری</label>
          <input type="text" id="username" name="username" required autocomplete="username"
            placeholder="admin" />
        </div>
        <div class="form-group">
          <label for="password">رمز عبور</label>
          <input type="password" id="password" name="password" required autocomplete="current-password"
            placeholder="admin" />
        </div>
        <button type="submit" id="loginBtn">ورود</button>
      </form>

      <div class="admin-login-footer">
        <p>درفکی — مشاور املاک حرفه‌ای</p>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      $('#adminLoginForm').on('submit', function (e) {
        const $btn = $('#loginBtn');
        const $username = $('#username');
        const $password = $('#password');

        // Basic client-side validation
        if (!$username.val().trim() || !$password.val()) {
          e.preventDefault();
          alert('لطفاً نام کاربری و رمز عبور را وارد کنید.');
          return;
        }

        $btn.prop('disabled', true).text('در حال ورود...');
      });
    });
  </script>
</body>

</html>
