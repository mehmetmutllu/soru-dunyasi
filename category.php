<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// URL'den slug bilgisini al (örn: ?slug=tarih)
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (!$slug) {
    echo "<div class='container mx-auto mt-10 p-4 bg-red-100 text-red-700 rounded'>Kategori bulunamadı.</div>";
    require_once 'includes/footer.php';
    exit;
}

try {
    // 1. Kategori Bilgilerini Çek
    $catStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $catStmt->execute([$slug]);
    $category = $catStmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo "<div class='container mx-auto mt-10 p-4 bg-red-100 text-red-700 rounded'>Böyle bir kategori bulunamadı.</div>";
        require_once 'includes/footer.php';
        exit;
    }

    // 2. Bu Kategoriye Ait Testleri Çek
    $quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE category_id = ? ORDER BY id DESC");
    $quizStmt->execute([$category['id']]);
    $quizzes = $quizStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Zorluk derecesi renkleri
$difficultyColors = [
    'kolay' => 'text-green-600 bg-green-100 border-green-200',
    'orta' => 'text-yellow-600 bg-yellow-100 border-yellow-200',
    'zor' => 'text-red-600 bg-red-100 border-red-200'
];
?>

<div class="relative pt-12 pb-20">
    <!-- Kategori Başlığı ve İkonu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12 fade-in">
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
            <!-- Arka Plan Süsü -->
            <div class="absolute top-0 right-0 w-64 h-64 <?= $category['color'] ?> opacity-10 rounded-full blur-3xl -mr-16 -mt-16"></div>

            <div class="<?= $category['color'] ?> w-24 h-24 rounded-2xl flex items-center justify-center text-white text-4xl shadow-lg transform rotate-3">
                <i class="fa-solid <?= $category['icon'] ?>"></i>
            </div>
            
            <div class="text-center md:text-left z-10">
                <span class="text-gray-500 font-bold uppercase tracking-widest text-sm">Kategori</span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mt-1 mb-2"><?= $category['name'] ?></h1>
                <p class="text-gray-600 text-lg">Bu alanda toplam <strong class="text-indigo-600"><?= count($quizzes) ?></strong> test bulunmaktadır.</p>
            </div>
        </div>
    </div>

    <!-- Test Listesi -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (count($quizzes) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in" style="animation-delay: 0.2s;">
                <?php foreach($quizzes as $quiz): 
                    $badgeClass = isset($difficultyColors[$quiz['difficulty']]) 
                                  ? $difficultyColors[$quiz['difficulty']] 
                                  : 'text-gray-600 bg-gray-100';
                ?>
                <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border border-gray-100 transition-all duration-300 transform hover:-translate-y-2 flex flex-col h-full group">
                    <div class="<?= $category['color'] ?> h-2 w-full rounded-t-2xl"></div>
                    
                    <div class="p-6 flex-grow flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <!-- Zorluk Rozeti -->
                            <span class="text-xs font-bold px-3 py-1 rounded-full border uppercase tracking-wide <?= $badgeClass ?>">
                                <?= ucfirst($quiz['difficulty']) ?>
                            </span>
                            
                            <!-- İkon -->
                            <span class="text-gray-300 group-hover:text-indigo-500 transition">
                                <i class="fa-solid fa-circle-play text-2xl"></i>
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                            <?= $quiz['title'] ?>
                        </h3>
                        <p class="text-gray-500 text-sm mb-6 line-clamp-2 flex-grow">
                            <?= $quiz['description'] ?>
                        </p>
                        
                        <div class="pt-4 border-t border-gray-50 flex items-center justify-between mt-auto">
                            <span class="text-xs text-gray-400 font-bold">
                                <i class="fa-regular fa-clock mr-1"></i> 10 Soru
                            </span>
                            <a href="quiz.php?id=<?= $quiz['id'] ?>" class="text-indigo-600 font-bold text-sm hover:underline flex items-center gap-1">
                                Teste Başla <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                <div class="inline-block p-6 rounded-full bg-gray-50 text-gray-400 mb-4">
                    <i class="fa-solid fa-folder-open text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700">Henüz Test Eklenmemiş</h3>
                <p class="text-gray-500 mt-2">Bu kategoriye yakında yeni testler eklenecektir.</p>
                <a href="index.php" class="mt-6 inline-block text-indigo-600 font-bold hover:underline">Ana Sayfaya Dön</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>