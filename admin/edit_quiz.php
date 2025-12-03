<?php 
require_once 'includes/header.php'; 

if (!isset($_GET['id'])) {
    die("Test ID belirtilmedi.");
}

$quizId = intval($_GET['id']);
$msg = '';

// 1. SORU SİLME İŞLEMİ
if (isset($_GET['delete_q_id'])) {
    $delQId = intval($_GET['delete_q_id']);
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    if($stmt->execute([$delQId])) {
        $msg = '<div class="bg-green-100 text-green-700 p-3 rounded mb-4">Soru silindi.</div>';
    }
}

// 2. TEST BİLGİLERİNİ GÜNCELLEME
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quiz'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $diff = $_POST['difficulty'];
    $catId = $_POST['category_id'];

    $stmt = $pdo->prepare("UPDATE quizzes SET title=?, description=?, difficulty=?, category_id=? WHERE id=?");
    if($stmt->execute([$title, $desc, $diff, $catId, $quizId])) {
        $msg = '<div class="bg-green-100 text-green-700 p-4 rounded mb-4 font-bold">Test bilgileri güncellendi!</div>';
    } else {
        $msg = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4 font-bold">Güncelleme hatası.</div>';
    }
}

// Test Bilgilerini Çek
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) die("Test bulunamadı.");

// Kategorileri Çek
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Soruları Çek
$questionsStmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$questionsStmt->execute([$quizId]);
$questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex items-center gap-2 mb-6 text-gray-500 text-sm font-bold">
    <a href="quizzes.php" class="hover:text-indigo-600">Testler</a>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <span>Test Düzenle</span>
</div>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Testi Düzenle: <span class="text-indigo-600"><?= $quiz['title'] ?></span></h1>
    <a href="quizzes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded font-bold transition">
        <i class="fa-solid fa-arrow-left mr-2"></i> Geri Dön
    </a>
</div>

<?= $msg ?>

<!-- 1. TEST BİLGİLERİ FORMU -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Genel Bilgiler</h2>
    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Başlık</label>
            <input type="text" name="title" value="<?= $quiz['title'] ?>" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
            <select name="category_id" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $quiz['category_id'] ? 'selected' : '' ?>>
                        <?= $cat['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Açıklama</label>
            <input type="text" name="description" value="<?= $quiz['description'] ?>" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Zorluk</label>
            <select name="difficulty" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="kolay" <?= $quiz['difficulty'] == 'kolay' ? 'selected' : '' ?>>Kolay</option>
                <option value="orta" <?= $quiz['difficulty'] == 'orta' ? 'selected' : '' ?>>Orta</option>
                <option value="zor" <?= $quiz['difficulty'] == 'zor' ? 'selected' : '' ?>>Zor</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" name="update_quiz" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                <i class="fa-solid fa-save mr-2"></i> Bilgileri Güncelle
            </button>
        </div>
    </form>
</div>

<!-- 2. SORULAR LİSTESİ -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h2 class="text-xl font-bold text-gray-800">Bu Testteki Sorular (<?= count($questions) ?>)</h2>
        <a href="add_question.php?id=<?= $quizId ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded font-bold text-sm transition">
            <i class="fa-solid fa-plus mr-1"></i> Yeni Soru Ekle
        </a>
    </div>

    <table class="w-full text-left">
        <tbody class="divide-y divide-gray-100">
            <?php foreach($questions as $q): ?>
            <tr class="hover:bg-indigo-50/30 transition">
                <td class="p-4 w-16 text-center font-bold text-gray-400">#<?= $q['id'] ?></td>
                <td class="p-4">
                    <p class="font-bold text-gray-800 mb-1"><?= $q['question_text'] ?></p>
                    <div class="text-xs text-gray-500 flex gap-2">
                        <span class="<?= $q['correct_answer'] == 'a' ? 'text-green-600 font-bold' : '' ?>">A) <?= $q['option_a'] ?></span>
                        <span class="text-gray-300">|</span>
                        <span class="<?= $q['correct_answer'] == 'b' ? 'text-green-600 font-bold' : '' ?>">B) <?= $q['option_b'] ?></span>
                        <span class="text-gray-300">|</span>
                        <span class="<?= $q['correct_answer'] == 'c' ? 'text-green-600 font-bold' : '' ?>">C) <?= $q['option_c'] ?></span>
                        <span class="text-gray-300">|</span>
                        <span class="<?= $q['correct_answer'] == 'd' ? 'text-green-600 font-bold' : '' ?>">D) <?= $q['option_d'] ?></span>
                    </div>
                </td>
                <td class="p-4 text-right w-32">
                    <div class="flex justify-end gap-2">
                        <a href="edit_question.php?id=<?= $q['id'] ?>" class="bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white p-2 rounded transition">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="edit_quiz.php?id=<?= $quizId ?>&delete_q_id=<?= $q['id'] ?>" onclick="return confirm('Bu soruyu silmek istiyor musunuz?')" class="bg-red-100 text-red-600 hover:bg-red-600 hover:text-white p-2 rounded transition">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($questions) == 0): ?>
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-500">Bu testte henüz soru yok.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>