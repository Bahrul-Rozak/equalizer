<?php
// includes/functions.php
// Kumpulan fungsi bantu biar codingan kita DRY (Don't Repeat Yourself)

session_start();

// 🔐 Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['whatsapp_number']);
}

// 🔐 Cek apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// 🔐 Redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// 🔐 Redirect jika sudah login
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: user_dashboard.php");
        exit();
    }
}

// 🔐 Redirect admin jika belum login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// 🧹 Sanitize input user (mencegah XSS)
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// 🔗 Generate WhatsApp link dengan pesan otomatis
function waLink($number, $message) {
    $number = preg_replace('/[^0-9]/', '', $number); // Hanya angka
    $message = urlencode($message);
    return "https://wa.me/{$number}?text={$message}";
}

// 📅 Format tanggal Indonesia
function formatDateIndo($date, $format = 'd M Y') {
    if (!$date) return '-';
    $timestamp = is_string($date) ? strtotime($date) : $date;
    return date($format, $timestamp);
}

// 📊 Hitung sisa hari subscription
function getRemainingDays($end_date) {
    if (!$end_date) return 0;
    $today = new DateTime();
    $end = new DateTime($end_date);
    $interval = $today->diff($end);
    return $interval->invert ? 0 : $interval->days;
}

// 🎯 Hitung streak (hari berturut-turut lapor)
function calculateStreak($user_id) {
    $pdo = getDBConnection();
    
    // Ambil semua tanggal laporan yang completed, urut descending
    $sql = "SELECT report_date FROM daily_reports 
            WHERE user_id = ? AND completion_status = 'completed_100' 
            ORDER BY report_date DESC";
    $reports = dbFetchAll($sql, [$user_id]);
    
    if (empty($reports)) return 0;
    
    $streak = 0;
    $expected_date = new DateTime(); // Hari ini
    
    foreach ($reports as $report) {
        $report_date = new DateTime($report['report_date']);
        
        // Jika tanggal sesuai dengan expected (berturut-turut)
        if ($report_date->format('Y-m-d') === $expected_date->format('Y-m-d')) {
            $streak++;
            $expected_date->modify('-1 day');
        } else {
            break; // Putus streak
        }
    }
    
    return $streak;
}

// 📝 Tambah entri ke live_feed
function addToLiveFeed($user_id, $action_type, $description, $is_public = true) {
    $sql = "INSERT INTO live_feed (user_id, action_type, action_description, is_public) 
            VALUES (?, ?, ?, ?)";
    dbQuery($sql, [$user_id, $action_type, $description, $is_public]);
}

// 🚨 Cek & update status user (auto kick logic - manual trigger)
function checkAndKickUser($user_id) {
    $user = dbFetch("SELECT * FROM users WHERE id = ?", [$user_id]);
    
    if (!$user) return false;
    
    // Jika kuota bolos habis dan belum di-kick
    if ($user['skip_count'] >= $user['skip_quota_total'] && $user['status'] !== 'kicked') {
        // Update user status
        dbQuery("UPDATE users SET status = 'kicked', is_active = FALSE, updated_at = NOW() WHERE id = ?", [$user_id]);
        
        // Tambah ke live feed
        addToLiveFeed($user_id, 'user_kicked', "{$user['full_name']} Di-kick otomatis: Kuota bolos habis");
        
        return true; // Berhasil di-kick
    }
    
    return false;
}

// 🔄 Reset password admin
function resetAdminPassword($admin_id, $new_password) {
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    dbQuery("UPDATE admin SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$hash, $admin_id]);
    return true;
}

// 📦 Ambil semua setting dari database
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        $rows = dbFetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = array_column($rows, 'setting_value', 'setting_key');
    }
    
    return $settings;
}

// 🎨 Helper: Get pricing info
function getPricingInfo() {
    $settings = getSettings();
    return [
        'trial' => ['name' => 'Trial', 'price' => 0, 'days' => (int)$settings['trial_days'], 'skip_quota' => 3],
        'monthly' => ['name' => 'Monthly', 'price' => (int)$settings['pricing_monthly'], 'days' => 30, 'skip_quota' => (int)$settings['skip_quota_monthly']],
        'quarterly' => ['name' => 'Quarterly', 'price' => (int)$settings['pricing_quarterly'], 'days' => 90, 'skip_quota' => (int)$settings['skip_quota_quarterly']],
        'yearly' => ['name' => 'Yearly', 'price' => (int)$settings['pricing_yearly'], 'days' => 365, 'skip_quota' => (int)$settings['skip_quota_yearly']],
    ];
}

// 📄 Pagination helper
function getPagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    $pagination = [];
    
    if ($total_pages <= 1) return '';
    
    $pagination[] = '<nav class="flex justify-center mt-6"><ul class="flex space-x-1">';
    
    // Previous
    if ($current_page > 1) {
        $pagination[] = '<li><a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="px-3 py-1 rounded border hover:bg-gray-100">&laquo;</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $pagination[] = '<li><span class="px-3 py-1 rounded bg-blue-600 text-white">' . $i . '</span></li>';
        } else {
            $pagination[] = '<li><a href="' . $base_url . '?page=' . $i . '" class="px-3 py-1 rounded border hover:bg-gray-100">' . $i . '</a></li>';
        }
    }
    
    // Next
    if ($current_page < $total_pages) {
        $pagination[] = '<li><a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="px-3 py-1 rounded border hover:bg-gray-100">&raquo;</a></li>';
    }
    
    $pagination[] = '</ul></nav>';
    
    return implode('', $pagination);
}
?>