<?php
// index.php - Landing Page Warrior Produktif (ENHANCED)
require_once 'config/database.php';
require_once 'includes/functions.php';

$settings = getSettings();
$pricing = getPricingInfo();
$page_title = 'Home';
?>

<?php require_once 'includes/header.php'; ?>

<!-- ===== HERO SECTION ===== -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-warrior-dark via-warrior-dark/95 to-warrior-dark"></div>
    
    <!-- Animated Background Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-orange-600/10 rounded-full blur-3xl animate-pulse-slow"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-red-600/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
    
    <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">
        
        <!-- Streak Notification Banner -->
        <div class="mb-6 flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-green-900/50 to-emerald-900/50 border border-green-700 rounded-full w-fit mx-auto animate-pulse">
            <i class="fas fa-fire text-orange-500"></i>
            <span class="text-sm text-green-300">Streak Baru: Member mencapai 30 hari streak!</span>
        </div>
        
        <!-- Main Title -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-black mb-4">
                <span class="text-gradient">WARRIOR PRODUKTIF</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                Login <span class="text-gray-600 mx-2">•</span> Ranking <span class="text-gray-600 mx-2">•</span> Feed
            </p>
            
            <!-- Login Button -->
            <?php if (isLoggedIn()): ?>
                <a href="user_dashboard.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-xl font-bold text-lg transition transform hover:scale-105 shadow-lg shadow-orange-900/20">
                    <i class="fas fa-dashboard mr-2"></i> BUKA DASHBOARD
                </a>
            <?php else: ?>
                <a href="login.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-xl font-bold text-lg transition transform hover:scale-105 shadow-lg shadow-orange-900/20">
                    <i class="fas fa-sign-in-alt mr-2"></i> LOGIN SEKARANG
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Brutal Warning Box -->
        <div class="max-w-4xl mx-auto bg-gradient-to-br from-red-900/30 to-orange-900/30 border-2 border-red-700 rounded-2xl p-6 md:p-8 mb-16">
            <div class="text-center">
                <h2 class="text-2xl md:text-3xl font-black text-red-400 mb-4">
                    <i class="fas fa-skull mr-2"></i>PERINGKAT 1: KUBURAN KEMALASAN
                </h2>
                <p class="text-lg md:text-xl text-gray-200 mb-4">
                    Lo <span class="text-orange-400 font-bold">PECUNDANG?</span><br>
                    <span class="text-red-500 font-bold"><i class="fas fa-times-circle mr-1"></i>TUTUP HALAMAN INI!!</span>
                </p>
                <p class="text-gray-300 leading-relaxed max-w-2xl mx-auto">
                    Lo punya ide, lo punya mimpi, lo punya potensi yang gila... tapi kenapa masih di tempat yang sama? 
                    Karena lo <span class="text-red-400 font-semibold">LEMAH</span>, anj*ng!
                </p>
                <p class="mt-4 text-gray-300 max-w-2xl mx-auto">
                    Gw gak jual kelas mahal, gak jual video motivasi bulsh*t, gak janji hasil instan. 
                    <span class="text-orange-400 font-bold">Gw jual PAKSAAN.</span> 
                    Lo butuh orang yang nerakin lo dari kasur, bukan influencer yang jago bacot doang!
                </p>
            </div>
        </div>
        
        <!-- CTA Button -->
        <div class="text-center">
            <a href="#cara-kerja" class="inline-flex items-center px-6 py-3 border-2 border-orange-600 text-orange-400 hover:bg-orange-600/20 rounded-lg font-semibold transition">
                <i class="fas fa-play-circle mr-2"></i> MATIIN RASA MALAS
            </a>
        </div>
        
    </div>
</section>

<!-- ===== STORY SECTION ===== -->
<section class="py-16 px-4 bg-gray-900/50">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center mb-8">Baca Pelan-Pelan... <span class="text-gradient">Ini Untuk Lo</span></h2>
        
        <div class="space-y-6 text-gray-300 leading-relaxed text-lg">
            <p>
                Lo baca halaman ini sambil rebahan, kan? Gak salah, gw juga dulu begitu. 
                Bukan lo salah, lo cuma manusia biasa yang kebobolan sama rasa nyaman yang <span class="text-red-400 font-semibold">BERACUN</span>.
            </p>
            <p>
                Lo punya ide bisnis yang bisa ubah hidup, tapi masih nongol di kepala doang. 
                Lo pengen body goals, tapi Netflix lebih manis. 
                Lo pengen keluar dari zona toxic, tapi takut tinggalin kenyamanan yang <span class="text-red-400 font-semibold">SEBENARNYA SIKSA</span>...
            </p>
            <p>
                Orang-orang di sekitar lo? Mereka bilang "sabar, semua butuh proses". 
                Padahal sabar yang mereka maksud adalah <span class="text-orange-400 font-semibold">TERIMA NASIB</span>. 
                Mereka bilang "jangan terlalu keras", padahal mereka yang bilang itu <span class="text-orange-400 font-semibold">MILIH JALAN MUDAH</span>!
            </p>
            <div class="bg-gray-800 p-4 rounded-lg border-l-4 border-orange-600 my-6">
                <p class="text-gray-200">
                    Lo tahu apa yang bikin gw kesal? Bukan orang malas, tapi orang yang 
                    <span class="text-red-400 font-bold">PUNYA KEMAMPUAN</span> tapi 
                    <span class="text-red-400 font-bold">MILIH MEMBUDAK</span> sama kemalasan sendiri. 
                    Itu yang namanya <span class="text-orange-400 font-black">PECUNDANG!</span>
                </p>
            </div>
            <p>
                Gw bikin sistem ini bukan buat lo yang cari hiburan, motivasi, atau temen kongkow. 
                Gw bikin ini buat lo yang udah <span class="text-red-400 font-bold">BENCI BANGET</span> sama versi diri lo yang sekarang. 
                Yang tiap malem nyesel, tiap pagi malas, tiap hari <span class="text-red-400 font-bold">MENYIKSA DIRI SENDIRI!</span>
            </p>
            <p class="text-center text-xl text-orange-300 font-semibold italic border-y border-orange-900/50 py-4 my-6">
                "Lo bukan orang bodoh, lo bukan orang lemah, lo cuma KEHILANGAN ALASAN UNTUK BERGERAK."
            </p>
            <p>
                Nah, lo lagi baca ini berarti ADA BAGIAN DIRI LO yang pengen berubah. 
                Bagian yang bilang "GUE BISA LEBIH DARI INI". 
                Bagian yang muak dengan rencana yang mangan di notes, mimpi yang mangan di kepala, potensi yang mangan terkubur!
            </p>
        </div>
        
        <div class="text-center mt-10">
            <p class="text-2xl font-bold text-gray-200 mb-6">
                LO MAU TETAP JADI BUDAK KEMALASAN ATAU LO MAU BANGUN DAN BERPERANG?
            </p>
            <a href="#pricing" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-xl font-bold text-lg transition transform hover:scale-105 animate-pulse shadow-lg shadow-orange-900/30">
                <i class="fas fa-fist-raised mr-2"></i> GUE SIAP BERPERANG
            </a>
        </div>
    </div>
</section>

<!-- ===== DREAM SHARE SECTION (NEW) ===== -->
<section class="py-16 px-4 bg-gradient-to-b from-warrior-dark to-gray-900">
    <div class="max-w-5xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-900/30 border border-purple-700 rounded-full text-purple-300 text-sm mb-6">
            <i class="fas fa-bullseye"></i>
            <span>Tempat Aman Buat Mimpi Lo</span>
        </div>
        
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            <span class="text-gradient">Gak Usah Takut Mimpi Lo Diketawain</span>
        </h2>
        <p class="text-gray-400 text-lg max-w-3xl mx-auto mb-8">
            Di sini, mimpi gila lo justru disambut. Karena yang join Warrior Produktif 
            bukan orang biasa — mereka orang yang MUAK sama versi diri mereka yang sekarang.
        </p>
        
        <div class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="p-6 bg-gray-800/50 rounded-xl border border-gray-700 hover:border-purple-600 transition">
                <i class="fas fa-shield-alt text-purple-400 text-3xl mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Zero Judgment</h3>
                <p class="text-sm text-gray-400">Gak ada yang ngejek. Semua di sini punya mimpi yang pernah diremehkan.</p>
            </div>
            <div class="p-6 bg-gray-800/50 rounded-xl border border-gray-700 hover:border-purple-600 transition">
                <i class="fas fa-users text-purple-400 text-3xl mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Komunitas Supportif</h3>
                <p class="text-sm text-gray-400">Warrior lain bakal dorong lo, bukan nahan. Kita naik bareng.</p>
            </div>
            <div class="p-6 bg-gray-800/50 rounded-xl border border-gray-700 hover:border-purple-600 transition">
                <i class="fas fa-trophy text-purple-400 text-3xl mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Buktiin Lo Bisa</h3>
                <p class="text-sm text-gray-400">Dashboard lo jadi bukti visual: progress, streak, achievement.</p>
            </div>
        </div>
        
        <!-- Dream Input Preview (Demo Only) -->
        <div class="max-w-xl mx-auto bg-gray-800 rounded-xl p-6 border border-gray-700">
            <p class="text-sm text-gray-400 mb-4">Contoh mimpi yang bisa lo catat di dashboard:</p>
            <div class="space-y-3 text-left">
                <div class="p-3 bg-gray-700/50 rounded-lg border-l-4 border-green-500">
                    <p class="font-medium text-gray-200">"Launch produk digital pertama dalam 90 hari"</p>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-check-circle text-green-400 mr-1"></i>Status: In Progress</p>
                </div>
                <div class="p-3 bg-gray-700/50 rounded-lg border-l-4 border-blue-500">
                    <p class="font-medium text-gray-200"> "Turun 10kg dengan konsisten olahraga"</p>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-spinner text-blue-400 mr-1"></i>Status: Progress</p>
                </div>
                <div class="p-3 bg-gray-700/50 rounded-lg border-l-4 border-yellow-500">
                    <p class="font-medium text-gray-200"> "Selesaikan skripsi sebelum wisuda"</p>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-check-circle text-yellow-400 mr-1"></i>Status: Completed</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4 italic">
                <i class="fas fa-lock mr-1"></i> Data impian lo privat. Cuma lo dan admin yang bisa akses.
            </p>
        </div>
        
        <div class="mt-10">
            <a href="#pricing" class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg font-semibold transition">
                <i class="fas fa-arrow-right mr-2"></i> Mulai Catat Mimpi Lo
            </a>
        </div>
    </div>
</section>

<!-- ===== SAVINGS CALCULATOR SECTION (NEW) ===== -->
<section class="py-16 px-4 bg-gray-900/50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-900/30 border border-green-700 rounded-full text-green-300 text-sm mb-4">
                <i class="fas fa-calculator"></i>
                <span>Hitung Penghematan Lo</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                <span class="text-gradient">Berapa Banyak Uang Lo Buang Sia-Sia?</span>
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Bandingkan biaya Warrior Produktif dengan beli buku/kelas produktivitas 
                yang ujung-ujungnya cuma jadi pajangan di rak.
            </p>
        </div>
        
        <!-- Calculator Card -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 md:p-8 mb-10">
            <div class="grid md:grid-cols-2 gap-8">
                
                <!-- Input Side -->
                <div class="space-y-6">
                    <h3 class="font-bold text-lg"><i class="fas fa-sliders-h mr-2"></i>Masukkan Data Lo</h3>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            <i class="fas fa-book mr-1 text-blue-400"></i>Buku Produktivitas yang Pernah Lo Beli
                        </label>
                        <div class="flex gap-2">
                            <input type="number" id="calc-books" value="3" min="0" max="50" 
                                   class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            <span class="px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-gray-300">x Rp 75.000</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Estimasi harga buku produktivitas: Rp 75.000/buku</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            <i class="fas fa-video mr-1 text-purple-400"></i>Kelas Online yang Pernah Lo Ikuti (tapi nggak selesai)
                        </label>
                        <div class="flex gap-2">
                            <input type="number" id="calc-courses" value="2" min="0" max="20" 
                                   class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            <span class="px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-gray-300">x Rp 250.000</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Estimasi harga kelas online: Rp 250.000/kelas</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            <i class="fas fa-apps mr-1 text-orange-400"></i>App Produktivitas Berlangganan yang Lo Lupa Cancel
                        </label>
                        <div class="flex gap-2">
                            <input type="number" id="calc-apps" value="1" min="0" max="10" 
                                   class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            <span class="px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-gray-300">x Rp 50.000/bln</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Estimasi langganan app: Rp 50.000/bulan</p>
                    </div>
                    
                    <button onclick="calculateSavings()" class="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 rounded-lg font-bold transition">
                        <i class="fas fa-calculator mr-2"></i>HITUNG PENGHEMATAN
                    </button>
                </div>
                
                <!-- Result Side -->
                <div class="space-y-6">
                    <h3 class="font-bold text-lg"><i class="fas fa-chart-line mr-2"></i>Hasil Perbandingan</h3>
                    
                    <!-- Wasted Money -->
                    <div class="p-4 bg-red-900/30 rounded-lg border border-red-700">
                        <p class="text-sm text-red-300 mb-1"><i class="fas fa-money-bill-wave mr-1"></i>Uang Terbuang Sia-Sia</p>
                        <p class="text-2xl font-bold text-red-400" id="result-wasted">Rp 725.000</p>
                        <p class="text-xs text-gray-500 mt-1">Dari buku + kelas + app yang nggak pernah dipake maksimal</p>
                    </div>
                    
                    <!-- Warrior Cost -->
                    <div class="p-4 bg-green-900/30 rounded-lg border border-green-700">
                        <p class="text-sm text-green-300 mb-1"><i class="fas fa-shield-alt mr-1"></i>Biaya Warrior Produktif</p>
                        <p class="text-2xl font-bold text-green-400" id="result-warrior">Rp 47.500/bln</p>
                        <p class="text-xs text-gray-500 mt-1">Termasuk: Dashboard, Grup Discord, Sistem Akuntabilitas, Support</p>
                    </div>
                    
                    <!-- Savings -->
                    <div class="p-4 bg-gradient-to-r from-green-900/50 to-emerald-900/50 rounded-lg border-2 border-green-600">
                        <p class="text-sm text-green-300 mb-1"><i class="fas fa-piggy-bank mr-1"></i>Penghematan Potensial</p>
                        <p class="text-3xl font-black text-green-400" id="result-savings">Rp 677.500/bln</p>
                        <p class="text-xs text-gray-400 mt-1">Dengan sistem yang MEMAKSA lo eksekusi, bukan cuma koleksi materi</p>
                    </div>
                    
                    <!-- Break-even -->
                    <div class="p-3 bg-gray-700/50 rounded-lg text-center">
                        <p class="text-sm text-gray-300">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            <strong id="result-breakeven">1 bulan</strong> 
                            pakai Warrior = balik modal dari uang yang biasa lo buang sia-sia
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Calculator Note -->
        <div class="text-center text-sm text-gray-500 max-w-2xl mx-auto">
            <p>
                <i class="fas fa-info-circle mr-1"></i> 
                Kalkulator ini estimasi berdasarkan rata-rata harga pasar. 
                Yang pasti: <span class="text-orange-400 font-semibold">Sistem tanpa eksekusi = uang hangus.</span> 
                Warrior Produktif = sistem yang MEMAKSA eksekusi.
            </p>
        </div>
    </div>
</section>

<!-- ===== FROM FOUNDER SECTION ===== -->
<section class="py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-4">Dari Founder</h2>
                <h3 class="text-xl text-orange-400 font-semibold mb-4">Kenapa Gw Bikin Sistem Ini</h3>
                <p class="text-gray-300 mb-4">
                    Bukan video motivasi, ini <span class="text-red-400 font-bold">KENYATAAN</span>
                </p>
                <p class="text-gray-400 mb-6">
                    Tonton sampai habis, gw jamin perspektif lo berubah.
                </p>
                
                <!-- YouTube Embed -->
                <div class="relative pb-[56.25%] h-0 rounded-xl overflow-hidden border-2 border-gray-700">
                    <iframe class="absolute top-0 left-0 w-full h-full" 
                            src="<?= sanitize($settings['youtube_video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ') ?>" 
                            title="Warrior Produktif - The Truth" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Cocok Untuk -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-green-700/50">
                    <h4 class="text-lg font-bold text-green-400 mb-3">
                        <i class="fas fa-check-circle mr-2"></i> COCOK UNTUK:
                    </h4>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li><i class="fas fa-code mr-2 text-blue-400"></i>Freelancer/programmer yang butuh deadline nyata</li>
                        <li><i class="fas fa-graduation-cap mr-2 text-blue-400"></i>Mahasiswa ambisius yang muak nunda-nunda</li>
                        <li><i class="fas fa-briefcase mr-2 text-blue-400"></i>Entrepreneur yang butuh akuntabilitas brutal</li>
                        <li><i class="fas fa-heart-broken mr-2 text-blue-400"></i>Orang yang BENCI versi diri sendiri yang sekarang</li>
                        <li><i class="fas fa-fire mr-2 text-blue-400"></i>Pecundang yang mau jadi LEGENDA</li>
                    </ul>
                </div>
                
                <!-- Minggir Kalau -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-red-700/50">
                    <h4 class="text-lg font-bold text-red-400 mb-3">
                        <i class="fas fa-times-circle mr-2"></i> MINGGIR KALAU LO:
                    </h4>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li><i class="fas fa-comment-slash mr-2 text-red-400"></i>Tukang alasan & pemilik mental korban</li>
                        <li><i class="fas fa-bolt mr-2 text-red-400"></i>Cari motivasi instan, bukan eksekusi</li>
                        <li><i class="fas fa-eye mr-2 text-red-400"></i>Join cuma penasaran, gak niat</li>
                        <li><i class="fas fa-user-slash mr-2 text-red-400"></i>Takut di-KICK dan uang hangus</li>
                        <li><i class="fas fa-microphone mr-2 text-red-400"></i>INFLUENCER BULLSH*T yang jago bacot doang</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== LIVE DATA SECTION ===== -->
<section id="live-feed-preview" class="py-16 px-4 bg-gray-900/50">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold mb-2"><i class="fas fa-bolt text-yellow-400 mr-2"></i>LIVE DATA</h2>
            <p class="text-gray-400">Medan Perang Sedang Berlangsung • Bukan rekayasa, ini kenyataan</p>
        </div>
        
        <!-- Live Activity Feed Preview -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden mb-8">
            <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600 flex items-center justify-between">
                <span class="font-semibold"><i class="fas fa-rss mr-2"></i>Live Activity Feed</span>
                <span class="text-xs px-2 py-1 bg-red-600 rounded-full animate-pulse">Real-time</span>
            </div>
            <div class="divide-y divide-gray-700 max-h-80 overflow-y-auto">
                <?php
                $feeds = dbFetchAll("
                    SELECT lf.*, u.full_name, u.whatsapp_number 
                    FROM live_feed lf 
                    LEFT JOIN users u ON lf.user_id = u.id 
                    WHERE lf.is_public = TRUE 
                    ORDER BY lf.timestamp DESC 
                    LIMIT 5
                ");
                
                if (empty($feeds)): ?>
                    <div class="p-4 text-center text-gray-500">Belum ada activity...</div>
                <?php else: 
                    foreach ($feeds as $feed): 
                        $icon = match($feed['action_type']) {
                            'report_submitted' => 'fas fa-clipboard-check text-green-400',
                            'user_kicked' => 'fas fa-user-slash text-red-400',
                            'skip_warning_1', 'skip_warning_2', 'skip_warning_3' => 'fas fa-exclamation-triangle text-orange-400',
                            'subscription_extended' => 'fas fa-crown text-yellow-400',
                            'user_added' => 'fas fa-user-plus text-blue-400',
                            default => 'fas fa-circle text-gray-400'
                        };
                ?>
                        <div class="px-4 py-3 hover:bg-gray-700/30 transition">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <i class="<?= $icon ?> mt-1"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-200"><?= sanitize($feed['action_description']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= formatDateIndo($feed['timestamp'], 'H:i') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
            <div class="px-4 py-3 bg-gray-700/30 border-t border-gray-600 text-center">
                <a href="public_feed.php" class="text-orange-400 hover:underline text-sm font-medium">
                    Lihat Feed Lengkap <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $stats = [
                ['label' => 'Laporan Hari Ini', 'value' => dbFetch("SELECT COUNT(*) as c FROM daily_reports WHERE report_date = CURDATE()")['c'] ?? 0, 'icon' => 'fas fa-file-alt', 'color' => 'blue'],
                ['label' => 'Di-Kick', 'value' => dbFetch("SELECT COUNT(*) as c FROM users WHERE status = 'kicked'")['c'] ?? 0, 'icon' => 'fas fa-user-slash', 'color' => 'red'],
                ['label' => 'Active Warriors', 'value' => dbFetch("SELECT COUNT(*) as c FROM users WHERE status IN ('active','trial') AND is_active = TRUE")['c'] ?? 0, 'icon' => 'fas fa-users', 'color' => 'green'],
                ['label' => 'Top Streak', 'value' => '<i class="fas fa-fire text-orange-400"></i>', 'icon' => 'fas fa-fire', 'color' => 'orange'],
            ];
            foreach ($stats as $stat): ?>
                <div class="bg-gray-800 rounded-lg p-4 text-center border border-gray-700 hover:border-<?= $stat['color'] ?>-500 transition">
                    <i class="<?= $stat['icon'] ?> text-<?= $stat['color'] ?>-400 text-xl mb-2"></i>
                    <p class="text-2xl font-bold"><?= $stat['value'] ?></p>
                    <p class="text-xs text-gray-400"><?= $stat['label'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CARA KERJA SECTION ===== -->
<section id="cara-kerja" class="py-16 px-4">
    <div class="max-w-5xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-4">Sederhana Tapi Mematikan</h2>
        <p class="text-gray-400 mb-12">Cara Main Sistem Ini</p>
        
        <div class="grid md:grid-cols-4 gap-6">
            <?php
            $steps = [
                ['num' => 1, 'title' => 'Isi Form', 'desc' => 'Ceritakan mimpi lo yang diremehkan, tulis komitmen lo!', 'icon' => 'fas fa-edit'],
                ['num' => 2, 'title' => 'Masuk Grup', 'desc' => 'Kenalan singkat, tujuan jelas, siap berperang!', 'icon' => 'fas fa-users'],
                ['num' => 3, 'title' => 'Lapor Harian', 'desc' => 'Setiap malam isi progress di dashboard. Tidak lapor = sanksi!', 'icon' => 'fas fa-clipboard-list'],
                ['num' => 4, 'title' => 'Sanksi Real', 'desc' => '3x bolos → KICK + BLACKLIST. Uang hangus!', 'icon' => 'fas fa-gavel'],
            ];
            foreach ($steps as $step): ?>
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-orange-600 transition group">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-red-600 rounded-full flex items-center justify-center font-bold text-xl mx-auto mb-4 group-hover:scale-110 transition">
                        <?= $step['num'] ?>
                    </div>
                    <div class="flex justify-center mb-3">
                        <i class="<?= $step['icon'] ?> text-orange-400 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2"><?= $step['title'] ?></h3>
                    <p class="text-sm text-gray-400"><?= $step['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p class="mt-8 text-gray-300 max-w-2xl mx-auto">
            Butuh solusi? Gw sebagai admin dan temen-temen warrior lain bakal bantu lo. 
            Bukan cuma salim-salaman, tapi <span class="text-orange-400 font-semibold">solusi nyata</span> untuk masalah lo!
        </p>
    </div>
</section>

<!-- ===== FEATURES PREVIEW SECTION (NEW) ===== -->
<section class="py-16 px-4 bg-gray-900/50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Fitur Dashboard Lo</h2>
            <p class="text-gray-400">Semua yang lo butuhin buat konsisten, dalam satu tempat</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $features = [
                ['icon' => 'fas fa-clipboard-list', 'title' => 'Laporan Harian', 'desc' => 'Catat target, progress, dan bukti kerja. Streak counter buat motivasi.'],
                ['icon' => 'fas fa-bullseye', 'title' => 'Dream Tracker', 'desc' => 'Manajemen impian jangka panjang. Update status: In Progress / Completed / Abandoned.'],
                ['icon' => 'fas fa-trophy', 'title' => 'Milestones', 'desc' => 'Achievement system otomatis. Unlock badge saat capai target tertentu.'],
                ['icon' => 'fas fa-stopwatch', 'title' => 'Pomodoro Timer', 'desc' => 'Timer fokus 25 menit + custom settings. Auto-save preferensi di browser.'],
                ['icon' => 'fas fa-tasks', 'title' => 'Todo List', 'desc' => 'Kelola tugas harian. Add/Toggle/Delete. Data tersimpan di localStorage.'],
                ['icon' => 'fas fa-palette', 'title' => 'Custom Theme', 'desc' => '6 tema visual. Background + aksen + tombol berubah. Tersimpan otomatis.'],
            ];
            foreach ($features as $f): ?>
                <div class="p-5 bg-gray-800 rounded-xl border border-gray-700 hover:border-blue-500 transition">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-900/50 border border-blue-700 flex items-center justify-center">
                            <i class="<?= $f['icon'] ?> text-blue-400"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-1"><?= $f['title'] ?></h4>
                            <p class="text-sm text-gray-400"><?= $f['desc'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== TESTIMONI SECTION ===== -->
<section class="py-16 px-4 bg-gray-900/50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2">Bukan Rekayasa</h2>
            <p class="text-gray-400">Kata Mereka Yang Selamat</p>
            <p class="text-sm text-orange-400 mt-2 italic">Yang di-KICK gak bisa testimoni, kan? wkwk</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $testimonials = [
                ['name' => 'Andi P.', 'role' => 'Freelancer, Pernah di-KICK', 'text' => 'Awalnya gue kesel banget karena di-KICK gegara 3 hari ga lapor. Padahal reason gue valid, kerja lembur! Tapi itu yang bikin gue sadar... Daftar ulang, sekarang 45 hari streak, penghasilan naik 3x lipat!'],
                ['name' => 'Rina S.', 'role' => 'Mahasiswa, 60 Hari Streak', 'text' => 'Gue gabung karena muak sama diri sendiri yang cuma nonton YouTube motivasi tapi ga gerak. 60 hari streak sekarang, hidup gue berubah 180 derajat. Skripsi kelar, kerja dapet!'],
                ['name' => 'Budi K.', 'role' => 'Karyawan, 30 Hari Streak', 'text' => 'Sebelum join, gue orang yang paling jago ngeles. Tiap hari ada alasan. Sekarang? Gue gak berani bolos karena takut uang gue hangus. Best investment ever!'],
                ['name' => 'Dewi M.', 'role' => 'Entrepreneur, 21 Hari Streak', 'text' => 'TIDAK ADA REFUND? Serius awalnya gue mikir ini scam. Tapi ternyata memang itu yang bikin gue serius. Komitmennya beda ketika ada konsekuensi nyata!'],
                ['name' => 'Fajar R.', 'role' => 'Founder Startup, 90 Hari Streak', 'text' => 'Ide gue selalu diketawain sama temen-temen. "Mimpi muluk-muluk", katanya. Di sini gue ketemu orang-orang yang SAMA GILA NYA seperti gue. Sekarang bisnis gue jalan!'],
                ['name' => 'Sari A.', 'role' => 'Content Creator, 45 Hari Streak', 'text' => 'Gue orang yang paling malas yang lo kenal. Makan aja minta diantar. Tapi setelah join sini, gue belajar disiplin paksa. Turun 8kg dalam 2 bulan karena konsisten olahraga!'],
            ];
            
            foreach ($testimonials as $t): ?>
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-green-600 transition">
                    <div class="flex items-center gap-1 mb-3">
                        <?php for($i=0; $i<5; $i++): ?>
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-300 text-sm mb-4 italic">"<?= sanitize($t['text']) ?>"</p>
                    <div>
                        <p class="font-bold"><?= sanitize($t['name']) ?></p>
                        <p class="text-xs text-gray-400"><?= sanitize($t['role']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FAQ SECTION ===== -->
<section class="py-16 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2">Jawaban Brutal</h2>
            <p class="text-gray-400">Pertanyaan Para Pecundang</p>
            <p class="text-sm text-orange-400 mt-2 italic">Read this before lo ngeles lagi...</p>
        </div>
        
        <div class="space-y-4">
            <?php
            $faqs = [
                ['q' => 'Apa yang gue dapat?', 'a' => 'Akses grup Discord eksklusif, dashboard personal untuk tracking progress, sistem akuntabilitas brutal, dan komunitas warrior yang sama-sama berjuang.'],
                ['q' => 'Kok gak ada refund?', 'a' => 'Karena komitmen itu serius. Kalau ada refund, lo bakal mikir "ah nanti aja". Gak ada jalan mundur = lo SERIUS dari awal.'],
                ['q' => 'Gimana kalau gue sibuk?', 'a' => 'Semua orang sibuk. Yang beda: warrior cari cara, pecundang cari alasan. Lapor progress itu cuma 2 menit. Kalau 2 menit aja gak ada, berarti lo gak prioritasin mimpi lo.'],
                ['q' => '3 hari gak lapor langsung KICK?', 'a' => 'IYA. Tanpa peringatan kedua. Sistem ini dirancang buat lo yang BENAR-BENAR MAU BERUBAH. Kalau lo butuh diingatkan berkali-kali, ini bukan tempat lo.'],
                ['q' => 'Trial gratis, terus kenapa harus subscribe?', 'a' => 'Trial 3 hari buat lo buktikan lo serius. Kalau setelah 3 hari lo masih mau lanjut, berarti lo siap invest ke diri sendiri. Simple.'],
                ['q' => 'Apa gara-gara gue sering ngeles lo buat sistem ini?', 'a' => 'Bukan cuma lo. Gw juga dulu gitu. Makanya gw bikin sistem yang MEMAKSA kita semua untuk konsisten. Termasuk gw sendiri.'],
                ['q' => 'Gue takut di-KICK, malu dong...', 'a' => 'Bagus. Rasa malu itu sehat. Itu artinya lo peduli. Tapi ingat: lebih malu mana? Di-KICK dari grup, atau nyesel seumur hidup karena gak pernah mulai?'],
                ['q' => 'Lo siapa sih berani-beraninya paksa orang?', 'a' => 'Gw? Gw cuma orang yang capek liat potensi terkubur. Gw gak maksa, gw cuma nyediain sistem. Lo yang pilih: ikut atau nggak.'],
                ['q' => 'Kalau gue gagal gimana?', 'a' => 'Gagal itu bagian dari proses. Yang gak boleh gagal: BERHENTI. Selama lo masih lapor, masih usaha, lo masih warrior. Gagal hari ini? Besok bangkit lagi.'],
                ['q' => 'Ini kan mahal ya...', 'a' => 'Mahal mana: Rp 15.000/bulan, atau mimpi lo yang terkubur selamanya? Investasi termahal itu waktu. Gw cuma bantu lo hemat waktu dengan paksa lo fokus.'],
            ];
            
            foreach ($faqs as $i => $faq): ?>
                <details class="group bg-gray-800 rounded-lg border border-gray-700">
                    <summary class="flex items-center justify-between p-4 cursor-pointer list-none hover:bg-gray-700/30 transition">
                        <span class="font-medium text-gray-200"><?= ($i+1) . '. ' . sanitize($faq['q']) ?></span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition text-gray-400"></i>
                    </summary>
                    <div class="px-4 pb-4 text-gray-300 text-sm border-t border-gray-700 pt-3">
                        <?= sanitize($faq['a']) ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== PRICING SECTION ===== -->
<section id="pricing" class="py-16 px-4 bg-gradient-to-b from-gray-900 to-warrior-dark">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2">Pilihan Ada Di Tangan Lo</h2>
            <p class="text-gray-400">Lo Mau Berubah Atau Tetap Pecundang?</p>
            <p class="text-sm text-orange-400 mt-2">Slot terbatas, gue gak terima semua orang. Serius aja!</p>
        </div>
        
        <!-- Trial Card -->
        <div class="max-w-md mx-auto mb-8 bg-gradient-to-br from-green-900/30 to-emerald-900/30 border-2 border-green-600 rounded-2xl p-6 text-center">
            <span class="inline-block px-3 py-1 bg-green-600 rounded-full text-xs font-bold mb-3">REKOMENDASI PEMULA</span>
            <h3 class="text-2xl font-bold mb-2">TRIAL <?= (int)$settings['trial_days'] ?> HARI</h3>
            <p class="text-gray-400 mb-4">Coba dulu, buktikan lo serius. GRATIS!</p>
            
            <ul class="text-left space-y-2 mb-6 text-sm text-gray-300">
                <li><i class="fas fa-check text-green-400 mr-2"></i> Akses grup Discord eksklusif</li>
                <li><i class="fas fa-check text-green-400 mr-2"></i> Dashboard personal + semua fitur</li>
                <li><i class="fas fa-check text-green-400 mr-2"></i> Sistem KICK berlaku</li>
                <li><i class="fas fa-check text-green-400 mr-2"></i> Support dari warrior lain</li>
            </ul>
            
            <div class="text-3xl font-black mb-4">Rp 0</div>
            
            <a href="login.php" class="inline-block w-full py-3 bg-green-600 hover:bg-green-700 rounded-lg font-bold transition">
                DAFTAR TRIAL GRATIS
            </a>
            <p class="text-xs text-gray-500 mt-2">Tanpa kartu kredit, tanpa ribet</p>
        </div>
        
        <!-- Pricing Note -->
        <p class="text-center text-gray-400 mb-8">
            Sudah yakin? Langsung subscribe dan dapat <span class="text-orange-400 font-semibold">diskon 5%!</span>
        </p>
        
        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <?php
            $packages = [
                [
                    'key' => 'monthly',
                    'name' => 'Monthly',
                    'original' => $pricing['monthly']['price'],
                    'discount' => $pricing['monthly']['price'] * 0.95,
                    'days' => 30,
                    'quota' => $pricing['monthly']['skip_quota'],
                    'popular' => false
                ],
                [
                    'key' => 'quarterly', 
                    'name' => 'Quarterly',
                    'original' => $pricing['quarterly']['price'],
                    'discount' => $pricing['quarterly']['price'] * 0.95,
                    'days' => 90,
                    'quota' => $pricing['quarterly']['skip_quota'],
                    'popular' => true
                ],
                [
                    'key' => 'yearly',
                    'name' => 'Yearly', 
                    'original' => $pricing['yearly']['price'],
                    'discount' => $pricing['yearly']['price'] * 0.95,
                    'days' => 365,
                    'quota' => $pricing['yearly']['skip_quota'],
                    'popular' => false
                ],
            ];
            
            foreach ($packages as $pkg): 
                $admin_wa = $settings['admin_whatsapp'] ?? '6281234567890';
                $wa_msg = urlencode("Halo admin, saya mau subscribe paket {$pkg['name']} Warrior Produktif");
            ?>
                <div class="relative bg-gray-800 rounded-2xl p-6 border <?= $pkg['popular'] ? 'border-orange-600 ring-2 ring-orange-600/50' : 'border-gray-700' ?> hover:border-orange-500 transition">
                    <?php if ($pkg['popular']): ?>
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-orange-600 rounded-full text-xs font-bold">MOST POPULAR</span>
                    <?php endif; ?>
                    
                    <h3 class="text-xl font-bold mb-2"><?= $pkg['name'] ?></h3>
                    
                    <div class="mb-4">
                        <span class="text-gray-500 line-through text-sm">Rp <?= number_format($pkg['original'], 0, ',', '.') ?></span>
                        <div class="text-3xl font-black">Rp <?= number_format($pkg['discount'], 0, ',', '.') ?></div>
                    </div>
                    
                    <ul class="space-y-2 mb-6 text-sm text-gray-300">
                        <li><i class="fas fa-calendar text-orange-400 mr-2"></i> <?= $pkg['days'] ?> hari akses penuh</li>
                        <li><i class="fas fa-exclamation-triangle text-orange-400 mr-2"></i> Kuota bolos: <?= $pkg['quota'] ?>x</li>
                        <li><i class="fas fa-shield-alt text-orange-400 mr-2"></i> 3x bolos = AUTO KICK</li>
                        <li><i class="fas fa-gift text-orange-400 mr-2"></i> Diskon 5% sudah termasuk</li>
                    </ul>
                    
                    <a href="<?= waLink($admin_wa, $wa_msg) ?>" 
                       target="_blank"
                       class="inline-block w-full py-3 <?= $pkg['popular'] ? 'bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700' : 'bg-gray-700 hover:bg-gray-600' ?> rounded-lg font-bold transition text-center">
                        Pilih Paket
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Final CTA -->
        <div class="text-center mt-12 max-w-3xl mx-auto">
            <p class="text-gray-300 mb-4">
                <i class="fas fa-heart text-red-500 mr-1"></i> 
                Ingat, setiap hari lo nunda, mimpi lo makin jauh. 
                Orang-orang yang lo sayangin (ibu, bapak, adik, pacar, keluarga lo) menunggu lo jadi 
                <span class="text-orange-400 font-bold">VERSI TERBAIK</span> diri lo.
            </p>
            <p class="text-gray-400 mb-6">
                Jangan sampai lo menyesal karena gak bergerak dari sekarang. Pilihannya di tangan lo.
            </p>
            <p class="text-lg font-bold text-gradient">
                Warrior Produktif - No Refund. No Mercy. No Bullsh*t.
            </p>
        </div>
    </div>
</section>

<!-- ===== SAVINGS CALCULATOR JS ===== -->
<script>
function calculateSavings() {
    // Get input values
    const books = parseInt(document.getElementById('calc-books').value) || 0;
    const courses = parseInt(document.getElementById('calc-courses').value) || 0;
    const apps = parseInt(document.getElementById('calc-apps').value) || 0;
    
    // Prices
    const BOOK_PRICE = 75000;
    const COURSE_PRICE = 250000;
    const APP_PRICE = 50000;
    const WARRIOR_PRICE = <?= (int)($pricing['monthly']['price'] * 0.95) ?>;
    
    // Calculate
    const wasted = (books * BOOK_PRICE) + (courses * COURSE_PRICE) + (apps * APP_PRICE);
    const savings = wasted - WARRIOR_PRICE;
    const breakEven = wasted > 0 ? Math.ceil(WARRIOR_PRICE / (wasted / 12)) : 1;
    
    // Update display with animation
    animateValue('result-wasted', wasted, 'Rp ');
    animateValue('result-warrior', WARRIOR_PRICE, 'Rp ');
    animateValue('result-savings', Math.max(0, savings), 'Rp ');
    
    document.getElementById('result-breakeven').textContent = 
        savings > 0 ? `${breakEven} bulan` : 'Segera';
    
    // Show celebration if savings is significant
    if (savings > 500000) {
        showToast('Wah, lo bisa hemat signifikan! 🎉', 'success');
    }
}

function animateValue(elementId, end, prefix = '') {
    const element = document.getElementById(elementId);
    const duration = 500;
    const start = 0;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Ease out quad
        const ease = 1 - (1 - progress) * (1 - progress);
        const current = Math.floor(start + (end - start) * ease);
        
        element.textContent = prefix + new Intl.NumberFormat('id-ID').format(current);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Initialize calculator on load
document.addEventListener('DOMContentLoaded', () => {
    calculateSavings();
});

// Toast notification (reuse from dashboard)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-gray-700'
    };
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg ${colors[type]} text-white z-50 animate-slide-up`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<!-- CSS Animation for Toast -->
<style>
@keyframes slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>

<?php require_once 'includes/footer.php'; ?>