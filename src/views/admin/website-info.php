<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Website Information
 *
 * Edits website content displayed on the public website:
 * Title, Header subtitle, Footer subtitle, Hero title, Hero subtitle,
 * About me (rich content), Divar merchant ID.
 * Values are stored in MySQL (website_info table).
 * All fields are rendered server-side — no AJAX.
 */

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
requireAdmin();

$DATA_TABLE = 'website_info';

$fields = [
    'title'            => '',
    'header_subtitle'  => '',
    'footer_subtitle'  => '',
    'hero_title'       => '',
    'hero_subtitle'    => '',
    'about_me'         => '',
    'divar_merchant_id' => '',
];

// Load existing values from database
try {
    $pdo = dorfakiDb();
    $stmt = $pdo->prepare('SELECT * FROM website_info WHERE id = 1');
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        foreach ($fields as $key => $default) {
            $fields[$key] = $row[$key] ?? $default;
        }
    }
} catch (PDOException $e) {
    $loadError = 'خطا در بارگذاری اطلاعات از پایگاه داده: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$message   = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $key => $default) {
        $fields[$key] = trim($_POST[$key] ?? '');
    }

    try {
        $pdo = dorfakiDb();
        $stmt = $pdo->prepare('
            UPDATE website_info
            SET title = :title,
                header_subtitle = :header_subtitle,
                footer_subtitle = :footer_subtitle,
                hero_title = :hero_title,
                hero_subtitle = :hero_subtitle,
                about_me = :about_me,
                divar_merchant_id = :divar_merchant_id
            WHERE id = 1
        ');
        $stmt->execute([
            ':title'            => $fields['title'] === '' ? null : $fields['title'],
            ':header_subtitle'  => $fields['header_subtitle'] === '' ? null : $fields['header_subtitle'],
            ':footer_subtitle'  => $fields['footer_subtitle'] === '' ? null : $fields['footer_subtitle'],
            ':hero_title'       => $fields['hero_title'] === '' ? null : $fields['hero_title'],
            ':hero_subtitle'    => $fields['hero_subtitle'] === '' ? null : $fields['hero_subtitle'],
            ':about_me'         => $fields['about_me'] === '' ? null : $fields['about_me'],
            ':divar_merchant_id'=> $fields['divar_merchant_id'] === '' ? null : $fields['divar_merchant_id'],
        ]);

        $message      = 'اطلاعات وب‌سایت با موفقیت ذخیره شد.';
        $messageType  = 'success';

        // Reload values after save
        $stmt = $pdo->prepare('SELECT * FROM website_info WHERE id = 1');
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            foreach ($fields as $key => $default) {
                $fields[$key] = $row[$key] ?? $default;
            }
        }
    } catch (PDOException $e) {
        $message      = 'خطا در ذخیره‌سازی: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $messageType  = 'error';
    }
}

$pageTitle = 'اطلاعات وب‌سایت';
$pageSubtitle = 'محتوای سایت را ویرایش کنید. تغییرات فوری در سایت نمایش داده می‌شود.';
?>
<?php require_once __DIR__ . '/layout.php'; ?>

<?php if (isset($loadError)): ?>
    <div class="admin-message alert-error p-4 rounded-xl text-sm mb-6 text-right border shadow-sm">
        <?= $loadError ?>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="admin-message alert-<?= $messageType ?> p-4 rounded-xl text-sm mb-6 text-right border shadow-sm">
        <?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="admin-card max-w-3xl">
    <div class="admin-card-header flex items-center gap-3 mb-6 text-right">
        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg">
            🌐
        </div>
        <div>
            <h2 class="text-xl font-bold text-white">اطلاعات وب‌سایت</h2>
            <p class="text-xs text-gray-400">تنظیمات محتوای سایت</p>
        </div>
    </div>

    <form id="websiteInfoForm" method="POST" action="" class="space-y-6">

        <!-- Title -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="title">
                <span>عنوان سایت</span>
            </label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($fields['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <p class="text-xs text-gray-500 mt-1 text-right">نمایش داده می‌شود در هدر و فوتر سایت.</p>
        </div>

        <!-- Header subtitle -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="header_subtitle">
                <span>زیرنویس هدر</span>
            </label>
            <input type="text" id="header_subtitle" name="header_subtitle"
                   value="<?= htmlspecialchars($fields['header_subtitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <p class="text-xs text-gray-500 mt-1 text-right">نمایش داده می‌شود در بالای هدر سایت.</p>
        </div>

        <!-- Footer subtitle -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="footer_subtitle">
                <span>زیرنویس فوتر</span>
            </label>
            <input type="text" id="footer_subtitle" name="footer_subtitle"
                   value="<?= htmlspecialchars($fields['footer_subtitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <p class="text-xs text-gray-500 mt-1 text-right">نمایش داده می‌شود در فوتر سایت.</p>
        </div>

        <!-- Hero title -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="hero_title">
                <span>عنوان هیرو</span>
            </label>
            <input type="text" id="hero_title" name="hero_title"
                   value="<?= htmlspecialchars($fields['hero_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <p class="text-xs text-gray-500 mt-1 text-right">عنوان اصلی بخش هیرو صفحه اصلی.</p>
        </div>

        <!-- Hero subtitle -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="hero_subtitle">
                <span>زیرنویس هیرو</span>
            </label>
            <input type="text" id="hero_subtitle" name="hero_subtitle"
                   value="<?= htmlspecialchars($fields['hero_subtitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <p class="text-xs text-gray-500 mt-1 text-right">زیرنویس بخش هیرو صفحه اصلی.</p>
        </div>

        <!-- About me (rich content) -->
        <div class="form-field">
            <label class="admin-field-label block text-right mb-2" for="about_me">
                <span>درباره من</span>
            </label>
            <div class="about-me-editor border border-gray-600 rounded-lg bg-gray-900/50 p-2">
                <div class="about-me-toolbar flex gap-1 mb-2 p-1.5 bg-gray-800/50 rounded border border-gray-700/50">
                    <button type="button" onclick="formatText('bold')" title="Bold" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">B</button>
                    <button type="button" onclick="formatText('italic')" title="Italic" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">I</button>
                    <button type="button" onclick="formatText('underline')" title="Underline" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">U</button>
                    <button type="button" onclick="formatText('insertUnorderedList')" title="List" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">⚫</button>
                    <button type="button" onclick="formatText('insertOrderedList')" title="Numbered List" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">⚪</button>
                    <button type="button" onclick="formatText('createLink')" title="Link" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-gray-300">🔗</button>
                    <button type="button" onclick="formatText('foreColor', '#fca5a5')" title="Red Color" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-700 text-red-400">A</button>
                </div>
                <div contenteditable="true" id="aboutMeEditor"
                    class="min-h-[300px] break-words text-gray-300 leading-relaxed outline-none p-3 text-right"
                    spellcheck="false"><?= htmlspecialchars($fields['about_me'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                <textarea id="about_me" name="about_me" class="hidden"><?= htmlspecialchars($fields['about_me'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                <p class="text-xs text-gray-500 mt-1 text-right">محتوای بخش «درباره من» در صفحه اصلی.</p>
            </div>
        </div>

        <!-- Divar merchant ID -->
        <div class="form-field">
            <label class="admin-field-label flex items-center gap-2 text-right" for="divar_merchant_id">
                <span>شناسه تاجر دیوار</span>
            </label>
            <input type="text" id="divar_merchant_id" name="divar_merchant_id"
                   value="<?= htmlspecialchars($fields['divar_merchant_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                   
                   class="admin-input" />
            <div class="bg-red-900/20 border border-red-700 text-red-400 p-3 rounded-lg text-sm mt-2 text-right">
                ⚠️ <strong>هشدار:</strong> تغییر این شناسه ممکن است منجر به فقدان داده‌های آگهی‌های دیوار شود.
                این مقدار آخری بخش آدرس <code>https://api.divar.ir/v8/premium-user/web/business/brand-landing/FFIEsACX</code> است.
            </div>
        </div>

        <button type="submit" class="admin-btn w-full py-3">
            ذخیره تغییرات
        </button>
    </form>
</div>

<script>
    // Sync contenteditable div to hidden textarea before submit
    document.getElementById('websiteInfoForm').addEventListener('submit', function () {
        const editor = document.getElementById('aboutMeEditor');
        const textarea = document.getElementById('about_me');
        if (editor && textarea) {
            textarea.value = editor.innerHTML;
        }
    });

    // Rich text formatting for contenteditable div
    function formatText(command, value = null) {
        const editor = document.getElementById('aboutMeEditor');
        editor.focus();
        document.execCommand(command, false, value);
    }
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
