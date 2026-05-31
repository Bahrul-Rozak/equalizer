<?php
// admin_login.php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $admin = dbFetch("SELECT * FROM admin WHERE username = ?", [$username]);
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "❌ Username atau password salah!";
    }
}
?>
<?php $page_title = 'Admin Login'; require_once 'includes/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gradient">🔐 Admin Login</h1>
            <p class="text-gray-400 text-sm mt-1">Akses Panel Warrior Produktif</p>
        </div>
        
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
            </div>
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-lg font-bold transition">
                <i class="fas fa-sign-in-alt mr-2"></i> MASUK
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="login.php" class="text-sm text-gray-400 hover:underline">← Kembali ke Login User</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>