<?php
// user_dashboard.php - ULTIMATE: Full Theme + Pomodoro CRUD + Mega Tutorial
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Initialize settings & pricing
$settings = getSettings();
$pricing = getPricingInfo();

// Ambil data user
$user = dbFetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

if (!$user || !$user['is_active']) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Cek subscription expired
if ($user['status'] !== 'trial' && $user['subscription_end'] && strtotime($user['subscription_end']) < time()) {
    $error = "<i class='fas fa-clock mr-2'></i>Subscription lo sudah habis. Silakan perpanjang untuk tetap akses dashboard.";
}

$success = '';
$error = $error ?? '';

// ===== HANDLE DAILY REPORT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $today = date('Y-m-d');
    $existing = dbFetch("SELECT id FROM daily_reports WHERE user_id = ? AND report_date = ?", [$user['id'], $today]);
    
    if ($existing) {
        $error = "<i class='fas fa-exclamation-circle mr-2'></i>Lo sudah kirim laporan untuk hari ini. Tunggu besok ya!";
    } else {
        $main_target = sanitize($_POST['main_target'] ?? '');
        $completion_status = $_POST['completion_status'] ?? '';
        $progress_note = sanitize($_POST['progress_note'] ?? '');
        $portfolio_link = sanitize($_POST['portfolio_link'] ?? '');
        
        if (empty($main_target)) {
            $error = "<i class='fas fa-times-circle mr-2'></i>Target utama wajib diisi!";
        } elseif (empty($completion_status)) {
            $error = "<i class='fas fa-times-circle mr-2'></i>Status penyelesaian wajib dipilih!";
        } else {
            dbQuery("INSERT INTO daily_reports (user_id, report_date, main_target, completion_status, progress_note, portfolio_link) VALUES (?, ?, ?, ?, ?, ?)", 
                    [$user['id'], $today, $main_target, $completion_status, $progress_note ?: null, $portfolio_link ?: null]);
            dbQuery("UPDATE users SET last_report_date = ? WHERE id = ?", [$today, $user['id']]);
            
            $target_preview = strlen($main_target) > 50 ? substr($main_target, 0, 50) . '...' : $main_target;
            addToLiveFeed($user['id'], 'report_submitted', "{$user['full_name']} Mengirim laporan: {$target_preview}");
            
            $success = "<i class='fas fa-check-circle mr-2'></i>Laporan berhasil dikirim! Keep fighting, Warrior!";
            $user = dbFetch("SELECT * FROM users WHERE id = ?", [$user['id']]);
        }
    }
}

// ===== HANDLE DREAMS CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_dream'])) {
        $title = sanitize($_POST['dream_title'] ?? '');
        $desc = sanitize($_POST['dream_description'] ?? '');
        if (!empty($title)) {
            dbQuery("INSERT INTO dreams (user_id, dream_title, dream_description) VALUES (?, ?, ?)", [$user['id'], $title, $desc]);
            $success = "<i class='fas fa-bullseye mr-2'></i>Impian berhasil ditambahkan!";
        }
    }
    elseif (isset($_POST['edit_dream'])) {
        $dream_id = (int)$_POST['dream_id'];
        $title = sanitize($_POST['dream_title'] ?? '');
        $desc = sanitize($_POST['dream_description'] ?? '');
        $status = $_POST['dream_status'] ?? 'in_progress';
        if (!empty($title)) {
            dbQuery("UPDATE dreams SET dream_title = ?, dream_description = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?", 
                    [$title, $desc, $status, $dream_id, $user['id']]);
            $success = "<i class='fas fa-edit mr-2'></i>Impian berhasil diupdate!";
        }
    }
    elseif (isset($_POST['delete_dream'])) {
        $dream_id = (int)$_POST['dream_id'];
        dbQuery("DELETE FROM dreams WHERE id = ? AND user_id = ?", [$dream_id, $user['id']]);
        $success = "<i class='fas fa-trash mr-2'></i>Impian berhasil dihapus!";
    }
}

// ===== HANDLE POMODORO SETTINGS SAVE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pomodoro_settings'])) {
    // Settings disimpan via JS localStorage, ini hanya untuk validasi jika perlu sync ke backend nanti
    $success = "<i class='fas fa-cog mr-2'></i>Setting Pomodoro berhasil disimpan!";
}

// Ambil data dashboard
$streak = calculateStreak($user['id']);
$remaining_days = getRemainingDays($user['subscription_end']);
$remaining_quota = $user['skip_quota_total'] - $user['skip_count'];
$today_report = dbFetch("SELECT * FROM daily_reports WHERE user_id = ? AND report_date = CURDATE()", [$user['id']]);
$recent_reports = dbFetchAll("SELECT * FROM daily_reports WHERE user_id = ? ORDER BY report_date DESC LIMIT 7", [$user['id']]);
$dreams = dbFetchAll("SELECT * FROM dreams WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);

// Milestones
$milestones = [
    ['id' => 'first_report', 'title' => 'First Step', 'desc' => 'Kirim laporan pertama', 'icon' => 'fa-flag', 'achieved' => $user['last_report_date'] !== null],
    ['id' => 'streak_7', 'title' => '7 Days Streak', 'desc' => 'Konsisten 7 hari berturut-turut', 'icon' => 'fa-fire', 'achieved' => $streak >= 7],
    ['id' => 'streak_30', 'title' => '30 Days Warrior', 'desc' => 'Streak 30 hari!', 'icon' => 'fa-crown', 'achieved' => $streak >= 30],
    ['id' => 'dream_completed', 'title' => 'Dream Achiever', 'desc' => 'Selesaikan 1 impian', 'icon' => 'fa-trophy', 'achieved' => count(array_filter($dreams, fn($d) => $d['status'] === 'completed')) > 0],
    ['id' => 'no_skip', 'title' => 'Zero Skip', 'desc' => 'Tidak pernah bolos', 'icon' => 'fa-shield-alt', 'achieved' => $user['skip_count'] === 0],
];

$page_title = 'Dashboard';
?>

<?php require_once 'includes/header.php'; ?>

<!-- ===== MEGA TUTORIAL MODAL ===== -->
<div id="tutorial-modal" class="fixed inset-0 bg-black/90 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700 shadow-2xl">
        <div class="sticky top-0 bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center z-10">
            <h3 class="text-xl font-bold"><i class="fas fa-graduation-cap mr-2 text-orange-400"></i>Tutorial Lengkap Dashboard</h3>
            <button onclick="closeTutorial()" class="text-gray-400 hover:text-white transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        
        <div id="tutorial-steps" class="p-6 space-y-6">
            <!-- Step 1: Daily Report -->
            <div class="tutorial-step" data-step="1">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-900/50 border border-blue-700 flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-blue-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">1. Laporan Harian</h4>
                        <p class="text-gray-300 mb-3">Fitur inti untuk tracking progress harian lo.</p>
                        <ul class="text-sm text-gray-400 space-y-2 ml-4 list-disc">
                            <li>Isi <strong>Target Utama</strong>: Tulis 1 target spesifik yang ingin lo capai hari ini</li>
                            <li>Pilih <strong>Status Penyelesaian</strong>: 100%, Sebagian, atau Gagal</li>
                            <li>Jika "Sebagian" atau "Gagal", isi <strong>Catatan Progress</strong> untuk evaluasi</li>
                            <li>(Opsional) Tambahkan <strong>Link Portofolio</strong> sebagai bukti hasil kerja</li>
                            <li>Klik <strong>Kirim Laporan</strong> untuk submit</li>
                        </ul>
                        <div class="mt-3 p-3 bg-blue-900/30 rounded border border-blue-700/50 text-sm text-blue-200">
                            <i class="fas fa-lightbulb mr-1"></i> Tips: Konsisten lapor = streak naik = motivasi meningkat!
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Dreams CRUD -->
            <div class="tutorial-step hidden" data-step="2">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-900/50 border border-green-700 flex items-center justify-center">
                        <i class="fas fa-bullseye text-green-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">2. Impian & CRUD</h4>
                        <p class="text-gray-300 mb-3">Catat dan kelola impian jangka panjang lo.</p>
                        <div class="space-y-3">
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-plus-circle text-green-400 mr-1"></i>Tambah Impian</p>
                                <p class="text-sm text-gray-400 ml-5">Klik "Tambah Impian", isi judul & deskripsi, pilih status, lalu Simpan.</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-edit text-blue-400 mr-1"></i>Edit Impian</p>
                                <p class="text-sm text-gray-400 ml-5">Hover item impian, klik icon <i class="fas fa-edit"></i>, update data, Submit.</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-trash text-red-400 mr-1"></i>Hapus Impian</p>
                                <p class="text-sm text-gray-400 ml-5">Hover item, klik icon <i class="fas fa-trash"></i>, konfirmasi penghapusan.</p>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-green-900/30 rounded border border-green-700/50 text-sm text-green-200">
                            <i class="fas fa-star mr-1"></i> Impian yang "Completed" akan unlock achievement di Milestones!
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Milestones -->
            <div class="tutorial-step hidden" data-step="3">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-900/50 border border-yellow-700 flex items-center justify-center">
                        <i class="fas fa-trophy text-yellow-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">3. Milestones & Achievements</h4>
                        <p class="text-gray-300 mb-3">Sistem reward otomatis untuk motivasi visual.</p>
                        <ul class="text-sm text-gray-400 space-y-2 ml-4 list-disc">
                            <li><strong>First Step</strong>: Terkunci setelah kirim laporan pertama</li>
                            <li><strong>7/30 Days Streak</strong>: Konsisten lapor berturut-turut</li>
                            <li><strong>Dream Achiever</strong>: Selesaikan minimal 1 impian</li>
                            <li><strong>Zero Skip</strong>: Pertahankan kuota bolos tetap 0</li>
                        </ul>
                        <p class="text-sm text-gray-400 mt-2">Achievement yang sudah dicapai akan berwarna <span class="text-yellow-400 font-semibold">emas</span>, yang belum akan abu-abu.</p>
                    </div>
                </div>
            </div>
            
            <!-- Step 4: Pomodoro CRUD -->
            <div class="tutorial-step hidden" data-step="4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-900/50 border border-purple-700 flex items-center justify-center">
                        <i class="fas fa-stopwatch text-purple-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">4. Pomodoro Timer + CRUD Settings</h4>
                        <p class="text-gray-300 mb-3">Timer fokus dengan setting yang bisa lo kustomisasi.</p>
                        <div class="space-y-3">
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-play-circle text-green-400 mr-1"></i>Kontrol Dasar</p>
                                <p class="text-sm text-gray-400 ml-5"><strong>Start</strong>: Mulai timer, <strong>Pause</strong>: Jeda, <strong>Reset</strong>: Ulang ke default</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-cog text-blue-400 mr-1"></i>Custom Settings (CRUD)</p>
                                <p class="text-sm text-gray-400 ml-5">Klik icon <i class="fas fa-cog"></i> di pojok kanan atas card Pomodoro untuk:</p>
                                <ul class="text-xs text-gray-500 ml-10 mt-1 space-y-1 list-disc">
                                    <li>Ubah <strong>Work Duration</strong> (default: 25 menit)</li>
                                    <li>Ubah <strong>Break Duration</strong> (default: 5 menit)</li>
                                    <li>Setel <strong>Auto-start Break</strong> (opsional)</li>
                                    <li>Klik <strong>Save Settings</strong> untuk simpan ke localStorage</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-purple-900/30 rounded border border-purple-700/50 text-sm text-purple-200">
                            <i class='fas fa-info-circle mr-1'></i> Setting tersimpan per-user di browser lo. Ganti device? Setting ulang ya!
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 5: Todo List CRUD -->
            <div class="tutorial-step hidden" data-step="5">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-pink-900/50 border border-pink-700 flex items-center justify-center">
                        <i class="fas fa-tasks text-pink-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">5. Todo List + CRUD</h4>
                        <p class="text-gray-300 mb-3">Kelola tugas harian dengan sistem sederhana.</p>
                        <div class="space-y-3">
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-plus text-green-400 mr-1"></i>Tambah Todo</p>
                                <p class="text-sm text-gray-400 ml-5">Klik "Tambah", ketik tugas di popup, tekan OK/Enter.</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-check-circle text-blue-400 mr-1"></i>Toggle Selesai</p>
                                <p class="text-sm text-gray-400 ml-5">Centang checkbox untuk tandai selesai (teks akan dicoret).</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-200"><i class="fas fa-times text-red-400 mr-1"></i>Hapus Todo</p>
                                <p class="text-sm text-gray-400 ml-5">Klik icon <i class="fas fa-times"></i> di kanan item untuk hapus.</p>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-pink-900/30 rounded border border-pink-700/50 text-sm text-pink-200">
                            <i class='fas fa-lock mr-1'></i> Data todo disimpan di localStorage browser lo, tidak sync ke server.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 6: Custom Theme -->
            <div class="tutorial-step hidden" data-step="6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-900/50 border border-indigo-700 flex items-center justify-center">
                        <i class="fas fa-palette text-indigo-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">6. Custom Theme Dashboard</h4>
                        <p class="text-gray-300 mb-3">Personalisasi tampilan dashboard sesuai selera lo.</p>
                        <ul class="text-sm text-gray-400 space-y-2 ml-4 list-disc">
                            <li>Klik icon <i class="fas fa-palette"></i> di header kanan</li>
                            <li>Pilih 1 dari 6 tema: Default, Ocean, Forest, Sunset, Midnight, Rose</li>
                            <li>Perubahan langsung diterapkan: background, gradient tombol, accent color</li>
                            <li>Preferensi otomatis tersimpan di localStorage</li>
                        </ul>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="p-2 rounded bg-gradient-to-br from-orange-500 to-red-600 text-center text-xs">Default</div>
                            <div class="p-2 rounded bg-gradient-to-br from-blue-500 to-cyan-600 text-center text-xs">Ocean</div>
                            <div class="p-2 rounded bg-gradient-to-br from-green-500 to-emerald-600 text-center text-xs">Forest</div>
                            <div class="p-2 rounded bg-gradient-to-br from-yellow-500 to-orange-600 text-center text-xs">Sunset</div>
                            <div class="p-2 rounded bg-gradient-to-br from-purple-500 to-indigo-600 text-center text-xs">Midnight</div>
                            <div class="p-2 rounded bg-gradient-to-br from-pink-500 to-rose-600 text-center text-xs">Rose</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 7: Pricing & Extend -->
            <div class="tutorial-step hidden" data-step="7">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-900/50 border border-amber-700 flex items-center justify-center">
                        <i class="fas fa-crown text-amber-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">7. Perpanjang Subscription</h4>
                        <p class="text-gray-300 mb-3">Kelola paket membership lo dengan mudah.</p>
                        <ul class="text-sm text-gray-400 space-y-2 ml-4 list-disc">
                            <li>Lihat paket tersedia: Monthly, Quarterly, Yearly</li>
                            <li>Setiap paket menampilkan: harga (diskon 5%), kuota bolos, durasi</li>
                            <li>Klik "Chat Admin" untuk langsung WhatsApp admin dengan pesan otomatis</li>
                            <li>Pesan sudah terisi: nama lo, nomor WA, dan paket yang dipilih</li>
                        </ul>
                        <div class="mt-3 p-3 bg-amber-900/30 rounded border border-amber-700/50 text-sm text-amber-200">
                            <i class='fas fa-exclamation-triangle mr-1'></i> Setelah bayar, admin akan update status & kuota lo manual. Refresh dashboard untuk lihat perubahan.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 8: Account Info & Security -->
            <div class="tutorial-step hidden" data-step="8">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-700 border border-gray-600 flex items-center justify-center">
                        <i class="fas fa-user-shield text-gray-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg mb-2">8. Info Akun & Keamanan</h4>
                        <p class="text-gray-300 mb-3">Pantau status akun dan data pribadi lo.</p>
                        <ul class="text-sm text-gray-400 space-y-2 ml-4 list-disc">
                            <li><strong>WhatsApp</strong>: Nomor login utama (tidak bisa diubah user)</li>
                            <li><strong>Discord</strong>: Username Discord untuk akses grup (diisi admin)</li>
                            <li><strong>Status</strong>: Trial / Active / Suspended / Kicked</li>
                            <li>Jika status <span class="text-red-400 font-semibold">Kicked</span>: Tidak bisa login, kuota bolos habis</li>
                        </ul>
                        <div class="mt-3 p-3 bg-red-900/30 rounded border border-red-700/50 text-sm text-red-200">
                            <i class='fas fa-skull mr-1'></i> Kicked = Auto blacklist. Tidak ada refund. Hubungi admin via WhatsApp hanya untuk konfirmasi, bukan untuk nego.
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Navigation -->
        <div class="sticky bottom-0 bg-gray-800 px-6 py-4 border-t border-gray-700 flex justify-between items-center">
            <button onclick="skipTutorial()" class="px-4 py-2 text-gray-400 hover:text-white transition text-sm">
                <i class="fas fa-forward mr-1"></i> Skip All
            </button>
            <div class="flex items-center gap-3">
                <button id="tutorial-prev" onclick="changeTutorialStep(-1)" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition hidden">
                    <i class="fas fa-arrow-left"></i> Prev
                </button>
                <button id="tutorial-next" onclick="changeTutorialStep(1)" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 rounded-lg transition font-medium">
                    Next <i class="fas fa-arrow-right ml-1"></i>
                </button>
                <button id="tutorial-finish" onclick="closeTutorial()" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg transition font-medium hidden">
                    <i class="fas fa-play mr-1"></i> Mulai Dashboard
                </button>
            </div>
        </div>
        
        <!-- Progress Indicator -->
        <div class="px-6 pb-4 flex justify-center gap-1.5">
            <?php for($i=1; $i<=8; $i++): ?>
                <span class="tutorial-dot w-2 h-2 rounded-full <?= $i===1 ? 'bg-orange-500' : 'bg-gray-600' ?>" data-dot="<?= $i ?>"></span>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- ===== POMODORO SETTINGS MODAL ===== -->
<div id="pomodoro-settings-modal" class="fixed inset-0 bg-black/80 z-40 hidden flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-xl max-w-md w-full p-6 border border-gray-700 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-bold"><i class="fas fa-cog mr-2 text-purple-400"></i>Setting Pomodoro</h4>
            <button onclick="togglePomodoroSettings()" class="text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="pomodoro-settings-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1"><i class="fas fa-briefcase mr-1 text-blue-400"></i>Work Duration (menit)</label>
                <input type="number" id="pomodoro-work" min="5" max="120" value="25" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><i class="fas fa-coffee mr-1 text-green-400"></i>Break Duration (menit)</label>
                <input type="number" id="pomodoro-break" min="3" max="30" value="5" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="pomodoro-auto-break" class="rounded text-purple-500">
                <label for="pomodoro-auto-break" class="text-sm text-gray-300">Auto-start break setelah work selesai</label>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="resetPomodoroSettings()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition">Reset Default</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-sm font-medium transition">Simpan Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Dashboard Header -->
<div class="bg-gradient-to-r from-warrior-dark via-gray-900 to-warrior-dark border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">
                    <i class="fas fa-user mr-2"></i>Halo, <?= sanitize($user['full_name']) ?>!
                </h1>
                <p class="text-gray-400">Daily Report Warrior • <?= formatDateIndo(date('Y-m-d'), 'l, d F Y') ?></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold <?= 
                    $user['status'] === 'trial' ? 'bg-blue-900 text-blue-300' : 
                    ($user['status'] === 'kicked' ? 'bg-red-900 text-red-300' : 'bg-green-900 text-green-300')
                ?>">
                    <?= strtoupper($user['status']) ?>
                </span>
                <button onclick="showTutorial()" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition" title="Lihat Tutorial Lengkap">
                    <i class="fas fa-question-circle"></i>
                </button>
                <button onclick="toggleThemePicker()" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition" title="Ganti Tema Dashboard">
                    <i class="fas fa-palette"></i>
                </button>
                <a href="logout.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Theme Picker Dropdown -->
<div id="theme-picker" class="hidden fixed top-16 right-4 z-30 bg-gray-800 rounded-xl border border-gray-700 p-4 shadow-xl w-72">
    <h4 class="font-semibold mb-3"><i class="fas fa-palette mr-2"></i>Pilih Tema Dashboard</h4>
    <p class="text-xs text-gray-400 mb-3">Background + aksen + tombol akan berubah. Tersimpan otomatis.</p>
    <div class="grid grid-cols-3 gap-2">
        <?php 
        $theme_options = [
            'default' => ['name' => 'Default', 'gradient' => 'from-orange-500 to-red-600', 'bg' => 'bg-warrior-dark', 'accent' => 'orange'],
            'ocean' => ['name' => 'Ocean', 'gradient' => 'from-blue-500 to-cyan-600', 'bg' => 'bg-slate-900', 'accent' => 'blue'],
            'forest' => ['name' => 'Forest', 'gradient' => 'from-green-500 to-emerald-600', 'bg' => 'bg-green-950', 'accent' => 'green'],
            'sunset' => ['name' => 'Sunset', 'gradient' => 'from-yellow-500 to-orange-600', 'bg' => 'bg-orange-950', 'accent' => 'yellow'],
            'midnight' => ['name' => 'Midnight', 'gradient' => 'from-purple-500 to-indigo-600', 'bg' => 'bg-indigo-950', 'accent' => 'purple'],
            'rose' => ['name' => 'Rose', 'gradient' => 'from-pink-500 to-rose-600', 'bg' => 'bg-rose-950', 'accent' => 'pink'],
        ];
        foreach ($theme_options as $key => $t): 
        ?>
            <button onclick="setTheme('<?= $key ?>')" class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-center transition group" title="<?= $t['name'] ?>">
                <div class="w-full h-8 rounded bg-gradient-to-br <?= $t['gradient'] ?> mb-2 ring-2 ring-transparent group-hover:ring-white/50 transition"></div>
                <span class="text-xs font-medium"><?= $t['name'] ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8" id="dashboard-container">
    
    <!-- Alert Messages -->
    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-900/50 border border-red-700 rounded-lg text-red-200 flex items-start gap-2">
            <i class="fas fa-exclamation-triangle mt-0.5"></i><span><?= $error ?></span>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-900/50 border border-green-700 rounded-lg text-green-200 flex items-start gap-2">
            <i class="fas fa-check-circle mt-0.5"></i><span><?= $success ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Status Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center hover:border-orange-500 transition">
            <i class="fas fa-fire text-orange-500 text-2xl mb-2"></i>
            <p class="text-3xl font-bold"><?= $streak ?></p>
            <p class="text-xs text-gray-400">Hari Streak</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center hover:border-yellow-500 transition">
            <i class="fas fa-exclamation-triangle text-<?= $remaining_quota <= 1 ? 'red' : 'yellow' ?>-500 text-2xl mb-2"></i>
            <p class="text-3xl font-bold"><?= $remaining_quota ?>/<?= $user['skip_quota_total'] ?></p>
            <p class="text-xs text-gray-400">Kuota Bolos</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center hover:border-blue-500 transition">
            <i class="fas fa-calendar-days text-blue-400 text-2xl mb-2"></i>
            <p class="text-3xl font-bold"><?= $remaining_days ?></p>
            <p class="text-xs text-gray-400">Hari Tersisa</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center hover:border-yellow-500 transition">
            <i class="fas fa-crown text-yellow-400 text-2xl mb-2"></i>
            <p class="text-lg font-bold"><?= ucfirst($user['subscription_type']) ?></p>
            <p class="text-xs text-gray-400"><?= $user['subscription_end'] ? 'Hingga ' . formatDateIndo($user['subscription_end']) : '-' ?></p>
        </div>
    </div>
    
    <!-- Warning if low quota -->
    <?php if ($remaining_quota <= 1 && $user['status'] !== 'kicked'): ?>
        <div class="mb-6 p-4 bg-gradient-to-r from-red-900/50 to-orange-900/50 border-2 border-red-600 rounded-xl animate-pulse-slow">
            <div class="flex items-start gap-3">
                <i class="fas fa-skull text-red-400 text-xl mt-1"></i>
                <div>
                    <h3 class="font-bold text-red-300"><i class="fas fa-exclamation-triangle mr-2"></i>PERINGATAN KERAS!</h3>
                    <p class="text-sm text-gray-300 mt-1">
                        Kuota bolos lo tinggal <span class="font-bold text-red-400"><?= $remaining_quota ?>x</span> lagi! 
                        Kalau habis, lo bakal AUTO KICK dari grup + BLACKLIST. 
                        <span class="text-orange-400 font-semibold">Tidak ada refund!</span>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Daily Report -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-700/50 border-b border-gray-600">
                    <h2 class="font-bold text-lg"><i class="fas fa-clipboard-list mr-2"></i>Laporan Pertempuran Harian Lo</h2>
                </div>
                <div class="p-6">
                    <?php if ($today_report): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                            <p class="text-gray-300">Lo sudah kirim laporan hari ini!</p>
                            <p class="text-sm text-gray-500 mt-1">Target: <?= sanitize($today_report['main_target']) ?></p>
                            <p class="text-sm text-gray-500">Status: 
                                <span class="px-2 py-0.5 rounded text-xs font-bold <?= 
                                    $today_report['completion_status'] === 'completed_100' ? 'bg-green-900 text-green-300' : 
                                    ($today_report['completion_status'] === 'partial' ? 'bg-yellow-900 text-yellow-300' : 'bg-red-900 text-red-300')
                                ?>">
                                    <?= $today_report['completion_status'] === 'completed_100' ? '<i class="fas fa-check mr-1"></i>100% Selesai' : 
                                        ($today_report['completion_status'] === 'partial' ? '<i class="fas fa-spinner mr-1"></i>Sebagian' : '<i class="fas fa-times mr-1"></i>Gagal') ?>
                                </span>
                            </p>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Apa target utama lo hari ini? <span class="text-red-400">*</span></label>
                                <textarea name="main_target" rows="3" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition" placeholder="Contoh: Edit 3 Video Reels" required><?= sanitize($_POST['main_target'] ?? '') ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Tuliskan minimal 1 target utama.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Apakah target hari ini sudah selesai? <span class="text-red-400">*</span></label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 p-3 bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                        <input type="radio" name="completion_status" value="completed_100" class="text-orange-500 focus:ring-orange-500" required>
                                        <span class="text-sm"><i class="fas fa-check-circle text-green-400 mr-1"></i>Sudah Selesai 100%</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                        <input type="radio" name="completion_status" value="partial" class="text-orange-500 focus:ring-orange-500">
                                        <span class="text-sm"><i class="fas fa-spinner text-yellow-400 mr-1"></i>Selesai Sebagian</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                        <input type="radio" name="completion_status" value="failed" class="text-orange-500 focus:ring-orange-500">
                                        <span class="text-sm"><i class="fas fa-times-circle text-red-400 mr-1"></i>Gagal Total</span>
                                    </label>
                                </div>
                            </div>
                            <div id="progress-note-field" class="hidden">
                                <label class="block text-sm font-medium mb-2">Tulis progress lo di bawah:</label>
                                <textarea name="progress_note" rows="2" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition" placeholder="Contoh: Sudah 2 dari 3 video, sisa 1 besok..."><?= sanitize($_POST['progress_note'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Link Bukti / Portofolio <span class="text-gray-500">(Opsional)</span></label>
                                <input type="url" name="portfolio_link" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition" placeholder="https://drive.google.com/..." value="<?= sanitize($_POST['portfolio_link'] ?? '') ?>">
                                <p class="text-xs text-gray-500 mt-1">Share link karya, drive, atau screenshot. (link aja, jangan gambar)</p>
                            </div>
                            <button type="submit" name="submit_report" class="w-full py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-lg font-bold transition">
                                <i class="fas fa-paper-plane mr-2"></i>KIRIM LAPORAN
                            </button>
                            <p class="text-xs text-center text-gray-500">Pastikan semua data benar sebelum kirim.</p>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Dreams CRUD -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-700/50 border-b border-gray-600 flex items-center justify-between">
                    <h2 class="font-bold text-lg"><i class="fas fa-bullseye mr-2"></i>Impian Lo</h2>
                    <button onclick="toggleDreamForm()" class="text-sm text-orange-400 hover:underline">
                        <i class="fas fa-plus mr-1"></i>Tambah Impian
                    </button>
                </div>
                <div class="p-6">
                    <form method="POST" id="dream-form" class="hidden mb-6 p-4 bg-gray-700/30 rounded-lg space-y-3">
                        <input type="hidden" name="dream_id" id="dream-id">
                        <input type="text" name="dream_title" id="dream-title" placeholder="Judul impian lo..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" required>
                        <textarea name="dream_description" id="dream-desc" placeholder="Deskripsi singkat (opsional)..." rows="2" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none"></textarea>
                        <div>
                            <label class="text-sm text-gray-400">Status:</label>
                            <select name="dream_status" id="dream-status" class="mt-1 px-3 py-1 bg-gray-700 border border-gray-600 rounded-lg text-sm">
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="abandoned">Abandoned</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" name="add_dream" id="dream-submit-add" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 rounded-lg text-sm font-medium transition">Simpan Impian</button>
                            <button type="submit" name="edit_dream" id="dream-submit-edit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-medium transition hidden">Update Impian</button>
                            <button type="button" onclick="resetDreamForm()" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-sm font-medium transition">Batal</button>
                        </div>
                    </form>
                    
                    <?php if (empty($dreams)): ?>
                        <p class="text-center text-gray-500 py-8"><i class="fas fa-inbox mr-2"></i>Belum ada impian yang dicatat. Yuk mulai sekarang!</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($dreams as $dream): ?>
                                <div class="p-4 bg-gray-700/30 rounded-lg border border-gray-600 group">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-semibold"><?= sanitize($dream['dream_title']) ?></h4>
                                            <?php if ($dream['dream_description']): ?>
                                                <p class="text-sm text-gray-400 mt-1"><?= sanitize($dream['dream_description']) ?></p>
                                            <?php endif; ?>
                                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-clock mr-1"></i>Ditambahkan: <?= formatDateIndo($dream['created_at']) ?></p>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="px-2 py-1 rounded text-xs font-bold <?= 
                                                $dream['status'] === 'completed' ? 'bg-green-900 text-green-300' : 
                                                ($dream['status'] === 'abandoned' ? 'bg-red-900 text-red-300' : 'bg-blue-900 text-blue-300')
                                            ?>">
                                                <?= $dream['status'] === 'completed' ? '<i class="fas fa-check mr-1"></i>Done' : 
                                                    ($dream['status'] === 'abandoned' ? '<i class="fas fa-times mr-1"></i>Stop' : '<i class="fas fa-spinner mr-1"></i>Progress') ?>
                                            </span>
                                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                                <button onclick="editDream(<?= htmlspecialchars(json_encode($dream)) ?>)" class="p-1 text-blue-400 hover:text-blue-300" title="Edit"><i class="fas fa-edit"></i></button>
                                                <form method="POST" onsubmit="return confirm('Hapus impian ini?')" class="inline">
                                                    <input type="hidden" name="dream_id" value="<?= $dream['id'] ?>">
                                                    <button type="submit" name="delete_dream" class="p-1 text-red-400 hover:text-red-300" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Milestones -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-700/50 border-b border-gray-600">
                    <h2 class="font-bold text-lg"><i class="fas fa-trophy mr-2 text-yellow-400"></i>Milestones & Achievements</h2>
                </div>
                <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($milestones as $m): ?>
                        <div class="p-3 rounded-lg border <?= $m['achieved'] ? 'bg-green-900/30 border-green-700' : 'bg-gray-700/30 border-gray-600' ?> text-center transition hover:scale-[1.02]">
                            <i class="fas <?= $m['icon'] ?> <?= $m['achieved'] ? 'text-yellow-400' : 'text-gray-500' ?> text-xl mb-2"></i>
                            <p class="text-xs font-semibold <?= $m['achieved'] ? 'text-green-300' : 'text-gray-400' ?>"><?= $m['title'] ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?= $m['desc'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Right Column: Sidebar -->
        <div class="space-y-6">
            
            <!-- Pomodoro Timer with Settings -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600 flex items-center justify-between">
                    <h3 class="font-semibold text-sm"><i class="fas fa-stopwatch mr-2"></i>Pomodoro Timer</h3>
                    <button onclick="togglePomodoroSettings()" class="text-gray-400 hover:text-white" title="Setting"><i class="fas fa-cog"></i></button>
                </div>
                <div class="p-4 text-center">
                    <div id="pomodoro-display" class="text-4xl font-mono font-bold mb-4">25:00</div>
                    <div class="flex justify-center gap-2 mb-3">
                        <button onclick="pomodoroStart()" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm transition"><i class="fas fa-play mr-1"></i>Start</button>
                        <button onclick="pomodoroPause()" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-lg text-sm transition"><i class="fas fa-pause mr-1"></i>Pause</button>
                        <button onclick="pomodoroReset()" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-sm transition"><i class="fas fa-redo mr-1"></i>Reset</button>
                    </div>
                    <p class="text-xs text-gray-500">
                        <span id="pomodoro-mode">Fokus</span> • 
                        <span id="pomodoro-cycle">Cycle 1</span>
                    </p>
                </div>
            </div>
            
            <!-- Todo List CRUD -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600 flex items-center justify-between">
                    <h3 class="font-semibold text-sm"><i class="fas fa-tasks mr-2"></i>Todo List</h3>
                    <button onclick="addTodoPrompt()" class="text-xs text-orange-400 hover:underline"><i class="fas fa-plus"></i> Tambah</button>
                </div>
                <div class="p-4">
                    <ul id="todo-list" class="space-y-2 max-h-48 overflow-y-auto">
                        <!-- Loaded via JS -->
                    </ul>
                    <p id="todo-empty" class="text-center text-gray-500 text-sm py-4 hidden"><i class="fas fa-clipboard mr-1"></i>Belum ada todo</p>
                </div>
            </div>
            
            <!-- Recent Reports -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600">
                    <h3 class="font-semibold text-sm"><i class="fas fa-chart-line mr-2"></i>Progress 7 Hari</h3>
                </div>
                <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                    <?php if (empty($recent_reports)): ?>
                        <p class="text-center text-gray-500 text-sm py-4"><i class="fas fa-inbox mr-1"></i>Belum ada laporan</p>
                    <?php else: 
                        foreach ($recent_reports as $report): ?>
                            <div class="p-3 bg-gray-700/30 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-400"><?= formatDateIndo($report['report_date'], 'd M') ?></span>
                                    <span class="px-2 py-0.5 rounded text-xs font-bold <?= 
                                        $report['completion_status'] === 'completed_100' ? 'bg-green-900 text-green-300' : 
                                        ($report['completion_status'] === 'partial' ? 'bg-yellow-900 text-yellow-300' : 'bg-red-900 text-red-300')
                                    ?>">
                                        <?= $report['completion_status'] === 'completed_100' ? '<i class="fas fa-check"></i>' : ($report['completion_status'] === 'partial' ? '<i class="fas fa-spinner"></i>' : '<i class="fas fa-times"></i>') ?>
                                    </span>
                                </div>
                                <p class="text-sm truncate"><?= sanitize($report['main_target']) ?></p>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
            
            <!-- Pricing -->
            <?php if ($user['status'] !== 'kicked'): ?>
                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600">
                        <h3 class="font-semibold text-sm"><i class="fas fa-crown mr-2"></i>Perpanjang Subscription</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-400">Kuota bolos habis? Mau upgrade paket?</p>
                        <div class="space-y-2">
                            <?php 
                            $admin_wa = $settings['admin_whatsapp'] ?? '6281234567890';
                            $packages_mini = [
                                ['name' => 'Monthly', 'price' => $pricing['monthly']['price'] * 0.95, 'quota' => $pricing['monthly']['skip_quota']],
                                ['name' => 'Quarterly', 'price' => $pricing['quarterly']['price'] * 0.95, 'quota' => $pricing['quarterly']['skip_quota']],
                                ['name' => 'Yearly', 'price' => $pricing['yearly']['price'] * 0.95, 'quota' => $pricing['yearly']['skip_quota']],
                            ];
                            foreach ($packages_mini as $pkg): 
                                $msg = urlencode("Halo admin, saya {$user['full_name']} (WA: {$user['whatsapp_number']}) mau perpanjang paket {$pkg['name']}");
                            ?>
                                <a href="<?= waLink($admin_wa, $msg) ?>" target="_blank" class="flex items-center justify-between p-3 bg-gray-700/50 hover:bg-gray-700 rounded-lg transition group">
                                    <div>
                                        <p class="font-medium text-sm"><?= $pkg['name'] ?></p>
                                        <p class="text-xs text-gray-400">Kuota bolos: <?= $pkg['quota'] ?>x</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-sm">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></p>
                                        <p class="text-xs text-orange-400 group-hover:underline"><i class="fas fa-arrow-right"></i></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Account Info -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600">
                    <h3 class="font-semibold text-sm"><i class="fas fa-cog mr-2"></i>Info Akun</h3>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">WhatsApp:</span><span><?= sanitize($user['whatsapp_number']) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Discord:</span><span><?= $user['discord_username'] ? sanitize($user['discord_username']) : '<span class="text-orange-400">Belum diisi</span>' ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Status:</span><span class="font-bold <?= $user['status'] === 'kicked' ? 'text-red-400' : 'text-green-400' ?>"><?= ucfirst($user['status']) ?></span></div>
                    <?php if ($user['status'] === 'kicked'): ?>
                        <div class="p-3 bg-red-900/30 rounded border border-red-700 text-red-300 text-xs">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Akun di-KICK karena kuota bolos habis. Hubungi admin via WhatsApp. <strong>Tidak ada refund!</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
    
</div>

<!-- ===== JAVASCRIPT: ALL FEATURES ===== -->
<script>
// ===== GLOBAL CONFIG =====
const userId = <?= json_encode($user['id']) ?>;
const defaultTheme = 'default';

// ===== TUTORIAL SYSTEM =====
let currentStep = 1;
const totalSteps = 8;

function showTutorial() {
    document.getElementById('tutorial-modal').classList.remove('hidden');
    currentStep = 1;
    updateTutorialStep();
    document.body.style.overflow = 'hidden';
}

function closeTutorial() {
    document.getElementById('tutorial-modal').classList.add('hidden');
    localStorage.setItem('tutorial_completed', 'true');
    document.body.style.overflow = '';
}

function skipTutorial() {
    closeTutorial();
}

function changeTutorialStep(delta) {
    currentStep += delta;
    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;
    updateTutorialStep();
}

function updateTutorialStep() {
    document.querySelectorAll('.tutorial-step').forEach(el => {
        el.classList.toggle('hidden', parseInt(el.dataset.step) !== currentStep);
    });
    document.querySelectorAll('.tutorial-dot').forEach(dot => {
        const dotNum = parseInt(dot.dataset.dot);
        dot.classList.toggle('bg-orange-500', dotNum === currentStep);
        dot.classList.toggle('bg-gray-600', dotNum !== currentStep);
    });
    document.getElementById('tutorial-prev').classList.toggle('hidden', currentStep === 1);
    document.getElementById('tutorial-next').classList.toggle('hidden', currentStep === totalSteps);
    document.getElementById('tutorial-finish').classList.toggle('hidden', currentStep !== totalSteps);
}

// ===== THEME SYSTEM - FULL BACKGROUND + ACCENT =====
const themes = {
    'default': { 
        primary: 'from-orange-600 to-red-600', 
        accent: 'orange', 
        bg: 'bg-warrior-dark',
        card: 'bg-gray-800',
        border: 'border-gray-700',
        text: 'text-gray-100'
    },
    'ocean': { 
        primary: 'from-blue-600 to-cyan-600', 
        accent: 'blue', 
        bg: 'bg-slate-900',
        card: 'bg-slate-800',
        border: 'border-slate-700',
        text: 'text-slate-100'
    },
    'forest': { 
        primary: 'from-green-600 to-emerald-600', 
        accent: 'green', 
        bg: 'bg-green-950',
        card: 'bg-green-900',
        border: 'border-green-800',
        text: 'text-green-100'
    },
    'sunset': { 
        primary: 'from-yellow-500 to-orange-600', 
        accent: 'yellow', 
        bg: 'bg-orange-950',
        card: 'bg-orange-900',
        border: 'border-orange-800',
        text: 'text-orange-100'
    },
    'midnight': { 
        primary: 'from-purple-600 to-indigo-600', 
        accent: 'purple', 
        bg: 'bg-indigo-950',
        card: 'bg-indigo-900',
        border: 'border-indigo-800',
        text: 'text-indigo-100'
    },
    'rose': { 
        primary: 'from-pink-500 to-rose-600', 
        accent: 'pink', 
        bg: 'bg-rose-950',
        card: 'bg-rose-900',
        border: 'border-rose-800',
        text: 'text-rose-100'
    },
};

function setTheme(themeName) {
    const theme = themes[themeName] || themes['default'];
    localStorage.setItem('dashboard_theme', themeName);
    applyTheme(theme);
    toggleThemePicker();
    showToast('Tema berhasil diubah!', 'success');
}

function applyTheme(theme) {
    // Apply body background
    document.body.className = document.body.className.replace(/bg-\w+-\d+/, theme.bg);
    
    // Apply card backgrounds
    document.querySelectorAll('.bg-gray-800').forEach(el => {
        if (!el.classList.contains('no-theme')) {
            el.className = el.className.replace(/bg-\w+-\d+/, theme.card);
        }
    });
    
    // Apply borders
    document.querySelectorAll('.border-gray-700').forEach(el => {
        el.className = el.className.replace(/border-\w+-\d+/, theme.border);
    });
    
    // Apply primary gradient buttons
    document.querySelectorAll('.bg-gradient-to-r').forEach(el => {
        if (el.classList.contains('from-orange-600') || el.classList.contains('from-blue-600') || el.classList.contains('from-green-600')) {
            el.className = el.className.replace(/from-\w+-\d+\s+to-\w+-\d+/, theme.primary);
        }
    });
    
    // Apply accent colors for icons/text
    document.querySelectorAll('.text-orange-400, .text-orange-500, .text-blue-400, .text-green-400, .text-yellow-400, .text-purple-400, .text-pink-400').forEach(el => {
        if (el.classList.contains('text-orange-400') || el.classList.contains('text-orange-500')) {
            el.className = el.className.replace(/text-\w+-[45]00/, `text-${theme.accent}-400`);
        }
    });
    
    // Update theme picker active state
    document.querySelectorAll('#theme-picker button').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-white');
    });
}

function loadTheme() {
    const themeName = localStorage.getItem('dashboard_theme') || defaultTheme;
    const theme = themes[themeName];
    if (theme) applyTheme(theme);
}

function toggleThemePicker() {
    const picker = document.getElementById('theme-picker');
    picker.classList.toggle('hidden');
}

// Close theme picker on outside click
document.addEventListener('click', (e) => {
    const picker = document.getElementById('theme-picker');
    const btn = e.target.closest('[onclick*="toggleThemePicker"]');
    if (!picker.contains(e.target) && !btn && !picker.classList.contains('hidden')) {
        picker.classList.add('hidden');
    }
});

// ===== POMODORO TIMER WITH CRUD SETTINGS =====
let pomodoroInterval = null;
let pomodoroTime = 25 * 60;
let pomodoroRunning = false;
let pomodoroMode = 'work'; // 'work' or 'break'
let pomodoroCycle = 1;
let pomodoroSettings = {
    work: 25,
    break: 5,
    autoBreak: false
};

function loadPomodoroSettings() {
    const saved = localStorage.getItem('pomodoro_settings_' + userId);
    if (saved) {
        pomodoroSettings = JSON.parse(saved);
    }
    updatePomodoroDisplay();
}

function savePomodoroSettings() {
    localStorage.setItem('pomodoro_settings_' + userId, JSON.stringify(pomodoroSettings));
}

function resetPomodoroSettings() {
    pomodoroSettings = { work: 25, break: 5, autoBreak: false };
    document.getElementById('pomodoro-work').value = 25;
    document.getElementById('pomodoro-break').value = 5;
    document.getElementById('pomodoro-auto-break').checked = false;
    savePomodoroSettings();
    pomodoroReset();
    showToast('Setting Pomodoro direset ke default', 'info');
}

function togglePomodoroSettings() {
    const modal = document.getElementById('pomodoro-settings-modal');
    if (modal.classList.contains('hidden')) {
        // Load current settings to form
        document.getElementById('pomodoro-work').value = pomodoroSettings.work;
        document.getElementById('pomodoro-break').value = pomodoroSettings.break;
        document.getElementById('pomodoro-auto-break').checked = pomodoroSettings.autoBreak;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Handle pomodoro settings form submit
document.getElementById('pomodoro-settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    pomodoroSettings.work = parseInt(document.getElementById('pomodoro-work').value) || 25;
    pomodoroSettings.break = parseInt(document.getElementById('pomodoro-break').value) || 5;
    pomodoroSettings.autoBreak = document.getElementById('pomodoro-auto-break').checked;
    savePomodoroSettings();
    pomodoroReset();
    togglePomodoroSettings();
    showToast('Setting Pomodoro berhasil disimpan!', 'success');
});

function pomodoroStart() {
    if (pomodoroRunning) return;
    pomodoroRunning = true;
    
    pomodoroInterval = setInterval(() => {
        pomodoroTime--;
        updatePomodoroDisplay();
        
        if (pomodoroTime <= 0) {
            clearInterval(pomodoroInterval);
            pomodoroRunning = false;
            
            if (pomodoroMode === 'work') {
                // Work session finished
                showToast('Waktu fokus selesai! Istirahat sebentar.', 'success');
                if (pomodoroSettings.autoBreak) {
                    pomodoroMode = 'break';
                    pomodoroTime = pomodoroSettings.break * 60;
                    updatePomodoroDisplay();
                    setTimeout(pomodoroStart, 1000); // Auto start break
                }
            } else {
                // Break finished
                showToast('Istirahat selesai! Kembali fokus.', 'success');
                pomodoroMode = 'work';
                pomodoroTime = pomodoroSettings.work * 60;
                pomodoroCycle++;
                updatePomodoroDisplay();
            }
        }
    }, 1000);
}

function pomodoroPause() {
    clearInterval(pomodoroInterval);
    pomodoroRunning = false;
}

function pomodoroReset() {
    pomodoroPause();
    pomodoroMode = 'work';
    pomodoroTime = pomodoroSettings.work * 60;
    pomodoroCycle = 1;
    updatePomodoroDisplay();
}

function updatePomodoroDisplay() {
    const m = Math.floor(pomodoroTime / 60).toString().padStart(2, '0');
    const s = (pomodoroTime % 60).toString().padStart(2, '0');
    document.getElementById('pomodoro-display').textContent = `${m}:${s}`;
    document.getElementById('pomodoro-mode').textContent = pomodoroMode === 'work' ? 'Fokus' : 'Istirahat';
    document.getElementById('pomodoro-cycle').textContent = `Cycle ${pomodoroCycle}`;
}

// ===== TODO LIST CRUD (localStorage) =====
function loadTodos() {
    const todos = JSON.parse(localStorage.getItem('todos_' + userId) || '[]');
    const list = document.getElementById('todo-list');
    const empty = document.getElementById('todo-empty');
    
    list.innerHTML = '';
    if (todos.length === 0) {
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');
    
    todos.forEach((todo, idx) => {
        const li = document.createElement('li');
        li.className = 'flex items-center gap-2 p-2 bg-gray-700/30 rounded hover:bg-gray-700/50 transition';
        li.innerHTML = `
            <input type="checkbox" ${todo.done ? 'checked' : ''} onchange="toggleTodo(${idx})" class="rounded text-orange-500 focus:ring-orange-500">
            <span class="${todo.done ? 'line-through text-gray-500' : 'text-gray-200'} flex-1 text-sm break-words">${escapeHtml(todo.text)}</span>
            <button onclick="deleteTodo(${idx})" class="text-red-400 hover:text-red-300 p-1" title="Hapus"><i class="fas fa-times"></i></button>
        `;
        list.appendChild(li);
    });
}

function addTodoPrompt() {
    const text = prompt('Tambah todo baru:');
    if (text && text.trim()) {
        const todos = JSON.parse(localStorage.getItem('todos_' + userId) || '[]');
        todos.unshift({ text: text.trim(), done: false, created: new Date().toISOString() });
        localStorage.setItem('todos_' + userId, JSON.stringify(todos));
        loadTodos();
        showToast('Todo berhasil ditambahkan!', 'success');
    }
}

function toggleTodo(idx) {
    const todos = JSON.parse(localStorage.getItem('todos_' + userId) || '[]');
    if (todos[idx]) {
        todos[idx].done = !todos[idx].done;
        todos[idx].updated = new Date().toISOString();
        localStorage.setItem('todos_' + userId, JSON.stringify(todos));
        loadTodos();
    }
}

function deleteTodo(idx) {
    if (confirm('Hapus todo ini?')) {
        const todos = JSON.parse(localStorage.getItem('todos_' + userId) || '[]');
        todos.splice(idx, 1);
        localStorage.setItem('todos_' + userId, JSON.stringify(todos));
        loadTodos();
        showToast('Todo dihapus', 'info');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== DREAMS FORM =====
function toggleDreamForm() {
    const form = document.getElementById('dream-form');
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        resetDreamForm();
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function resetDreamForm() {
    document.getElementById('dream-id').value = '';
    document.getElementById('dream-title').value = '';
    document.getElementById('dream-desc').value = '';
    document.getElementById('dream-status').value = 'in_progress';
    document.getElementById('dream-submit-add').classList.remove('hidden');
    document.getElementById('dream-submit-edit').classList.add('hidden');
}

function editDream(dream) {
    document.getElementById('dream-id').value = dream.id;
    document.getElementById('dream-title').value = dream.dream_title;
    document.getElementById('dream-desc').value = dream.dream_description || '';
    document.getElementById('dream-status').value = dream.status;
    document.getElementById('dream-submit-add').classList.add('hidden');
    document.getElementById('dream-submit-edit').classList.remove('hidden');
    document.getElementById('dream-form').classList.remove('hidden');
    document.getElementById('dream-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ===== PROGRESS NOTE TOGGLE =====
document.querySelectorAll('input[name="completion_status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const noteField = document.getElementById('progress-note-field');
        if (this.value === 'partial' || this.value === 'failed') {
            noteField.classList.remove('hidden');
        } else {
            noteField.classList.add('hidden');
        }
    });
});

// ===== TOAST NOTIFICATION =====
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-gray-700',
        warning: 'bg-yellow-600'
    };
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg ${colors[type] || colors.info} text-white z-50 animate-slide-up`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== INIT ON LOAD =====
document.addEventListener('DOMContentLoaded', () => {
    // Load saved preferences
    loadTheme();
    loadPomodoroSettings();
    loadTodos();
    
    // Show tutorial if first time
    if (!localStorage.getItem('tutorial_completed')) {
        setTimeout(showTutorial, 500);
    }
    
    // Add CSS animation for toast
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slide-up 0.3s ease-out; }
    `;
    document.head.appendChild(style);
});
</script>

<?php require_once 'includes/footer.php'; ?>