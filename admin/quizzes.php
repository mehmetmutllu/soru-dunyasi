<?php 
require_once 'includes/header.php'; 

$msg = '';

// 1. SİLME İŞLEMİ
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    
    if ($_SESSION['role'] == 'admin') {
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        if ($stmt->execute([$deleteId])) {
            $msg = '<div class="bg-green-100 text-green-700 p-4 rounded mb-4 font-bold"><i class="fa-solid fa-check mr-2"></i> Test ve ilgili tüm veriler silindi.</div>';
        } else {
            $msg = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4 font-bold">Silme işlemi başarısız.</div>';
        }
    }
}

// 2. YENİ TEST EKLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_quiz'])) {
    $categoryId = $_POST['category_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $diff = $_POST['difficulty'];

    if(!empty($title) && !empty($desc)) {
        $stmt = $pdo->prepare("INSERT INTO quizzes (category_id, title, description, difficulty) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$categoryId, $title, $desc, $diff])) {
            $msg = '<div class="bg-green-100 text-green-700 p-4 rounded mb-4 font-bold">Test başarıyla oluşturuldu!</div>';
        } else {
            $msg = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4 font-bold">Hata oluştu.</div>';
        }
    }
}

// Kategorileri Çek
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Testleri Listele
$quizzes = $pdo->query("SELECT q.*, c.name as cat_name, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as q_count FROM quizzes q JOIN categories c ON q.category_id = c.id ORDER BY q.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Test Yönetimi</h1>
</div>

<?= $msg ?>

<!-- YENİ TEST EKLEME FORMU -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Yeni Test Oluştur</h2>
    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Başlık</label>
            <input type="text" name="title" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Örn: 1. Dünya Savaşı">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
            <select name="category_id" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Açıklama</label>
            <input type="text" name="description" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Test hakkında kısa bilgi...">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Zorluk</label>
            <select name="difficulty" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="kolay">Kolay</option>
                <option value="orta">Orta</option>
                <option value="zor">Zor</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" name="add_quiz" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                <i class="fa-solid fa-plus mr-2"></i> Oluştur
            </button>
        </div>
    </form>
</div>

<!-- MEVCUT TESTLER LİSTESİ -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase border-b">
            <tr>
                <th class="p-4">ID</th>
                <th class="p-4">Başlık</th>
                <th class="p-4">Kategori</th>
                <th class="p-4">Soru Sayısı</th>
                <th class="p-4 text-right">İşlemler</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach($quizzes as $q): ?>
            <tr class="hover:bg-gray-50">
                <td class="p-4 font-mono text-gray-500">#<?= $q['id'] ?></td>
                <td class="p-4 font-bold text-gray-800"><?= $q['title'] ?></td>
                <td class="p-4"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold"><?= $q['cat_name'] ?></span></td>
                <td class="p-4">
                    <span class="font-bold <?= $q['q_count'] > 0 ? 'text-green-600' : 'text-red-500' ?>"><?= $q['q_count'] ?></span>
                </td>
                <td class="p-4 text-right flex justify-end gap-2">
                    <!-- Soru Ekle -->
                    <a href="add_question.php?id=<?= $q['id'] ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm font-bold transition flex items-center" title="Soru Ekle">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    
                    <!-- Düzenle (YENİ) -->
                    <a href="edit_quiz.php?id=<?= $q['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-bold transition flex items-center" title="Düzenle ve Soruları Gör">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    
                    <!-- Sil -->
                    <a href="quizzes.php?delete_id=<?= $q['id'] ?>" onclick="return confirm('Bu testi ve içindeki tüm soruları silmek istediğinize emin misiniz?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-bold transition flex items-center" title="Sil">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>