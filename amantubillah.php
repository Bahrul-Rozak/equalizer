<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    
    $cookieParams = session_get_cookie_params();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}




/**
 * Get real client IP address (behind proxy? adapt if needed)
 */
function getClientIP() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Ensure login_attempts table exists (auto-create)
 */
function createAttemptsTableIfNotExists() {
    global $pdo; 
    if (!$pdo) return;
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                attempt_time DATETIME NOT NULL,
                username VARCHAR(255) DEFAULT NULL,
                INDEX idx_ip_time (ip_address, attempt_time)
            )";
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        
        error_log("Rate limiter table creation: " . $e->getMessage());
    }
}

/**
 * Check if IP is rate limited (max 5 attempts per 15 minutes)
 * Returns true if allowed, false if blocked.
 */
function isRateLimited($ip) {
    global $pdo;
    if (!$pdo) return false; 
    $window = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > ?");
    $stmt->execute([$ip, $window]);
    $attempts = (int)$stmt->fetchColumn();
    return $attempts >= 5;
}

/**
 * Record a failed login attempt (IP + optional username)
 */
function recordFailedAttempt($ip, $username = '') {
    global $pdo;
    if (!$pdo) return;
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time, username) VALUES (?, NOW(), ?)");
    $stmt->execute([$ip, substr($username, 0, 255)]);
}

/**
 * Clear all attempts for an IP after successful login
 */
function clearLoginAttempts($ip) {
    global $pdo;
    if (!$pdo) return;
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}

/**
 * Clean up old attempts (older than 1 day) – optional, called occasionally
 */
function cleanupOldAttempts() {
    global $pdo;
    if (!$pdo) return;
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL 1 DAY");
    $stmt->execute();
}

/**
 * Generate or retrieve CSRF token
 */
function getCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send security headers
 */
function sendSecurityHeaders() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}




sendSecurityHeaders();
createAttemptsTableIfNotExists();
cleanupOldAttempts();          
$csrf_token = getCSRFToken();  




$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getClientIP();

    
    if (isRateLimited($ip)) {
        $error = "Terlalu banyak percobaan login. Silakan coba lagi setelah 15 menit.";
    }
    
    elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid. Muat ulang halaman.";
    }
    
    elseif (!empty($_POST['website'])) {
        
        sleep(2);
        $error = "Login gagal."; 
    }
    else {
        $username = sanitize(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        
        if (strlen($username) < 3 || strlen($username) > 50) {
            $error = "Username tidak valid.";
        } elseif (strlen($password) < 1) {
            $error = "Password tidak boleh kosong.";
        } else {
            
            $admin = dbFetch("SELECT * FROM admin WHERE username = ?", [$username]);

            if ($admin && password_verify($password, $admin['password_hash'])) {
                
                clearLoginAttempts($ip);
                session_regenerate_id(true); 
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: admin_dashboard.php");
                exit();
            } else {
                
                recordFailedAttempt($ip, $username);
                $error = "Username atau password salah!";
                
                usleep(rand(200000, 500000));
            }
        }
    }
}


if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
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
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <!-- Honeypot field (hidden from real users) -->
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
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