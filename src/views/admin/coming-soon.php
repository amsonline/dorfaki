<?php
declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
requireAdmin();

$pageTitle = $pageTitle ?? 'به زودی';
$pageSubtitle = $pageSubtitle ?? 'این بخش در حال توسعه است و به زودی در دسترس خواهد بود.';
$section = $section ?? 'بخش';
?>
<?php require_once __DIR__ . '/layout.php'; ?>

<div class="coming-soon">
  <div class="coming-soon-icon">🚧</div>
  <div class="badge">به زودی</div>
  <h2><?php echo htmlspecialchars($section, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
  <p><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
