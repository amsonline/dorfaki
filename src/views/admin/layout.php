<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Layout Shell
 *
 * Wraps admin pages with a top header and a sidebar containing the
 * four admin sections: Files, Contact, Website info, Settings.
 *
 * Usage:
 *   require_once 'layout.php';   // sets $pageTitle and renders head + header + sidebar open
 *   ... page content ...
 *   require_once 'layout-footer.php'; // closes the layout
 */

require_once __DIR__ . '/../../auth.php';
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF'] ?? '/admin/dashboard');
$adminUser = getAdminUser() ?? 'admin';
// Body class per page (e.g. admin-page-files) for section-specific styling
$bodyClass = 'admin-page-' . str_replace('.php', '', $currentPage);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($pageTitle ?? 'پنل مدیریت', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> | درفکی</title>
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

<body class="admin-app <?php echo $bodyClass; ?>">
  <div class="admin-header">
    <a href="/admin/dashboard" class="admin-brand">
      <mark>درفکی</mark> مدیریت
    </a>
    <div class="admin-user">
      <span><?php echo htmlspecialchars($adminUser, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
      <a href="/admin/logout" class="admin-logout-btn">خروج از حساب</a>
    </div>
  </div>

  <div class="admin-main">
    <aside class="admin-sidebar">
      <div class="admin-sidebar-header">پنل مدیریت</div>
      <ul class="admin-nav">
        <li>
          <a href="/admin/dashboard" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span> داشبورد
          </a>
        </li>
        <li>
          <a href="/admin/files" class="<?php echo $currentPage === 'files.php' ? 'active' : ''; ?>">
            <span class="icon">📁</span> فایل‌ها
          </a>
        </li>
        <li>
          <a href="/admin/contact" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">
            <span class="icon">📩</span> تماس
          </a>
        </li>
        <li>
          <a href="/admin/website-info" class="<?php echo $currentPage === 'website-info.php' ? 'active' : ''; ?>">
            <span class="icon">🌐</span> اطلاعات وب‌سایت
          </a>
        </li>
        <li>
          <a href="/admin/settings" class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
            <span class="icon">⚙️</span> تنظیمات
          </a>
        </li>
      </ul>
      <div class="admin-sidebar-footer">
        Dorfaki Admin &middot; v1.0
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-page-header">
        <h1 class="admin-page-title"><?php echo htmlspecialchars($pageTitle ?? 'پنل مدیریت', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
        <p class="admin-page-subtitle"><?php echo htmlspecialchars($pageSubtitle ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
      </div>

      <?php // Page content is injected here by the including file ?>
