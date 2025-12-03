<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Güvenlik: Giriş yapmamışsa at
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$resultId = isset($_GET['result_id']) ? intval($_GET['result_id']) : 0;
$userId = $_SESSION['user_id'];

// 1. Sonucun bu kullanıcıya ait olduğunu doğrula ve detayları çek
$stmt = $pdo->prepare("
    SELECT r.*, q.title as quiz_title, c.name as cat_name 
    FROM quiz_results r 
    JOIN quizzes q ON r.quiz_id = q.id 
    JOIN categories c ON q.category_id = c.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$resultId, $userId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "<div class='container mx-auto mt-10 p-4 bg-red-100 text-red-700 text-center rounded'>Sonuç bulunamadı veya görüntüleme yetkiniz yok.</div>";
    require_once 'includes/footer.php';
    exit;
}

// 2. Soruları ve Kullanıcının Cevaplarını Çek
$qStmt = $pdo->prepare("
    SELECT q.*, a.user_choice, a.is_correct as user_is_correct 
    FROM questions q 
    JOIN user_answers a ON q.id = a.question_id 
    WHERE a.result_id = ?
    ORDER BY q.id ASC
");
$qStmt->execute([$resultId]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    
    <!-- Başlık Alanı -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded uppercase"><?= $result['cat_name'] ?></span>
                <span class="text-xs text-gray-400"><?= date('d.m.Y H:i', strtotime($result['created_at'])) ?></span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900"><?= $result['quiz_title'] ?></h1>
        </div>
        <div class="text-right">
            <div class="text-4xl font-black <?= $result['percentage'] >= 50 ? 'text-green-600' : 'text-red-600' ?>">%<?= $result['percentage'] ?></div>
            <div class="text-sm font-bold text-gray-500">Başarı Oranı</div>
        </div>
    </div>

    <!-- Soru Listesi -->
    <div class="space-y-6">
        <?php foreach ($questions as $index => $q): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 relative overflow-hidden">
                
                <!-- Durum Çubuğu (Sol Kenar) -->
                <div class="absolute left-0 top-0 bottom-0 w-2 <?= $q['user_is_correct'] ? 'bg-green-500' : 'bg-red-500' ?>"></div>

                <div class="flex items-start gap-4 mb-6">
                    <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold flex items-center justify-center text-sm">
                        <?= $index + 1 ?>
                    </span>
                    <h3 class="text-lg font-bold text-gray-800 leading-relaxed pt-1">
                        <?= $q['question_text'] ?>
                    </h3>
                </div>

                <!-- Şıklar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-12">
                    <?php 
                    $options = ['a', 'b', 'c', 'd', 'e'];
                    foreach ($options as $opt): 
                        if (empty($q["option_$opt"])) continue; // Boş şıkkı atla

                        // Şıkkın Durumunu Belirle
                        $class = "border-gray-200 bg-white text-gray-600"; // Varsayılan
                        $icon = "";

                        // Bu şık DOĞRU CEVAP ise -> Yeşil yap
                        if ($opt == $q['correct_answer']) {
                            $class = "border-green-500 bg-green-50 text-green-800 font-bold ring-1 ring-green-500";
                            $icon = '<i class="fa-solid fa-check text-green-600 ml-auto"></i>';
                        }
                        
                        // Bu şık KULLANICININ YANLIŞ CEVABI ise -> Kırmızı yap
                        if ($opt == $q['user_choice'] && !$q['user_is_correct']) {
                            $class = "border-red-500 bg-red-50 text-red-800 font-bold";
                            $icon = '<i class="fa-solid fa-xmark text-red-600 ml-auto"></i>';
                        }
                    ?>
                        <div class="flex items-center p-3 rounded-xl border-2 <?= $class ?> transition">
                            <span class="w-6 h-6 rounded-full border border-current flex items-center justify-center text-xs font-bold mr-3 opacity-70 uppercase">
                                <?= $opt ?>
                            </span>
                            <span><?= $q["option_$opt"] ?></span>
                            <?= $icon ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sonuç Mesajı -->
                <div class="mt-4 pl-12 text-sm font-bold">
                    <?php if ($q['user_is_correct']): ?>
                        <span class="text-green-600 flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Doğru Cevapladınız!</span>
                    <?php else: ?>
                        <span class="text-red-500 flex items-center gap-2"><i class="fa-solid fa-circle-xmark"></i> Yanlış Cevap. Doğru şık: <span class="uppercase"><?= $q['correct_answer'] ?></span></span>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-10 text-center">
        <a href="profile.php" class="inline-block bg-gray-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-gray-700 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Profile Dön
        </a>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>