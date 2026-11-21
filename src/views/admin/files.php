<?php
declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
requireAdmin();

$pageTitle = '';
$pageSubtitle = 'مدیریت آگهی‌ها';
$section = 'فایل‌ها';

// Get database connection
$pdo = dorfakiDb();

// Fetch all ads with their preserve status
$stmt = $pdo->prepare('SELECT * FROM divar_ads ORDER BY updated_at DESC');
$stmt->execute();
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle re-fetch request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refetch'])) {
    header('Location: /admin/files?refresh=1');
    exit;
}

// Handle preserve toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_preserve'])) {
    // Only handle if NOT an AJAX request going to preserve_ad.php
    // AJAX requests have different handling
    $adId = $_POST['ad_id'] ?? '';
    $preserve = isset($_POST['preserve']) ? 1 : 0;
    
    if (!empty($adId)) {
        $updateStmt = $pdo->prepare(
            'UPDATE divar_ads SET preserve = :preserve WHERE divar_ad_id = :ad_id'
        );
        $updateStmt->execute([':preserve' => $preserve, ':ad_id' => $adId]);
    }
    // Redirect for regular form submission (non-AJAX)
    header('Location: /admin/files');
    exit;
}
?>

<?php require_once __DIR__ . '/layout.php'; ?>

<div class="admin-page-content">
    <div class="flex justify-between items-center mb-6">
        <h2 class="admin-page-title text-2xl font-bold">فایل‌ها</h2>
        <div class="flex items-center gap-3">
            <button id="refetchBtn" class="btn btn-primary btn-sm">
                <i class="icon">🔄</i> بازیابی مجدد
            </button>
        </div>
    </div>
    
    <p class="admin-page-subtitle mb-6"><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    
    <?php if (empty($ads)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <h3>آگهی‌ای یافت نشود</h3>
            <p>هنوز آگهی‌ای در پایگاه داده موجود نیست. برای بارگذاری آگهی‌ها، دکمه «بازیابی مجدد» را بزنید.</p>
        </div>
    <?php else: ?>
        <div class="ads-table-wrapper">
            <table class="ads-table w-full min-w-[800px]">
                <thead>
                    <tr>
                        <th class="text-left p-4 border-b">عنوان</th>
                        <th class="text-left p-4 border-b">قیمت</th>
                        <th class="text-left p-4 border-b">موقعیت</th>
                        <th class="text-left p-4 border-b">تصویر</th>
                        <th class="text-left p-4 border-b">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ads as $ad): ?>
                        <tr class="ads-row">
                            <td class="ad-title p-4">
                                <p class="font-medium"><?php echo htmlspecialchars($ad['title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($ad['divar_ad_id'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                            </td>
                            <td class="ad-price p-4">
                                <p><?php echo htmlspecialchars($ad['price'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                            </td>
                            <td class="ad-location p-4">
                                <p><?php echo htmlspecialchars($ad['location'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                            </td>
                            <td class="ad-image p-4">
                                <?php if ($ad['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($ad['image_url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="광고 이미지" class="ad-img" />
                                <?php else: ?>
                                    <span class="no-image">تصویر موجود نیست</span>
                                <?php endif; ?>
                            </td>
                            <td class="ad-actions p-4">
                                <form method="POST" class="preserve-form" data-api="/preserve_ad.php">
                                    <input type="hidden" name="ad_id" value="<?php echo htmlspecialchars($ad['divar_ad_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <input type="hidden" name="preserve" value="<?php echo $ad['preserve'] ? '1' : '0'; ?>">
                                    <button type="submit" name="toggle_preserve" class="preserve-btn <?php echo $ad['preserve'] ? 'preserved' : ''; ?>">
                                        <?php echo $ad['preserve'] ? 'حفاظت‌شده' : 'حفاظتPending'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Re-fetch confirmation modal -->
<div id="refetchModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50" role="dialog" aria-modal="true">
    <div class="bg-white rounded-lg p-8 max-w-md w-full transform scale-95 opacity-0 transition-all duration-300" role="alertdialog">
        <h3 class="text-xl font-bold mb-4">بارگذاری مجدد آگهی‌ها</h3>
        <p class="text-gray-600 mb-6">آیاreally می‌خواهید آگهی‌ها را ازpanel Divar بارگیری کنید؟ این کار ممکن است کمی زمان ببرد.</p>
        <div class="flex gap-3">
            <button id="cancelRefetch" class="btn btn-secondary">انصراف</button>
            <button id="confirmRefetch" class="btn btn-primary">بله، بارگذاری کن</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Re-fetch button
    const refetchBtn = document.getElementById('refetchBtn');
    const modal = document.getElementById('refetchModal');
    const cancelRefetch = document.getElementById('cancelRefetch');
    const confirmRefetch = document.getElementById('confirmRefetch');
    
    if (refetchBtn) {
        refetchBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.opacity = '1';
            modal.style.transform = 'scale(1)';
        });
    }
    
    if (cancelRefetch) {
        cancelRefetch.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.opacity = '0';
            modal.style.transform = 'scale(95)';
        });
    }
    
    if (confirmRefetch) {
        confirmRefetch.addEventListener('click', function() {
            // Submit form to trigger re-fetch
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/files';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'refetch';
            input.value = '1';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Close modal on overlay click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.opacity = '0';
                modal.style.transform = 'scale(95)';
            }
        });
    }
    
    // Preserve toggle form
    const preserveForms = document.querySelectorAll('.preserve-form');
    
    preserveForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(form.dataset.api, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update preserve button text based on new status
                    const btn = form.querySelector('.preserve-btn');
                    if (data.preserve) {
                        btn.textContent = 'حفاظت‌شده';
                        btn.classList.add('preserved');
                    } else {
                        btn.textContent = 'حفاظتPending';
                        btn.classList.remove('preserved');
                    }
                    alert('وضعیت با موفقیت به‌روزرسانی شد');
                    location.reload();
                } else {
                    alert('خطا در به‌روزرسانی وضعیت: ' + (data.message || 'نامشخص'));
                }
            })
            .catch(error => {
                alert('خطا در ارتباط با سرور');
                console.error('Error:', error);
            });
        });
    });
});
</script>

<!-- Admin CSS and buttons -->
<style>
    .ads-table { width: 100%; border-collapse: collapse; }
    .ads-table th, .ads-table td { padding: 12px 8; border: 1px solid #e2e8f0; }
    .ads-table th { background-color: #f8fafc; font-weight: 600; }
    
    /* Mobile responsive: enable horizontal scroll on small screens */
    @media (max-width: 768px) {
        .ads-table_wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .ads-table { min-width: 600px; }
    }
    
    .ads-row { transition: background-color 0.2s; }
    .ads-row:hover { background-color: #f1f5f9; color: #0a0a0a }
    
    .empty-state { text-align: center; padding: 40px 20px; }
    .empty-state-icon { font-size: 48px; margin-bottom: 12px; color: #cbd5e1; }
    .empty-state h3 { margin-bottom: 8px; font-size: 18px; }
    .empty-state p { color: #64748b; font-size: 14px; }
    
    .ad-image { max-width: 80px; max-height: 60px; }
    .ad-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
    .no-image { color: #64748b; font-size: 12px; }
    
    .ad-actions { display: flex; gap: 8px; }
    .preserve-btn { padding: 6px 12px; font-size: 12px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .preserve-btn.preserved { background-color: #d1fae5; border-color: #a7f3d0; color: #059669; }
    .preserve-btn:not(.preserved) { background-color: #fff; }
    
    .preserve-form { display: inline; }
</style>
