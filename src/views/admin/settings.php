<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Settings
 *
 * Only allows changing the admin password.
 * Values are stored in MySQL (admin_users table).
 */

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
requireAdmin();

$message   = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password']     ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $message      = 'لطفاً تمام فیلدها را پر کنید.';
        $messageType  = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message      = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
        $messageType  = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message      = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.';
        $messageType  = 'error';
    } else {
        try {
            $pdo = dorfakiDb();

            // Verify current password
            $stmt = $pdo->prepare(
                'SELECT password_hash FROM admin_users WHERE username = :username LIMIT 1'
            );
            $stmt->execute([':username' => 'admin']);
            $row = $stmt->fetch();

            if ($row === false || !password_verify($currentPassword, $row['password_hash'] ?? '')) {
                $message      = 'رمز عبور فعلی اشتباه است.';
                $messageType  = 'error';
            } else {
                // Update password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'UPDATE admin_users SET password_hash = :password_hash WHERE username = :username'
                );
                $stmt->execute([
                    ':password_hash' => $newHash,
                    ':username'      => 'admin',
                ]);

                $message      = 'رمز عبور با موفقیت تغییر کرد.';
                $messageType  = 'success';
            }
        } catch (PDOException $e) {
            $message      = 'خطا در تغییر رمز عبور: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $messageType  = 'error';
        }
    }
}

$pageTitle = 'تنظیمات';
$pageSubtitle = 'تغییر رمز عبور پنل مدیریت.';
?>
<?php require_once __DIR__ . '/layout.php'; ?>

<?php if ($message): ?>
    <div class="admin-message alert-<?= $messageType ?> p-4 rounded-xl text-sm mb-6 text-right border shadow-sm">
        <?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="admin-card max-w-lg">
    <div class="admin-card-header flex items-center gap-3 mb-6 text-right">
        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg">
            ⚙️
        </div>
        <div>
            <h2 class="text-xl font-bold text-white">تنظیمات</h2>
            <p class="text-xs text-gray-400">تغییر رمز عبور پنل مدیریت</p>
        </div>
    </div>

    <form id="settingsForm" method="POST" action="" class="space-y-5">

        <!-- Current password -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="current_password">
                <span>رمز عبور فعلی</span>
            </label>
            <input type="password" id="current_password" name="current_password" required
                   class="admin-input" />
        </div>

        <!-- New password -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="new_password">
                <span>رمز عبور جدید</span>
            </label>
            <input type="password" id="new_password" name="new_password" required
                   
                   class="admin-input" />
        </div>

        <!-- Confirm new password -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="confirm_password">
                <span>تکرار رمز عبور جدید</span>
            </label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   class="admin-input" />
        </div>

        <button type="submit" class="admin-btn w-full py-3">
            تغییر رمز عبور
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
