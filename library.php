<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// URL Parametrelerini Al
$examId = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : null;
$lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : null;

// Geri Dönüş Linki Mantığı
$backLink = 'library.php';
$pageTitle = 'Eğitim Kütüphanesi';
$pageDesc = 'Hedefini seç ve öğrenmeye başla.';

// 1. SINAVLARI ÇEK (Her zaman lazım olabilir)
$exams = $pdo->query("SELECT * FROM exams")->fetchAll(PDO::FETCH_ASSOC);

// DURUMA GÖRE İÇERİK HAZIRLA
if ($lessonId) {
    // --- KONULARI LİSTELE (Ders Seçilmiş) ---
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lessonId]);
    $currentLesson = $stmt->fetch();
    
    $pageTitle = $currentLesson['name'];
    $pageDesc = 'Konu seçimi yap ve çalışmaya başla.';
    $backLink = "library.php?exam_id=" . $currentLesson['exam_id'];

    $stmt = $pdo->prepare("
        SELECT t.*, 
               (SELECT COUNT(*) FROM articles WHERE topic_id = t.id) as article_count,
               (SELECT COUNT(*) FROM quizzes WHERE topic_id = t.id) as quiz_count
        FROM topics t 
        WHERE lesson_id = ? 
        ORDER BY order_no ASC
    ");
    $stmt->execute([$lessonId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $viewMode = 'topics';

} elseif ($examId) {
    // --- DERSLERİ LİSTELE (Sınav Seçilmiş) ---
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    $currentExam = $stmt->fetch();

    $pageTitle = $currentExam['name'];
    $pageDesc = $currentExam['description'];
    
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE exam_id = ?");
    $stmt->execute([$examId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $viewMode = 'lessons';

} else {
    // --- SINAVLARI LİSTELE (Ana Ekran) ---
    $items = $exams;
    $viewMode = 'exams';
}
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Başlık ve Geri Butonu -->
        <div class="mb-8 flex items-center gap-4">
            <?php if($viewMode !== 'exams'): ?>
                <a href="<?= $backLink ?>" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            <?php endif; ?>
            
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900"><?= $pageTitle ?></h1>
                <p class="text-slate-500"><?= $pageDesc ?></p>
            </div>
        </div>

        <!-- İÇERİK ALANI -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- MOD: SINAVLAR -->
            <?php if ($viewMode == 'exams'): ?>
                <?php foreach($items as $item): ?>
                <a href="library.php?exam_id=<?= $item['id'] ?>" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col h-64">
                    <div class="h-32 <?= $item['color'] ?> relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <h2 class="text-3xl font-black text-white relative z-10 text-center px-4 drop-shadow-md">
                            <?= $item['name'] ?>
                        </h2>
                    </div>
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <p class="text-gray-500 text-sm"><?= $item['description'] ?></p>
                        <span class="text-indigo-600 font-bold text-sm flex items-center gap-2 group-hover:gap-3 transition-all">
                            Dersleri Gör <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>

            <!-- MOD: DERSLER -->
            <?php elseif ($viewMode == 'lessons'): ?>
                <?php foreach($items as $item): ?>
                <a href="library.php?lesson_id=<?= $item['id'] ?>" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-indigo-300 hover:shadow-md transition flex items-center gap-4 group">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid <?= $item['icon'] ?>"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800"><?= $item['name'] ?></h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">Konuları İncele</p>
                    </div>
                    <div class="ml-auto text-gray-300 group-hover:text-indigo-600 transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </a>
                <?php endforeach; ?>

            <!-- MOD: KONULAR (TOPICS) -->
            <?php elseif ($viewMode == 'topics'): ?>
                <?php foreach($items as $item): ?>
                <div class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    
                    <div class="mb-4">
                        <span class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded mb-2 inline-block">
                            Ünite <?= $item['order_no'] ?>
                        </span>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight"><?= $item['name'] ?></h3>
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                        <?php if($item['article_count'] > 0): ?>
                            <span class="flex items-center gap-1 text-green-600 font-bold bg-green-50 px-2 py-1 rounded-lg">
                                <i class="fa-solid fa-book-open"></i> Konu Anlatımı
                            </span>
                        <?php endif; ?>
                        
                        <?php if($item['quiz_count'] > 0): ?>
                            <span class="flex items-center gap-1 text-orange-600 font-bold bg-orange-50 px-2 py-1 rounded-lg">
                                <i class="fa-solid fa-clipboard-check"></i> <?= $item['quiz_count'] ?> Test
                            </span>
                        <?php endif; ?>
                    </div>

                    <a href="topic.php?id=<?= $item['id'] ?>" class="block w-full text-center bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-indigo-600 transition">
                        Çalışmaya Başla
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>