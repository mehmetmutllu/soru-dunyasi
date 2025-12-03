<?php 
require_once 'includes/header.php'; 

if (!isset($_GET['id'])) {
    die("Test ID belirtilmedi.");
}

$quizId = intval($_GET['id']);
$msg = '';

// Test Bilgisini Çek (Başlığı göstermek için)
$stmt = $pdo->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) die("Test bulunamadı.");

// SORU EKLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $_POST['question_text'];
    $optA = $_POST['option_a'];
    $optB = $_POST['option_b'];
    $optC = $_POST['option_c'];
    $optD = $_POST['option_d'];
    $optE = $_POST['option_e'];
    $correct = $_POST['correct_answer'];

    $insertStmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($insertStmt->execute([$quizId, $question, $optA, $optB, $optC, $optD, $optE, $correct])) {
        $msg = '<div class="bg-green-100 text-green-700 p-4 rounded mb-6 font-bold flex justify-between items-center">
                    <span><i class="fa-solid fa-check-circle mr-2"></i> Soru başarıyla eklendi!</span>
                    <a href="quizzes.php" class="text-sm underline">Listeye Dön</a>
                </div>';
    } else {
        $msg = '<div class="bg-red-100 text-red-700 p-4 rounded mb-6 font-bold">Hata oluştu.</div>';
    }
}
?>

<div class="max-w-4xl mx-auto">
    
    <div class="mb-6 flex items-center gap-2 text-gray-500 text-sm font-bold">
        <a href="quizzes.php" class="hover:text-indigo-600">Testler</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span>Soru Ekle</span>
    </div>

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Soru Ekle</h1>
            <p class="text-gray-500 mt-1">Eklenen Test: <span class="text-indigo-600 font-bold"><?= $quiz['title'] ?></span></p>
        </div>
        <a href="quizzes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded font-bold transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Geri Dön
        </a>
    </div>

    <?= $msg ?>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form action="" method="POST">
            
            <div class="mb-6">
                <label class="block text-lg font-bold text-gray-800 mb-2">Soru Metni</label>
                <textarea name="question_text" required rows="3" class="w-full border-2 border-gray-200 p-3 rounded-lg focus:border-indigo-500 focus:ring-0 outline-none transition text-lg" placeholder="Soruyu buraya yazın..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block font-bold text-gray-600 mb-1">A Şıkkı</label>
                    <div class="flex">
                        <span class="bg-gray-100 border border-gray-300 border-r-0 rounded-l px-3 flex items-center font-bold text-gray-500">A</span>
                        <input type="text" name="option_a" required class="w-full border border-gray-300 p-2 rounded-r focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">B Şıkkı</label>
                    <div class="flex">
                        <span class="bg-gray-100 border border-gray-300 border-r-0 rounded-l px-3 flex items-center font-bold text-gray-500">B</span>
                        <input type="text" name="option_b" required class="w-full border border-gray-300 p-2 rounded-r focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">C Şıkkı</label>
                    <div class="flex">
                        <span class="bg-gray-100 border border-gray-300 border-r-0 rounded-l px-3 flex items-center font-bold text-gray-500">C</span>
                        <input type="text" name="option_c" required class="w-full border border-gray-300 p-2 rounded-r focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-gray-600 mb-1">D Şıkkı</label>
                    <div class="flex">
                        <span class="bg-gray-100 border border-gray-300 border-r-0 rounded-l px-3 flex items-center font-bold text-gray-500">D</span>
                        <input type="text" name="option_d" required class="w-full border border-gray-300 p-2 rounded-r focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold text-gray-600 mb-1">E Şıkkı (Opsiyonel)</label>
                    <div class="flex">
                        <span class="bg-gray-100 border border-gray-300 border-r-0 rounded-l px-3 flex items-center font-bold text-gray-500">E</span>
                        <input type="text" name="option_e" class="w-full border border-gray-300 p-2 rounded-r focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="LGS için boş bırakılabilir">
                    </div>
                </div>
            </div>

            <div class="mb-8 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                <label class="block text-lg font-bold text-indigo-900 mb-2">Doğru Cevap Hangisi?</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded border hover:border-indigo-500">
                        <input type="radio" name="correct_answer" value="a" required class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold">A</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded border hover:border-indigo-500">
                        <input type="radio" name="correct_answer" value="b" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold">B</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded border hover:border-indigo-500">
                        <input type="radio" name="correct_answer" value="c" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold">C</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded border hover:border-indigo-500">
                        <input type="radio" name="correct_answer" value="d" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold">D</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded border hover:border-indigo-500">
                        <input type="radio" name="correct_answer" value="e" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-bold">E</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-200 transition text-lg">
                <i class="fa-solid fa-save mr-2"></i> Soruyu Kaydet
            </button>
        </form>
    </div>
</div>

</body>
</html>