<?php
// admin_dashboard.php - FIXED: Cek Bolos dengan Preview + Confirm + Excuse Feature
require_once 'config/database.php';
require_once 'includes/functions.php';
// session_start() sudah dipanggil di functions.php

requireAdmin();

$message = '';
$error = '';

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        // ➕ CREATE / UPDATE User (Unified)
        if ($action === 'save_user') {
            $uid = (int)($_POST['user_id'] ?? 0);
            $wa = preg_replace('/[^0-9]/', '', $_POST['whatsapp_number']);
            $name = sanitize($_POST['full_name']);
            $discord = sanitize($_POST['discord_username'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $type = $_POST['subscription_type'] ?? 'trial';
            $start = $_POST['subscription_start'] ?? date('Y-m-d');
            $end = $_POST['subscription_end'] ?? null;
            
            $pricing = getPricingInfo();
            $quota = $pricing[$type]['skip_quota'] ?? 3;
            
            if ($uid > 0) {
                dbQuery("UPDATE users SET 
                    whatsapp_number = ?, full_name = ?, discord_username = ?, 
                    status = ?, subscription_type = ?, subscription_start = ?, 
                    subscription_end = ?, skip_quota_total = ?, updated_at = NOW()
                    WHERE id = ?", 
                    [$wa, $name, $discord ?: null, $status, $type, $start, $end, $quota, $uid]);
                $message = "✅ Data user berhasil diupdate!";
                addToLiveFeed($uid, 'user_added', "Admin mengupdate data: $name");
            } else {
                dbQuery("INSERT INTO users (whatsapp_number, full_name, discord_username, status, subscription_type, subscription_start, subscription_end, skip_quota_total) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$wa, $name, $discord ?: null, $status, $type, $start, $end, $quota]);
                $new_user = dbFetch("SELECT id FROM users WHERE whatsapp_number = ?", [$wa]);
                $message = "✅ User baru berhasil ditambahkan!";
                addToLiveFeed($new_user['id'], 'user_added', "$name Ditambahkan admin via dashboard");
            }
            
        } 
        // 🗑️ DELETE User Permanently
        elseif ($action === 'delete_user') {
            $uid = (int)$_POST['user_id'];
            $user = dbFetch("SELECT full_name FROM users WHERE id = ?", [$uid]);
            dbQuery("DELETE FROM live_feed WHERE user_id = ?", [$uid]);
            dbQuery("DELETE FROM daily_reports WHERE user_id = ?", [$uid]);
            dbQuery("DELETE FROM skip_logs WHERE user_id = ?", [$uid]);
            dbQuery("DELETE FROM dreams WHERE user_id = ?", [$uid]);
            dbQuery("DELETE FROM users WHERE id = ?", [$uid]);
            $message = "🗑️ User \"{$user['full_name']}\" berhasil dihapus permanen!";
            
        } 
        // 🚫 KICK User
        elseif ($action === 'kick_user') {
            $uid = (int)$_POST['user_id'];
            dbQuery("UPDATE users SET status = 'kicked', is_active = FALSE WHERE id = ?", [$uid]);
            $user = dbFetch("SELECT full_name FROM users WHERE id = ?", [$uid]);
            addToLiveFeed($uid, 'user_kicked', "{$user['full_name']} Di-kick manual oleh admin");
            $message = "🚫 User berhasil di-KICK!";
            
        } 
        // ♻️ RESTORE Kuota
        elseif ($action === 'restore_quota') {
            $uid = (int)$_POST['user_id'];
            dbQuery("UPDATE users SET skip_count = 0, status = 'active', is_active = TRUE WHERE id = ?", [$uid]);
            $user = dbFetch("SELECT full_name FROM users WHERE id = ?", [$uid]);
            addToLiveFeed($uid, 'quota_restored', "{$user['full_name']} Kuota dikembalikan admin (0 bolos)");
            $message = "♻️ Kuota berhasil di-restore!";
            
        } 
        // 🔍 PREVIEW: Cek user yang belum lapor (READ-ONLY, no DB change)
        elseif ($action === 'preview_skips') {
            $check_date = $_POST['check_date'] ?? date('Y-m-d', strtotime('-1 day'));
            $preview_users = [];
            
            $users = dbFetchAll("SELECT id, full_name, whatsapp_number, skip_count, skip_quota_total, status FROM users WHERE status IN ('active','trial') AND is_active = TRUE");
            
            foreach ($users as $u) {
                $has_report = dbFetch("SELECT id FROM daily_reports WHERE user_id = ? AND report_date = ?", [$u['id'], $check_date]);
                if (!$has_report) {
                    $potential_new_skip = $u['skip_count'] + 1;
                    $will_be_kicked = $potential_new_skip >= $u['skip_quota_total'];
                    $preview_users[] = [
                        'id' => $u['id'],
                        'full_name' => $u['full_name'],
                        'whatsapp_number' => $u['whatsapp_number'],
                        'current_skip' => $u['skip_count'],
                        'potential_skip' => $potential_new_skip,
                        'quota_total' => $u['skip_quota_total'],
                        'will_be_kicked' => $will_be_kicked,
                        'status' => $u['status']
                    ];
                }
            }
            
            // Simpan preview ke session untuk konfirmasi nanti
            $_SESSION['skip_preview'] = $preview_users;
            $_SESSION['skip_check_date'] = $check_date;
            
            if (empty($preview_users)) {
                $message = "✅ Semua user sudah lapor pada tanggal " . formatDateIndo($check_date) . ". Tidak ada yang perlu di-penalty.";
            } else {
                $message = "🔍 Ditemukan " . count($preview_users) . " user yang belum lapor. Silakan review dan konfirmasi di bawah.";
            }
            
        } 
        // ✅ CONFIRM: Eksekusi penalty untuk user yang dipilih admin
        elseif ($action === 'confirm_skips') {
            $selected_ids = $_POST['selected_users'] ?? [];
            $excuse_ids = $_POST['excuse_users'] ?? [];
            $check_date = $_SESSION['skip_check_date'] ?? date('Y-m-d', strtotime('-1 day'));
            $preview = $_SESSION['skip_preview'] ?? [];
            
            $penalized = 0;
            $excused = 0;
            $kicked_count = 0;
            
            foreach ($preview as $p) {
                if (in_array($p['id'], $excuse_ids)) {
                    // Admin kasih keringanan - catat di skip_logs sebagai "excused"
                    dbQuery("INSERT INTO skip_logs (user_id, skip_date, skip_number, admin_note, is_restored) VALUES (?, ?, ?, ?, ?)", 
                            [$p['id'], $check_date, $p['current_skip'] + 1, "Excused by admin - alasan valid", true]);
                    addToLiveFeed($p['id'], 'quota_restored', "{$p['full_name']} Dikecualikan dari penalty (alasan valid)");
                    $excused++;
                } 
                elseif (in_array($p['id'], $selected_ids)) {
                    // Eksekusi penalty
                    dbQuery("UPDATE users SET skip_count = skip_count + 1 WHERE id = ?", [$p['id']]);
                    
                    $new_skip = $p['current_skip'] + 1;
                    $warn_type = "skip_warning_" . min($new_skip, 3);
                    addToLiveFeed($p['id'], $warn_type, "{$p['full_name']} Bolos hari ke-$new_skip");
                    
                    // Catat di skip_logs
                    dbQuery("INSERT INTO skip_logs (user_id, skip_date, skip_number) VALUES (?, ?, ?)", 
                            [$p['id'], $check_date, $new_skip]);
                    
                    $penalized++;
                    
                    // Auto-kick jika kuota habis
                    if ($new_skip >= $p['quota_total']) {
                        checkAndKickUser($p['id']);
                        $kicked_count++;
                    }
                }
            }
            
            // Clear session
            unset($_SESSION['skip_preview'], $_SESSION['skip_check_date']);
            
            $msg_parts = [];
            if ($penalized > 0) $msg_parts[] = "$penalized user di-penalty";
            if ($excused > 0) $msg_parts[] = "$excused user di-excuse";
            if ($kicked_count > 0) $msg_parts[] = "$kicked_count user di-KICK otomatis";
            
            $message = "✅ Proses selesai! " . implode(', ', $msg_parts) . ".";
            
        }
        // ❌ CANCEL: Batalkan preview
        elseif ($action === 'cancel_preview') {
            unset($_SESSION['skip_preview'], $_SESSION['skip_check_date']);
            $message = "❌ Preview dibatalkan. Tidak ada perubahan pada database.";
        }
        
    } catch (Exception $e) {
        error_log("Admin Error: " . $e->getMessage());
        $error = "❌ Terjadi kesalahan: " . $e->getMessage();
    }
}

// Pagination & Search
$tab = $_GET['tab'] ?? 'users';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;
$search = sanitize($_GET['search'] ?? '');

$search_clause = $search ? "WHERE full_name LIKE ? OR whatsapp_number LIKE ?" : "";
$search_params = $search ? ["%$search%", "%$search%"] : [];

$total_users = dbFetch("SELECT COUNT(*) as c FROM users $search_clause", $search_params)['c'];
$users = dbFetchAll("SELECT * FROM users $search_clause ORDER BY created_at DESC LIMIT ? OFFSET ?", 
    array_merge($search_params, [$per_page, $offset]));
$pagination_users = getPagination($total_users, $per_page, $page, "admin_dashboard.php?tab=users&search=" . urlencode($search));

$skip_users = dbFetchAll("SELECT * FROM users WHERE skip_count > 0 ORDER BY skip_count DESC");

$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_user = dbFetch("SELECT * FROM users WHERE id = ?", [(int)$_GET['edit']]);
}

// Load preview data if exists
$preview_mode = isset($_SESSION['skip_preview']);
$preview_users = $_SESSION['skip_preview'] ?? [];
$check_date = $_SESSION['skip_check_date'] ?? date('Y-m-d', strtotime('-1 day'));

$page_title = 'Admin Dashboard';
?>

<?php require_once 'includes/header.php'; ?>
<div class="min-h-screen bg-gray-900 px-4 py-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold"><i class="fas fa-shield-alt mr-2 text-orange-400"></i>Admin Command Center</h1>
            <div class="flex gap-2">
                <a href="setting.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition"><i class="fas fa-cog mr-1"></i> Settings</a>
                <a href="public_feed.php" target="_blank" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition"><i class="fas fa-rss mr-1"></i> Live Feed</a>
                <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm transition"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-green-900/50 border border-green-700 rounded text-green-200 text-sm"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm"><?= $error ?></div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-700 pb-2">
            <?php foreach(['users' => '<i class="fas fa-users mr-1"></i>Users', 'skips' => '<i class="fas fa-exclamation-triangle mr-1"></i>Cek Bolos'] as $key => $label): ?>
                <a href="?tab=<?= $key ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
                   class="px-4 py-2 rounded-t-lg transition <?= $tab === $key ? 'bg-gray-800 text-orange-400 border-b-2 border-orange-400' : 'hover:bg-gray-800 text-gray-400' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Bar -->
        <?php if ($tab === 'users'): ?>
            <form method="GET" class="mb-4 flex gap-2">
                <input type="hidden" name="tab" value="users">
                <input type="text" name="search" value="<?= sanitize($_GET['search'] ?? '') ?>" 
                       placeholder="Cari nama atau WhatsApp..." 
                       class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition"><i class="fas fa-search"></i></button>
                <?php if ($search): ?>
                    <a href="?tab=users" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <!-- EDIT FORM (Inline if editing) -->
        <?php if ($tab === 'users' && $edit_user): ?>
            <div class="mb-6 bg-gray-800 rounded-xl p-6 border-2 border-orange-600">
                <h3 class="font-bold mb-4 flex items-center justify-between">
                    <span><i class="fas fa-edit mr-2 text-orange-400"></i>Edit User: <?= sanitize($edit_user['full_name']) ?></span>
                    <a href="?tab=users<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-sm text-gray-400 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Batal</a>
                </h3>
                <form method="POST" class="grid md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="save_user">
                    <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                        <input type="text" name="full_name" value="<?= sanitize($edit_user['full_name']) ?>" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">WhatsApp *</label>
                        <input type="text" name="whatsapp_number" value="<?= sanitize($edit_user['whatsapp_number']) ?>" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Discord Username</label>
                        <input type="text" name="discord_username" value="<?= sanitize($edit_user['discord_username'] ?? '') ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                            <option value="trial" <?= $edit_user['status'] === 'trial' ? 'selected' : '' ?>>Trial</option>
                            <option value="active" <?= $edit_user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= $edit_user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="kicked" <?= $edit_user['status'] === 'kicked' ? 'selected' : '' ?>>Kicked</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Paket</label>
                        <select name="subscription_type" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                            <option value="trial" <?= $edit_user['subscription_type'] === 'trial' ? 'selected' : '' ?>>Trial</option>
                            <option value="monthly" <?= $edit_user['subscription_type'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="quarterly" <?= $edit_user['subscription_type'] === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                            <option value="yearly" <?= $edit_user['subscription_type'] === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Start Date</label>
                        <input type="date" name="subscription_start" value="<?= $edit_user['subscription_start'] ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">End Date</label>
                        <input type="date" name="subscription_end" value="<?= $edit_user['subscription_end'] ?>" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuota Bolos</label>
                        <input type="number" name="skip_quota_total" value="<?= $edit_user['skip_quota_total'] ?>" readonly class="w-full px-4 py-2 bg-gray-600 border border-gray-500 rounded-lg text-gray-400 cursor-not-allowed" title="Kuota ditentukan oleh paket, tidak bisa diubah manual">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Kuota otomatis sesuai paket. Ubah paket untuk ganti kuota.</p>
                    </div>
                    
                    <div class="md:col-span-2 flex gap-2 mt-2">
                        <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 rounded-lg font-medium transition"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                        <a href="?tab=users<?= $search ? '&search='.urlencode($search) : '' ?>" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition"><i class="fas fa-times mr-1"></i> Batal</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- TAB: Users List -->
        <?php if ($tab === 'users'): ?>
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-700/50 text-gray-300">
                            <tr>
                                <th class="p-4">Nama</th>
                                <th class="p-4">WhatsApp</th>
                                <th class="p-4">Discord</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Paket</th>
                                <th class="p-4">Bolos</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="p-6 text-center text-gray-500">Tidak ada user ditemukan.</td></tr>
                            <?php else: 
                                foreach ($users as $u): 
                            ?>
                                <tr class="hover:bg-gray-700/30">
                                    <td class="p-4 font-medium"><?= sanitize($u['full_name']) ?></td>
                                    <td class="p-4"><?= sanitize($u['whatsapp_number']) ?></td>
                                    <td class="p-4 text-gray-400"><?= sanitize($u['discord_username'] ?? '-') ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold <?= 
                                            $u['status'] === 'kicked' ? 'bg-red-900 text-red-300' : 
                                            ($u['status'] === 'trial' ? 'bg-blue-900 text-blue-300' : 
                                            ($u['status'] === 'suspended' ? 'bg-yellow-900 text-yellow-300' : 'bg-green-900 text-green-300'))
                                        ?>">
                                            <?= ucfirst($u['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4"><?= ucfirst($u['subscription_type']) ?></td>
                                    <td class="p-4">
                                        <span class="<?= $u['skip_count'] >= $u['skip_quota_total'] ? 'text-red-400 font-bold' : 'text-yellow-400' ?>">
                                            <?= $u['skip_count'] ?>/<?= $u['skip_quota_total'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center gap-1 flex-wrap">
                                            <!-- ✏️ Edit -->
                                            <a href="?tab=users&edit=<?= $u['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
                                               class="px-2 py-1 bg-blue-600 hover:bg-blue-700 rounded text-xs transition" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($u['status'] !== 'kicked'): ?>
                                                <!-- 🚫 Kick -->
                                                <form method="POST" onsubmit="return confirm('KICK user ini? Mereka tidak bisa login lagi.')">
                                                    <input type="hidden" name="action" value="kick_user">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button class="px-2 py-1 bg-red-600 hover:bg-red-700 rounded text-xs transition" title="Kick">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- ♻️ Restore -->
                                                <?php if ($u['skip_count'] > 0): ?>
                                                    <form method="POST" onsubmit="return confirm('Reset kuota bolos user ini ke 0?')">
                                                        <input type="hidden" name="action" value="restore_quota">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <button class="px-2 py-1 bg-green-600 hover:bg-green-700 rounded text-xs transition" title="Restore Kuota">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <!-- 🗑️ Delete (Hati-hati!) -->
                                            <form method="POST" onsubmit="return confirm('HAPUS PERMANEN user ini? Semua data (laporan, log, dreams) akan ikut terhapus. Tindakan ini TIDAK BISA dibatalkan!')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button class="px-2 py-1 bg-gray-600 hover:bg-gray-500 rounded text-xs transition" title="Hapus Permanen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?= $pagination_users ?>
            </div>
            
            <!-- Quick Add Button -->
            <div class="mt-4 text-right">
                <a href="?tab=users&edit=0" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-medium transition">
                    <i class="fas fa-plus mr-2"></i> Tambah User Baru
                </a>
            </div>
        <?php endif; ?>

        <!-- TAB: Cek Bolos (FIXED: Preview + Confirm Flow) -->
        <?php if ($tab === 'skips'): ?>
            
            <!-- Preview Mode: Show who didn't report (READ-ONLY) -->
            <?php if ($preview_mode): ?>
                <div class="bg-gray-800 rounded-xl border border-blue-700 mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-blue-900/30 border-b border-blue-700">
                        <h3 class="font-bold text-lg text-blue-300">
                            <i class="fas fa-eye mr-2"></i>Preview: User yang Belum Lapor (<?= formatDateIndo($check_date) ?>)
                        </h3>
                        <p class="text-sm text-blue-200/80 mt-1">
                            <i class="fas fa-info-circle mr-1"></i> Ini hanya preview. Kuota user BELUM berubah. Pilih aksi di bawah.
                        </p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="action" value="confirm_skips">
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-700/50 text-gray-300">
                                    <tr>
                                        <th class="p-4 w-8"><input type="checkbox" id="select-all-penalty" class="rounded text-orange-500"></th>
                                        <th class="p-4">Nama</th>
                                        <th class="p-4">WhatsApp</th>
                                        <th class="p-4 text-center">Skip Saat Ini</th>
                                        <th class="p-4 text-center">Jika Di-Penalty</th>
                                        <th class="p-4 text-center">Status Nanti</th>
                                        <th class="p-4 text-center">Aksi Admin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <?php foreach ($preview_users as $p): 
                                        $will_be_kicked = $p['potential_skip'] >= $p['quota_total'];
                                    ?>
                                        <tr class="hover:bg-gray-700/30 <?= $will_be_kicked ? 'bg-red-900/20' : '' ?>">
                                            <td class="p-4 text-center">
                                                <input type="checkbox" name="selected_users[]" value="<?= $p['id'] ?>" class="penalty-checkbox rounded text-orange-500" data-will-kick="<?= $will_be_kicked ? '1' : '0' ?>">
                                            </td>
                                            <td class="p-4 font-medium"><?= sanitize($p['full_name']) ?></td>
                                            <td class="p-4"><?= sanitize($p['whatsapp_number']) ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-1 rounded text-xs font-bold bg-yellow-900 text-yellow-300">
                                                    <?= $p['current_skip'] ?>/<?= $p['quota_total'] ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-1 rounded text-xs font-bold <?= $will_be_kicked ? 'bg-red-900 text-red-300' : 'bg-orange-900 text-orange-300' ?>">
                                                    <?= $p['potential_skip'] ?>/<?= $p['quota_total'] ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <?php if ($will_be_kicked): ?>
                                                    <span class="text-red-400 font-bold text-xs"><i class="fas fa-skull mr-1"></i>DI-KICK</span>
                                                <?php else: ?>
                                                    <span class="text-green-400 text-xs"><i class="fas fa-check mr-1"></i>Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-4 text-center">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="excuse_users[]" value="<?= $p['id'] ?>" class="excuse-checkbox rounded text-green-500" data-user-id="<?= $p['id'] ?>">
                                                    <span class="text-xs text-green-300">Excuse</span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-6 flex flex-wrap gap-3 justify-end">
                            <button type="button" onclick="if(confirm('Batalkan preview? Tidak ada perubahan yang akan disimpan.')) { document.querySelector('input[name=\'action\'][value=\'cancel_preview\']')?.click(); }" 
                                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition">
                                <i class="fas fa-times mr-1"></i> Batal
                            </button>
                            <button type="submit" name="action" value="confirm_skips" 
                                    class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-lg font-bold transition flex items-center gap-2"
                                    onclick="return confirmSelected()">
                                <i class="fas fa-check-circle"></i> Konfirmasi & Eksekusi
                            </button>
                        </div>
                        
                        <p class="mt-4 text-xs text-gray-500 text-center">
                            <i class="fas fa-lightbulb mr-1"></i> Tips: Centang "Excuse" jika user punya alasan valid (sakit, darurat). Mereka tidak akan kena penalty dan kuota tetap.
                        </p>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Normal Mode: Button to start preview -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
                    <h3 class="font-bold mb-2"><i class="fas fa-search mr-2"></i>Manual Check Bolos</h3>
                    <p class="text-sm text-gray-400 mb-4">
                        Scan user yang tidak lapor pada tanggal tertentu. <strong class="text-orange-400">Tidak ada perubahan database sampai admin konfirmasi.</strong>
                    </p>
                    
                    <form method="POST" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal yang Dicek</label>
                            <input type="date" name="check_date" value="<?= $check_date ?>" 
                                   class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                        </div>
                        <button type="submit" name="action" value="preview_skips" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition flex items-center gap-2">
                            <i class="fas fa-search"></i> Preview User yang Belum Lapor
                        </button>
                    </form>
                    
                    <div class="mt-4 p-3 bg-blue-900/30 rounded border border-blue-700/50 text-sm text-blue-200">
                        <i class="fas fa-info-circle mr-1"></i> 
                        <strong>Cara Pakai:</strong> 
                        1) Pilih tanggal → 2) Klik "Preview" → 3) Review list user → 4) Pilih: Penalty / Excuse → 5) Konfirmasi.
                    </div>
                </div>

                <!-- Existing Skip Monitor Table (Read-Only Display) -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600">
                        <h3 class="font-semibold text-sm"><i class="fas fa-list mr-2"></i>Riwayat User dengan Skip</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-700/50 text-gray-300">
                                <tr><th class="p-4">Nama</th><th class="p-4">WhatsApp</th><th class="p-4 text-center">Bolos Ke-</th><th class="p-4">Status</th><th class="p-4 text-center">Aksi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php if (empty($skip_users)): ?>
                                    <tr><td colspan="5" class="p-6 text-center text-gray-500"><i class="fas fa-check-circle text-green-400 mr-2"></i>Tidak ada user yang bolos.</td></tr>
                                <?php else: 
                                    foreach ($skip_users as $u): 
                                        $level = min($u['skip_count'], 3);
                                        $color = $level === 1 ? 'yellow' : ($level === 2 ? 'orange' : 'red');
                                ?>
                                    <tr class="hover:bg-gray-700/30">
                                        <td class="p-4"><?= sanitize($u['full_name']) ?></td>
                                        <td class="p-4"><?= sanitize($u['whatsapp_number']) ?></td>
                                        <td class="p-4 text-center text-<?= $color ?>-400 font-bold"><i class="fas fa-exclamation-triangle mr-1"></i><?= $u['skip_count'] ?>x</td>
                                        <td class="p-4"><?= $u['status'] === 'kicked' ? '<span class="text-red-400 font-bold"><i class="fas fa-user-slash mr-1"></i>DI-KICK</span>' : '<span class="text-green-400"><i class="fas fa-check mr-1"></i>AKTIF</span>' ?></td>
                                        <td class="p-4 text-center">
                                            <?php if ($u['status'] !== 'kicked' && $u['skip_count'] > 0): ?>
                                                <form method="POST" onsubmit="return confirm('Reset kuota bolos user ini ke 0?')">
                                                    <input type="hidden" name="action" value="restore_quota">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button class="px-3 py-1 bg-green-600 hover:bg-green-700 rounded text-xs transition" title="Restore Kuota">
                                                        <i class="fas fa-undo mr-1"></i>Restore
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-500 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
    </div>
</div>

<!-- JavaScript: Preview Confirm Logic -->
<script>
// Select All Checkbox for Penalty
document.getElementById('select-all-penalty')?.addEventListener('change', function() {
    document.querySelectorAll('.penalty-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

// Excuse checkbox logic: cannot select both penalty and excuse for same user
document.querySelectorAll('.excuse-checkbox').forEach(excuseCb => {
    excuseCb.addEventListener('change', function() {
        const userId = this.dataset.userId;
        const penaltyCb = document.querySelector(`.penalty-checkbox[value='${userId}']`);
        if (this.checked && penaltyCb) {
            penaltyCb.checked = false;
        }
    });
});

document.querySelectorAll('.penalty-checkbox').forEach(penaltyCb => {
    penaltyCb.addEventListener('change', function() {
        const userId = this.value;
        const excuseCb = document.querySelector(`.excuse-checkbox[value='${userId}']`);
        if (this.checked && excuseCb) {
            excuseCb.checked = false;
        }
    });
});

// Confirm before submit: warn if any user will be kicked
function confirmSelected() {
    const selected = document.querySelectorAll('.penalty-checkbox:checked');
    const willKick = Array.from(selected).some(cb => cb.dataset.willKick === '1');
    
    if (willKick) {
        return confirm('⚠️ PERINGATAN: Beberapa user akan di-KICK otomatis karena kuota bolos habis!\n\nTindakan ini TIDAK BISA dibatalkan.\n\nLanjutkan?');
    }
    if (selected.length === 0) {
        return confirm('Tidak ada user yang dipilih untuk di-penalty. Yakin ingin melanjutkan?');
    }
    return true;
}

// Toast notification (reuse from functions)
<?php if ($message || $error): ?>
    showToast("<?= addslashes($message ?: $error) ?>", "<?= $message ? 'success' : 'error' ?>");
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>