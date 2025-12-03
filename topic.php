<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: library.php");
    exit;
}

$topicId = intval($_GET['id']);
$userId = $_SESSION['user_id'] ?? 0;

// Konu Bilgisi
$stmt = $pdo->prepare("SELECT t.*, l.name as lesson_name, l.id as lesson_id FROM topics t JOIN lessons l ON t.lesson_id = l.id WHERE t.id = ?");
$stmt->execute([$topicId]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) die("Konu bulunamadı.");

// Konu Anlatımı (Article)
$artStmt = $pdo->prepare("SELECT * FROM articles WHERE topic_id = ?");
$artStmt->execute([$topicId]);
$article = $artStmt->fetch(PDO::FETCH_ASSOC);

// İlgili Testler (Quizzes)
// Çözülüp çözülmediğini de kontrol ediyoruz (LEFT JOIN ile)
$quizStmt = $pdo->prepare("
    SELECT q.*, 
           (SELECT MAX(score) FROM quiz_results WHERE quiz_id = q.id AND user_id = ?) as best_score,
           (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id AND user_id = ?) as is_solved
    FROM quizzes q 
    WHERE q.topic_id = ?
");
$quizStmt->execute([$userId, $userId, $topicId]);
$quizzes = $quizStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6 font-bold">
            <a href="library.php" class="hover:text-indigo-600">Kütüphane</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <a href="library.php?lesson_id=<?= $topic['lesson_id'] ?>" class="hover:text-indigo-600"><?= $topic['lesson_name'] ?></a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span class="text-gray-900"><?= $topic['name'] ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- SOL KOLON: KONU ANLATIMI -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <?php if ($article): ?>
                        <div class="mb-6 border-b border-gray-100 pb-4">
                            <h1 class="text-3xl font-extrabold text-gray-900"><?= $article['title'] ?></h1>
                            <p class="text-sm text-gray-400 mt-2"><i class="fa-regular fa-clock"></i> Okuma Süresi: 5 dk</p>
                        </div>
                        
                        <!-- İçerik Alanı (Prose) -->
                        <div class="prose prose-indigo max-w-none text-gray-700 leading-relaxed">
                            <?= $article['content'] ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <i class="fa-solid fa-book-open-reader text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-bold">Bu konu için henüz anlatım eklenmemiş.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SAĞ KOLON: İLGİLİ TESTLER -->
            <div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-pen-ruler text-indigo-600"></i> Kendini Test Et
                    </h3>
                    
                    <?php if(count($quizzes) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach($quizzes as $q): ?>
                            <div class="p-4 rounded-xl border-2 <?= $q['is_solved'] ? 'border-green-100 bg-green-50' : 'border-gray-100 hover:border-indigo-100 bg-white' ?> transition group">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border 
                                        <?= $q['difficulty'] == 'kolay' ? 'text-green-600 border-green-200' : ($q['difficulty'] == 'orta' ? 'text-yellow-600 border-yellow-200' : 'text-red-600 border-red-200') ?>">
                                        <?= ucfirst($q['difficulty']) ?>
                                    </span>
                                    <?php if($q['is_solved']): ?>
                                        <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <h4 class="font-bold text-gray-800 mb-3 text-sm leading-snug"><?= $q['title'] ?></h4>
                                
                                <a href="quiz.php?id=<?= $q['id'] ?>" class="block w-full text-center py-2 rounded-lg text-sm font-bold transition 
                                    <?= $q['is_solved'] ? 'bg-white text-green-600 border border-green-200' : 'bg-gray-900 text-white hover:bg-indigo-600' ?>">
                                    <?= $q['is_solved'] ? 'Tekrar Çöz' : 'Başla' ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 italic">Bu konuya ait test henüz yok.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>