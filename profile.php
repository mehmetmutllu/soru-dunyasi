<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Güvenlik: Giriş yapmamışsa login'e gönder
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// 1. KULLANICI BİLGİLERİNİ ÇEK
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// 2. TEMEL İSTATİSTİKLERİ HESAPLA
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tests,
        SUM(score) as total_score,
        SUM(total_questions) as total_questions_seen,
        AVG(percentage) as avg_percentage
    FROM quiz_results 
    WHERE user_id = ?
");
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Değerleri al (Boşsa 0 ata)
$totalTests = $stats['total_tests'] ?? 0;
$totalScore = $stats['total_score'] ?? 0;
$totalQuestions = $stats['total_questions_seen'] ?? 0;
$avgSuccess = round($stats['avg_percentage'] ?? 0);

// 3. SIRALAMAYI BUL (Liderlik Tablosundaki Yeri)
// DÜZELTME: 'rank' kelimesi MySQL 8.0'da rezerve olduğu için 'user_rank' olarak değiştirdik.
$rankStmt = $pdo->prepare("
    SELECT COUNT(*) + 1 as user_rank FROM (
        SELECT user_id, SUM(score) as user_total_score 
        FROM quiz_results 
        GROUP BY user_id 
        HAVING user_total_score > ?
    ) as better_players
");
$rankStmt->execute([$totalScore]);
$myRank = $rankStmt->fetchColumn();

// 4. EN BAŞARILI DERSİ BUL
$favCatStmt = $pdo->prepare("
    SELECT c.name, AVG(r.percentage) as cat_avg
    FROM quiz_results r
    JOIN quizzes q ON r.quiz_id = q.id
    JOIN categories c ON q.category_id = c.id
    WHERE r.user_id = ?
    GROUP BY c.id
    ORDER BY cat_avg DESC
    LIMIT 1
");
$favCatStmt->execute([$userId]);
$favCat = $favCatStmt->fetch(PDO::FETCH_ASSOC);
$bestSubject = $favCat ? $favCat['name'] : 'Henüz Yok';

// 5. GEÇMİŞ TESTLERİ LİSTELE
$historyStmt = $pdo->prepare("
    SELECT r.*, q.title as quiz_title, c.name as cat_name, c.color as cat_color
    FROM quiz_results r
    JOIN quizzes q ON r.quiz_id = q.id
    JOIN categories c ON q.category_id = c.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$historyStmt->execute([$userId]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Üst Profil Kartı -->
    <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 flex flex-col md:flex-row items-center gap-8 mb-10 relative overflow-hidden">
        <!-- Arka Plan Efekti -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 opacity-5 rounded-full blur-3xl -mr-16 -mt-16"></div>
        
        <!-- Avatar -->
        <div class="relative">
            <div class="w-28 h-28 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-5xl font-bold shadow-2xl border-4 border-white">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <!-- Sıralama Rozeti -->
            <div class="absolute -bottom-2 -right-2 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-bold border-2 border-white shadow-sm flex items-center gap-1">
                <i class="fa-solid fa-trophy"></i> #<?= $myRank ?>
            </div>
        </div>
        
        <div class="text-center md:text-left z-10 flex-grow">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-1"><?= htmlspecialchars($user['username']) ?></h1>
            <p class="text-gray-500 font-medium"><?= htmlspecialchars($user['email']) ?></p>
            <div class="flex items-center justify-center md:justify-start gap-4 mt-4">
                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-sm font-bold border border-indigo-100">
                    <i class="fa-solid fa-calendar-day mr-1"></i> <?= date('d.m.Y', strtotime($user['created_at'])) ?> Tarihinden Beri Üye
                </span>
            </div>
        </div>
    </div>

    <!-- İstatistik Izgarası -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        
        <!-- 1. Toplam Puan -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Toplam Puan</div>
                <div class="text-2xl font-black text-gray-900"><?= number_format($totalScore) ?></div>
            </div>
        </div>

        <!-- 2. Çözülen Soru -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Çözülen Soru</div>
                <div class="text-2xl font-black text-gray-900"><?= $totalQuestions ?></div>
            </div>
        </div>

        <!-- 3. Genel Başarı -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-14 h-14 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="flex-grow">
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Ortalama Başarı</div>
                <div class="text-2xl font-black text-gray-900">%<?= $avgSuccess ?></div>
                <!-- Mini Progress Bar -->
                <div class="w-full bg-gray-100 h-1.5 rounded-full mt-2">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $avgSuccess ?>%"></div>
                </div>
            </div>
        </div>

        <!-- 4. En İyi Ders -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-14 h-14 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">En İyi Ders</div>
                <div class="text-xl font-bold text-gray-900 truncate max-w-[120px]" title="<?= $bestSubject ?>"><?= $bestSubject ?></div>
            </div>
        </div>
    </div>

    <!-- Geçmiş Testler Tablosu -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Test Geçmişi</h2>
        <a href="index.php" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">Yeni Test Çöz <i class="fa-solid fa-arrow-right ml-1"></i></a>
    </div>
    
    <?php if (count($history) > 0): ?>
    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">Test Adı</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-center">Puan</th>
                        <th class="px-6 py-4 text-center">Başarı</th>
                        <th class="px-6 py-4 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($history as $res): ?>
                    <tr class="hover:bg-indigo-50/30 transition group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800"><?= $res['quiz_title'] ?></div>
                            <div class="text-xs text-gray-400"><?= date('d.m.Y H:i', strtotime($res['created_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold px-2 py-1 rounded text-white <?= $res['cat_color'] ?>">
                                <?= $res['cat_name'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono text-gray-600 font-bold">
                            <?= $res['score'] ?> / <?= $res['total_questions'] ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php 
                                $color = $res['percentage'] >= 80 ? 'text-green-600 bg-green-50 border-green-200' : ($res['percentage'] >= 50 ? 'text-yellow-600 bg-yellow-50 border-yellow-200' : 'text-red-600 bg-red-50 border-red-200');
                            ?>
                            <span class="font-bold px-3 py-1 rounded-full text-xs border <?= $color ?>">%<?= $res['percentage'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="review.php?result_id=<?= $res['id'] ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition shadow-sm" title="Detaylı İncele">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
        <div class="text-center py-20 bg-white rounded-3xl border-2 border-gray-100 border-dashed">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl">
                <i class="fa-solid fa-clipboard-question"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-700">Henüz hiç test çözmediniz.</h3>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">İstatistiklerinizi görmek için ilk testinizi çözmeye başlayın.</p>
            <a href="index.php" class="mt-6 inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Hemen Başla</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>