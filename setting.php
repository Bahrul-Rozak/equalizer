<?php
// setting.php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
requireAdmin();

$success = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings_data = [
        'admin_whatsapp' => preg_replace('/[^0-9]/', '', $_POST['admin_whatsapp']),
        'youtube_video_url' => sanitize($_POST['youtube_video_url']),
        'pricing_monthly' => (int)$_POST['pricing_monthly'],
        'pricing_quarterly' => (int)$_POST['pricing_quarterly'],
        'pricing_yearly' => (int)$_POST['pricing_yearly'],
        'skip_quota_monthly' => (int)$_POST['skip_quota_monthly'],
        'skip_quota_quarterly' => (int)$_POST['skip_quota_quarterly'],
        'skip_quota_yearly' => (int)$_POST['skip_quota_yearly'],
        'trial_days' => (int)$_POST['trial_days'],
        'footer_text' => sanitize($_POST['footer_text'])
    ];
    
    foreach ($settings_data as $key => $val) {
        dbQuery("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()", [$key, $val, $val]);
    }
    $success = "✅ Pengaturan berhasil disimpan!";
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $current = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $admin = dbFetch("SELECT password_hash FROM admin WHERE id = ?", [$_SESSION['admin_id']]);
    
    if (!password_verify($current, $admin['password_hash'])) {
        $error = "❌ Password lama salah!";
    } elseif (strlen($new_pass) < 6) {
        $error = "❌ Password baru minimal 6 karakter!";
    } elseif ($new_pass !== $confirm) {
        $error = "❌ Konfirmasi password tidak cocok!";
    } else {
        resetAdminPassword($_SESSION['admin_id'], $new_pass);
        $success = "🔑 Password berhasil diubah!";
    }
}

$current_settings = getSettings();
$page_title = 'Settings';
?>

<?php require_once 'includes/header.php'; ?>
<div class="min-h-screen bg-gray-900 px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold"><i class="fas fa-cogs mr-2"></i>Admin Settings</h1>
            <div class="flex gap-2">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
                <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm transition"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-900/50 border border-green-700 rounded text-green-200 text-sm"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- General Settings -->
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                <h2 class="text-lg font-bold mb-4 border-b border-gray-700 pb-2">📱 Kontak & Media</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">WhatsApp Admin (tanpa + atau spasi)</label>
                        <input type="text" name="admin_whatsapp" value="<?= $current_settings['admin_whatsapp'] ?? '' ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">YouTube Embed URL</label>
                        <input type="text" name="youtube_video_url" value="<?= $current_settings['youtube_video_url'] ?? '' ?>" placeholder="https://youtube.com/embed/..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Pricing Settings -->
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                <h2 class="text-lg font-bold mb-4 border-b border-gray-700 pb-2">💰 Pricing & Kuota Bolos</h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Trial Days</label>
                        <input type="number" name="trial_days" value="<?= $current_settings['trial_days'] ?? 3 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Harga Monthly</label>
                        <input type="number" name="pricing_monthly" value="<?= $current_settings['pricing_monthly'] ?? 15000 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuota Bolos Monthly</label>
                        <input type="number" name="skip_quota_monthly" value="<?= $current_settings['skip_quota_monthly'] ?? 3 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Harga Quarterly</label>
                        <input type="number" name="pricing_quarterly" value="<?= $current_settings['pricing_quarterly'] ?? 40000 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuota Bolos Quarterly</label>
                        <input type="number" name="skip_quota_quarterly" value="<?= $current_settings['skip_quota_quarterly'] ?? 6 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Harga Yearly</label>
                        <input type="number" name="pricing_yearly" value="<?= $current_settings['pricing_yearly'] ?? 129000 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuota Bolos Yearly</label>
                        <input type="number" name="skip_quota_yearly" value="<?= $current_settings['skip_quota_yearly'] ?? 20 ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Footer Text</label>
                    <input type="text" name="footer_text" value="<?= $current_settings['footer_text'] ?? '© 2026 All rights reserved.' ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
            </div>

            <button type="submit" name="update_settings" class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-lg font-bold transition">💾 SIMPAN PENGATURAN</button>
        </form>

        <!-- Password Reset Form -->
        <form method="POST" class="mt-8 bg-gray-800 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-bold mb-4 border-b border-gray-700 pb-2">🔑 Reset Password Admin</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="password" name="current_password" placeholder="Password Lama" required class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                <input type="password" name="new_password" placeholder="Password Baru" required class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none md:col-span-2">
            </div>
            <button type="submit" name="reset_password" class="mt-4 px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition">Ubah Password</button>
        </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>