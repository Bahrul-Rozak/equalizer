<?php
// login.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect jika sudah login
redirectIfLoggedIn();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp = sanitize($_POST['whatsapp_number'] ?? '');
    
    // Validasi format WhatsApp (minimal 10 digit, hanya angka)
    $whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp);
    
    if (strlen($whatsapp_clean) < 10) {
        $error = "❌ Nomor WhatsApp tidak valid. Minimal 10 digit angka.";
    } else {
        // Cari user berdasarkan WhatsApp number
        $user = dbFetch("SELECT * FROM users WHERE whatsapp_number = ? AND is_active = TRUE", [$whatsapp_clean]);
        
        if ($user) {
            // Cek status akun
            if ($user['status'] === 'kicked') {
                $error = "🚫 Akun lo di-KICK karena kuota bolos habis. Hubungi admin via WhatsApp.";
            } elseif ($user['status'] === 'suspended') {
                $error = "⚠️ Akun lo sedang disuspend. Hubungi admin untuk informasi lebih lanjut.";
            } elseif ($user['subscription_end'] && strtotime($user['subscription_end']) < time() && $user['status'] !== 'trial') {
                $error = "⏰ Subscription lo sudah habis. Perpanjang sekarang!";
            } else {
                // ✅ Login berhasil - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['whatsapp_number'] = $user['whatsapp_number'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['status'] = $user['status'];
                
                // Tambah ke live feed
                addToLiveFeed($user['id'], 'user_login', "{$user['full_name']} Login ke dashboard", false);
                
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            // User tidak ditemukan - tampilkan instruksi
            $settings = getSettings();
            $admin_wa = $settings['admin_whatsapp'] ?? '6281234567890';
            
            $error = "🔍 Nomor WhatsApp belum terdaftar.";
            $success = "
                <div class='mt-3 p-3 bg-gray-800 rounded border border-gray-600 text-sm'>
                    <p class='font-semibold mb-2'><i class='fas fa-info-circle text-blue-400'></i> Cara Daftar:</p>
                    <ol class='list-decimal list-inside space-y-1 text-gray-300'>
                        <li>Klik tombol di bawah untuk chat admin</li>
                        <li>Kirim: <strong>\"Saya mau join Warrior Produktif\"</strong></li>
                        <li>Sertakan: Nama Lengkap & Nomor WhatsApp lo</li>
                        <li>Admin akan verifikasi & kasih akses grup Discord</li>
                        <li>Setelah itu, login lagi di halaman ini</li>
                    </ol>
                    <a href=\"" . waLink($admin_wa, "Halo admin, saya mau join Warrior Produktif. Nama: [Nama Lengkap], WA: $whatsapp_clean") . "\" 
                       target=\"_blank\"
                       class='inline-flex items-center mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 rounded font-medium transition'>
                        <i class='fab fa-whatsapp mr-2'></i> Chat Admin Sekarang
                    </a>
                </div>
            ";
        }
    }
}
?>

<?php $page_title = 'Login'; require_once 'includes/header.php'; ?>

<!-- Login Page -->
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gradient mb-2">WARRIOR PRODUKTIF</h1>
            <p class="text-gray-400">Login dengan nomor WhatsApp terdaftar</p>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-blue-900/50 border border-blue-700 rounded text-blue-200 text-sm">
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <div class="bg-gray-800 rounded-xl p-6 shadow-xl border border-gray-700">
            <form method="POST" class="space-y-4">
                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium mb-2">
                        <i class="fab fa-whatsapp text-green-500 mr-1"></i> Nomor WhatsApp *
                    </label>
                    <input type="tel" 
                           name="whatsapp_number" 
                           id="whatsapp_number"
                           value="<?= sanitize($_POST['whatsapp_number'] ?? '') ?>"
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Gunakan nomor yang sama saat daftar via chat admin</p>
                </div>
                
                <button type="submit" 
                        class="w-full py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-lg font-semibold transition transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fas fa-sign-in-alt mr-2"></i> MASUK DASHBOARD
                </button>
            </form>
            
            <div class="mt-6 pt-4 border-t border-gray-700 text-center text-sm text-gray-400">
                <p>Belum punya akun? <a href="index.php" class="text-orange-400 hover:underline">Lihat paket membership</a></p>
            </div>
        </div>
        
        <!-- Security Note -->
        <div class="mt-6 text-center text-xs text-gray-500">
            <i class="fas fa-shield-alt mr-1"></i> Login aman tanpa password • Data terenkripsi
        </div>
        
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>