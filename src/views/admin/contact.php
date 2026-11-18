<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Contact Information
 *
 * Edits the contact details displayed on the public website:
 * Tel, Address, Email, Telegram ID, Whatsapp link.
 * Values are stored in MySQL (contact_info table).
 * Fields left blank are hidden on the website.
 */

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
requireAdmin();

$message   = '';
$messageType = '';

// Default empty values
$tel        = '';
$address    = '';
$email      = '';
$telegram   = '';
$whatsapp   = '';

// Load existing values from database
try {
    $pdo = dorfakiDb();
    $stmt = $pdo->prepare('SELECT tel, address, email, telegram, whatsapp FROM contact_info WHERE id = 1');
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $tel       = $row['tel']        ?? '';
        $address   = $row['address']    ?? '';
        $email     = $row['email']      ?? '';
        $telegram  = $row['telegram']   ?? '';
        $whatsapp  = $row['whatsapp']   ?? '';
    }
} catch (PDOException $e) {
    $message      = 'خطا در اتصال به پایگاه داده: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $messageType  = 'error';
}

// Handle form submission (server-side)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tel       = trim($_POST['tel']        ?? '');
    $address   = trim($_POST['address']    ?? '');
    $email     = trim($_POST['email']      ?? '');
    $telegram  = trim($_POST['telegram']   ?? '');
    $whatsapp  = trim($_POST['whatsapp']   ?? '');

    try {
        $pdo = dorfakiDb();
        $stmt = $pdo->prepare('
            UPDATE contact_info
            SET tel = :tel,
                address = :address,
                email = :email,
                telegram = :telegram,
                whatsapp = :whatsapp
            WHERE id = 1
        ');
        $stmt->execute([
            ':tel'       => $tel === '' ? null : $tel,
            ':address'   => $address === '' ? null : $address,
            ':email'     => $email === '' ? null : $email,
            ':telegram'  => $telegram === '' ? null : $telegram,
            ':whatsapp'  => $whatsapp === '' ? null : $whatsapp,
        ]);

        $message      = 'اطلاعات تماس با موفقیت ذخیره شد.';
        $messageType  = 'success';
    } catch (PDOException $e) {
        $message      = 'خطا در ذخیره‌سازی: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $messageType  = 'error';
    }
}

$pageTitle = 'تماس';
$pageSubtitle = 'اطلاعات تماس نمایش داده شده در سایت را ویرایش کنید. فیلدهایی خالی گذاشته شده در سایت نمایش داده نمی‌شوند.';
?>
<?php require_once __DIR__ . '/layout.php'; ?>

<?php if ($message): ?>
    <div class="admin-message alert-<?= $messageType ?> p-4 rounded-xl text-sm mb-6 text-right border shadow-sm">
        <?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="admin-card max-w-2xl">
    <div class="admin-card-header flex items-center gap-3 mb-6 text-right">
        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg">
            📩
        </div>
        <div>
            <h2 class="text-xl font-bold text-white">تنظیمات تماس</h2>
            <p class="text-xs text-gray-400">تنظیمات اطلاعات تماس در بوم سایت</p>
        </div>
    </div>

    <form id="contactSettingsForm" method="POST" action="" class="space-y-5">
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="tel">
                <span>تلفن</span>
            </label>
            <input type="tel" id="tel" name="tel"
                   value="<?= htmlspecialchars($tel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
        </div>

        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="address">
                <span>آدرس</span>
            </label>
            <input type="text" id="address" name="address"
                   value="<?= htmlspecialchars($address, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
        </div>

        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="email">
                <span>ایمیل</span>
            </label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
        </div>

        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="telegram">
                <span>تلگرام</span>
            </label>
            <input type="text" id="telegram" name="telegram"
                   value="<?= htmlspecialchars($telegram, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
        </div>

        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="whatsapp">
                <span>واتساپ</span>
            </label>
            <input type="url" id="whatsapp" name="whatsapp"
                   value="<?= htmlspecialchars($whatsapp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
        </div>

        <button type="submit" class="admin-btn w-full py-3">
            ذخیره تغییرات
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
