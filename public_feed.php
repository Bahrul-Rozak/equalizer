<?php
// public_feed.php - Live Activity Feed (Public)
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Live Feed';

// Pagination config
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Get total items
$total = dbFetch("SELECT COUNT(*) as c FROM live_feed WHERE is_public = TRUE")['c'];

// Get feeds with pagination
$feeds = dbFetchAll("
    SELECT lf.*, u.full_name, u.whatsapp_number 
    FROM live_feed lf 
    LEFT JOIN users u ON lf.user_id = u.id 
    WHERE lf.is_public = TRUE 
    ORDER BY lf.timestamp DESC 
    LIMIT ? OFFSET ?
", [$per_page, $offset]);

$pagination = getPagination($total, $per_page, $page, 'public_feed.php');
?>

<?php require_once 'includes/header.php'; ?>

<!-- Feed Header -->
<div class="bg-gradient-to-r from-warrior-dark via-gray-900 to-warrior-dark border-b border-gray-700">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-bolt text-yellow-400 mr-2"></i>Live Activity Feed
                </h1>
                <p class="text-gray-400 text-sm">Medan Perang Sedang Berlangsung • Real-time</p>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                <i class="fas fa-home mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Feed Content -->
<div class="max-w-4xl mx-auto px-4 py-8">
    
    <!-- Feed List -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        
        <?php if (empty($feeds)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                <p>Belum ada activity...</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-700">
                <?php foreach ($feeds as $feed): 
                    // Determine icon & color based on action type
                    $icon_info = match($feed['action_type']) {
                        'report_submitted' => ['icon' => 'fa-clipboard-check', 'color' => 'green', 'label' => 'Laporan'],
                        'user_kicked' => ['icon' => 'fa-user-slash', 'color' => 'red', 'label' => 'Di-KICK'],
                        'skip_warning_1' => ['icon' => 'fa-exclamation-triangle', 'color' => 'yellow', 'label' => 'Peringatan 1'],
                        'skip_warning_2' => ['icon' => 'fa-exclamation-triangle', 'color' => 'orange', 'label' => 'Peringatan 2'],
                        'skip_warning_3' => ['icon' => 'fa-skull', 'color' => 'red', 'label' => 'Peringatan 3'],
                        'subscription_extended' => ['icon' => 'fa-crown', 'color' => 'yellow', 'label' => 'Subscribe'],
                        'user_added' => ['icon' => 'fa-user-plus', 'color' => 'blue', 'label' => 'User Baru'],
                        'quota_restored' => ['icon' => 'fa-undo', 'color' => 'blue', 'label' => 'Kuota Dikembalikan'],
                        'trial_started' => ['icon' => 'fa-gift', 'color' => 'purple', 'label' => 'Trial Dimulai'],
                        'user_login' => ['icon' => 'fa-sign-in-alt', 'color' => 'gray', 'label' => 'Login'],
                        default => ['icon' => 'fa-circle', 'color' => 'gray', 'label' => 'Aktivitas'],
                    };
                ?>
                    <div class="px-6 py-4 hover:bg-gray-700/30 transition">
                        <div class="flex items-start gap-4">
                            <!-- Icon Badge -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-<?= $icon_info['color'] ?>-900/50 border border-<?= $icon_info['color'] ?>-700 flex items-center justify-center">
                                    <i class="fas fa-<?= $icon_info['icon'] ?> text-<?= $icon_info['color'] ?>-400"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-<?= $icon_info['color'] ?>-900/50 text-<?= $icon_info['color'] ?>-300">
                                        <?= $icon_info['label'] ?>
                                    </span>
                                    <span class="text-xs text-gray-500"><?= formatDateIndo($feed['timestamp'], 'H:i') ?></span>
                                </div>
                                <p class="text-gray-200"><?= sanitize($feed['action_description']) ?></p>
                                
                                <!-- Additional info for skip warnings -->
                                <?php if (str_starts_with($feed['action_type'], 'skip_warning')): 
                                    $warning_level = (int)substr($feed['action_type'], -1);
                                ?>
                                    <div class="mt-2 p-2 bg-<?= $warning_level === 3 ? 'red' : ($warning_level === 2 ? 'orange' : 'yellow') ?>-900/30 rounded border border-<?= $warning_level === 3 ? 'red' : ($warning_level === 2 ? 'orange' : 'yellow') ?>-700/50">
                                        <p class="text-xs text-<?= $warning_level === 3 ? 'red' : ($warning_level === 2 ? 'orange' : 'yellow') ?>-300">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <?= $warning_level === 1 ? '⚠️ Peringatan 1: Jangan diulang!' : 
                                                ($warning_level === 2 ? '🚨 Peringatan 2: 1x lagi = KICK!' : '💀 PERINGATAN TERAKHIR: Bolos sekali lagi = AUTO KICK!') ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Pagination -->
    <?= $pagination ?>
    
    <!-- Auto-refresh hint -->
    <div class="mt-6 text-center text-xs text-gray-500">
        <i class="fas fa-sync-alt mr-1"></i> Feed update otomatis setiap ada aktivitas baru
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>