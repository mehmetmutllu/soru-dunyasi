<?php require_once 'includes/header.php'; ?>

<?php
// İstatistikleri Çek
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$quizCount = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$questionCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$solvedCount = $pdo->query("SELECT COUNT(*) FROM quiz_results")->fetchColumn();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Hoşgeldin, Yönetici! 👋</h1>
    <p class="text-gray-500">Sistem istatistiklerine hızlı bir bakış.</p>
</div>

<!-- İstatistik Kartları -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <!-- Kart 1 -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $userCount ?></div>
            <div class="text-xs text-gray-500 uppercase font-bold">Üye</div>
        </div>
    </div>

    <!-- Kart 2 -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $quizCount ?></div>
            <div class="text-xs text-gray-500 uppercase font-bold">Test</div>
        </div>
    </div>

    <!-- Kart 3 -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $questionCount ?></div>
            <div class="text-xs text-gray-500 uppercase font-bold">Soru</div>
        </div>
    </div>

    <!-- Kart 4 -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-800"><?= $solvedCount ?></div>
            <div class="text-xs text-gray-500 uppercase font-bold">Çözülen Test</div>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Hızlı İşlemler</h3>
    <a href="quizzes.php" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 transition">
        <i class="fa-solid fa-plus"></i> Yeni Test Oluştur
    </a>
</div>

</body>
</html>