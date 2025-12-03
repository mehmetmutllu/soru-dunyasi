<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Renk Paleti (Zorluk için - Daha canlı renkler)
$difficultyColors = [
    'kolay' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
    'orta'   => 'text-amber-700 bg-amber-50 border-amber-200',
    'zor'    => 'text-rose-700 bg-rose-50 border-rose-200'
];

/* -------------------------------------------------------------------------
   MOD 1: KULLANICI GİRİŞ YAPMIŞSA (DASHBOARD)
   ------------------------------------------------------------------------- 
*/
if ($isLoggedIn): 
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // ... (STREAK ve USER İşlemleri - Aynı)
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user['last_seen'] != $today) {
        if ($user['last_seen'] == $yesterday) {
            $newStreak = $user['streak'] + 1;
        } else {
            $newStreak = 1;
        }
        $updateStmt = $pdo->prepare("UPDATE users SET streak = ?, last_seen = ? WHERE id = ?");
        $updateStmt->execute([$newStreak, $today, $userId]);
        $user['streak'] = $newStreak;
    }

    // İSTATİSTİKLER
    $statsQuery = $pdo->prepare("SELECT COUNT(*) as total_solved, SUM(score) as total_score, SUM(total_questions) as total_q FROM quiz_results WHERE user_id = ?");
    $statsQuery->execute([$userId]);
    $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
    
    $totalSolved = $stats['total_solved'] ?? 0;
    $successRate = ($stats['total_q'] > 0) ? round(($stats['total_score'] / $stats['total_q']) * 100) : 0;

    // SIRALAMA
    $rankStmt = $pdo->prepare("SELECT COUNT(*) + 1 as user_rank FROM (SELECT user_id, SUM(score) as user_total_score FROM quiz_results GROUP BY user_id HAVING user_total_score > ?) as better_players");
    $rankStmt->execute([$stats['total_score'] ?? 0]);
    $myRank = $rankStmt->fetchColumn();

    // ÖNERİLEN TESTLER (İkon bilgisini de çekiyoruz artık)
    $recQuery = $pdo->prepare("
        SELECT q.*, c.name as cat_name, c.color as cat_color, c.icon as cat_icon 
        FROM quizzes q 
        JOIN categories c ON q.category_id = c.id 
        WHERE q.id NOT IN (SELECT quiz_id FROM quiz_results WHERE user_id = ?)
        ORDER BY RAND() LIMIT 3
    ");
    $recQuery->execute([$userId]);
    $recommendations = $recQuery->fetchAll(PDO::FETCH_ASSOC);

    if (count($recommendations) == 0) {
        $recQuery = $pdo->query("SELECT q.*, c.name as cat_name, c.color as cat_color, c.icon as cat_icon FROM quizzes q JOIN categories c ON q.category_id = c.id ORDER BY RAND() LIMIT 3");
        $recommendations = $recQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    // SON AKTİVİTELER
    $historyQuery = $pdo->prepare("
        SELECT r.*, q.title, q.id as q_id 
        FROM quiz_results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC LIMIT 3
    ");
    $historyQuery->execute([$userId]);
    $history = $historyQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-in">
    
    <!-- ÜST BİLGİ KARTI -->
    <div class="bg-slate-900 rounded-[2rem] p-8 text-white shadow-2xl mb-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600 opacity-20 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-purple-600 opacity-10 rounded-full blur-3xl -ml-20 -mb-20"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row items-center gap-8">
            <div class="flex items-center gap-6 w-full lg:w-auto">
                <div class="relative">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-4xl font-bold shadow-lg border-4 border-slate-800">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-bold border-4 border-slate-900 flex items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-trophy"></i> #<?= $myRank ?>
                    </div>
                </div>
                <div>
                    <div class="text-indigo-300 font-bold text-sm uppercase tracking-wide mb-1">Öğrenci Paneli</div>
                    <h1 class="text-3xl font-extrabold leading-tight">Selam, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                    <p class="text-slate-400 text-sm mt-1">Gelişimini buradan takip et.</p>
                </div>
            </div>

            <div class="flex-grow flex justify-center w-full lg:w-auto">
                <div class="flex gap-2 md:gap-8 bg-white/5 p-4 rounded-2xl backdrop-blur-sm border border-white/10 w-full justify-around lg:w-auto">
                    <div class="text-center px-2 md:px-4">
                        <div class="text-2xl md:text-3xl font-black text-green-400">%<?= $successRate ?></div>
                        <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold tracking-wider mt-1">Başarı</div>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div class="text-center px-2 md:px-4">
                        <div class="text-2xl md:text-3xl font-black text-white"><?= $totalSolved ?></div>
                        <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold tracking-wider mt-1">Test</div>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div class="text-center px-2 md:px-4">
                        <div class="text-2xl md:text-3xl font-black text-orange-500 flex items-center justify-center gap-1">
                            <?= $user['streak'] ?> <i class="fa-solid fa-fire text-lg animate-pulse"></i>
                        </div>
                        <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold tracking-wider mt-1">Gün Seri</div>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 w-full lg:w-auto">
                <a href="library.php" class="block w-full text-center bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-4 rounded-xl font-bold transition shadow-lg shadow-indigo-900/50 group">
                    Tüm Testler
                    <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- SOL KOLON -->
        <div class="lg:col-span-2 space-y-10">
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </span>
                    Sana Özel Öneriler
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($recommendations as $quiz): 
                        $badgeClass = $difficultyColors[$quiz['difficulty']] ?? 'text-gray-600 bg-gray-100';
                        $iconClass = str_replace('bg-', 'text-', $quiz['cat_color']); // İkon rengi için
                    ?>
                    
                    <!-- YENİ GAMIFIED KART TASARIMI -->
                    <div class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 relative overflow-hidden border border-gray-100 flex flex-col">
                        
                        <!-- Renkli Üst Header -->
                        <div class="h-20 <?= $quiz['cat_color'] ?> relative overflow-hidden">
                            <!-- Dekoratif Daireler -->
                            <div class="absolute top-0 right-0 w-24 h-24 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-black opacity-5 rounded-full -ml-8 -mb-8"></div>
                        </div>

                        <!-- Yüzen İkon -->
                        <div class="absolute top-8 left-6 w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center text-3xl <?= $iconClass ?> border-4 border-white">
                            <i class="fa-solid <?= $quiz['cat_icon'] ?? 'fa-book' ?>"></i>
                        </div>

                        <!-- İçerik -->
                        <div class="pt-10 px-6 pb-6 flex-grow flex flex-col">
                            <div class="flex justify-end mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border <?= $badgeClass ?>">
                                    <?= ucfirst($quiz['difficulty']) ?>
                                </span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-800 mb-2 leading-tight group-hover:text-indigo-600 transition">
                                <?= $quiz['title'] ?>
                            </h3>
                            <p class="text-sm text-gray-500 mb-6 line-clamp-2 h-10">
                                <?= $quiz['description'] ?>
                            </p>
                            
                            <a href="quiz.php?id=<?= $quiz['id'] ?>" class="mt-auto block w-full text-center bg-gray-50 hover:bg-gray-800 hover:text-white text-gray-700 font-bold py-3 rounded-xl transition border border-gray-200 hover:border-gray-800">
                                Teste Başla
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- SAĞ KOLON: Son Aktiviteler -->
        <div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-gray-400"></i> Son Aktiviteler
                    </h3>
                    <a href="profile.php" class="text-xs font-bold text-gray-400 hover:text-indigo-600">Geçmiş</a>
                </div>
                
                <?php if(count($history) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach($history as $item): ?>
                        <div class="flex items-center justify-between border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                            <div>
                                <div class="font-bold text-gray-800 text-sm line-clamp-1"><?= $item['title'] ?></div>
                                <div class="text-xs text-gray-400 mt-0.5"><?= date('d.m.Y', strtotime($item['created_at'])) ?></div>
                            </div>
                            <div class="text-right">
                                <span class="block text-sm font-black <?= $item['percentage'] >= 50 ? 'text-green-600' : 'text-red-500' ?>">%<?= $item['percentage'] ?></span>
                                <a href="quiz.php?id=<?= $item['q_id'] ?>" class="text-[10px] font-bold text-indigo-500 hover:underline">Tekrar</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400">Henüz aktivite yok.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <a href="leaderboard.php" class="block w-full py-3 bg-yellow-50 text-yellow-700 font-bold rounded-xl border border-yellow-100 text-center hover:bg-yellow-100 transition text-sm">
                        <i class="fa-solid fa-trophy mr-2"></i> Liderlik Tablosu
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php 
/* -------------------------------------------------------------------------
   MOD 2: MİSAFİR KULLANICI (LANDING PAGE - GÜNCELLENDİ)
   ------------------------------------------------------------------------- 
*/
else: 
    // Misafirler için de testleri çekiyoruz (İkon eklendi)
    $guestQuizzes = $pdo->query("SELECT q.*, c.name as cat_name, c.color as cat_color, c.icon as cat_icon FROM quizzes q JOIN categories c ON q.category_id = c.id ORDER BY q.id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="relative pt-20 pb-32 overflow-hidden bg-white">
    <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Hero -->
        <div class="text-center max-w-3xl mx-auto mb-20 fade-in">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full text-sm font-bold mb-8 border border-indigo-100 shadow-sm animate-pulse">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> YKS & LGS Hazırlık
            </div>
            <h1 class="text-5xl md:text-7xl font-black text-slate-900 mb-6 leading-tight tracking-tight">
                Geleceğini <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Şansa Bırakma.</span>
            </h1>
            <p class="text-xl text-slate-500 mb-10 leading-relaxed font-medium">
                Binlerce soru, akıllı analizler ve rekabetçi liderlik sistemiyle sınavlara en iyi şekilde hazırlan.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:shadow-2xl hover:-translate-y-1 transition-all">
                    Ücretsiz Başla
                </a>
                <a href="#demo-tests" class="bg-white text-slate-700 px-10 py-4 rounded-2xl font-bold text-lg shadow-lg border border-gray-100 hover:bg-gray-50 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i class="fa-regular fa-eye"></i> Örnekler
                </a>
            </div>
        </div>

        <!-- VİTRİN: MİSAFİRLER İÇİN TESTLER -->
        <div id="demo-tests" class="pt-12 border-t border-gray-100">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-slate-800">Popüler İçerikler</h2>
                <p class="text-slate-500 mt-2">Giriş yapmadan önce göz atabilirsin.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($guestQuizzes as $quiz): 
                    $badgeClass = $difficultyColors[$quiz['difficulty']] ?? 'text-gray-600 bg-gray-100';
                    $iconClass = str_replace('bg-', 'text-', $quiz['cat_color']);
                ?>
                <!-- MİSAFİR KART TASARIMI (Yenilenmiş) -->
                <div class="group bg-white rounded-2xl shadow-lg shadow-gray-200/50 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 relative overflow-hidden border border-gray-100 flex flex-col">
                    
                    <!-- Renkli Header -->
                    <div class="h-24 <?= $quiz['cat_color'] ?> relative overflow-hidden">
                        <div class="absolute inset-0 bg-black opacity-5 pattern-dots"></div>
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white opacity-20 rounded-full"></div>
                    </div>
                    
                    <!-- Yüzen İkon -->
                    <div class="absolute top-12 left-6 w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center text-3xl <?= $iconClass ?> border-4 border-white">
                        <i class="fa-solid <?= $quiz['cat_icon'] ?? 'fa-book' ?>"></i>
                    </div>

                    <div class="pt-12 px-6 pb-8 flex-grow flex flex-col">
                        <div class="flex justify-end mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border <?= $badgeClass ?>">
                                <?= ucfirst($quiz['difficulty']) ?>
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-indigo-600 transition">
                            <?= $quiz['title'] ?>
                        </h3>
                        <p class="text-slate-500 text-sm mb-6 line-clamp-2 h-10">
                            <?= $quiz['description'] ?>
                        </p>
                        
                        <a href="quiz.php?id=<?= $quiz['id'] ?>" class="mt-auto block w-full text-center bg-gray-50 hover:bg-slate-800 hover:text-white text-slate-700 font-bold py-3 rounded-xl transition border border-gray-200 hover:border-slate-800">
                            Hemen Başla
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-12 text-center">
                <a href="library.php" class="inline-block border-b-2 border-slate-300 pb-1 text-slate-500 font-bold hover:text-indigo-600 hover:border-indigo-600 transition">Daha Fazlasını Gör</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>