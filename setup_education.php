<?php
require_once 'includes/db.php';

echo "<body style='font-family: sans-serif; background: #f3f4f6; padding: 40px;'>";
echo "<div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #4f46e5;'>Eğitim Modülü Kurulumu</h2>";

try {
    // 1. SINAVLAR (Exams)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `exams` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `slug` varchar(100) NOT NULL,
      `description` varchar(255) DEFAULT '',
      `color` varchar(50) DEFAULT 'bg-indigo-600',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p>✅ <b>Exams</b> tablosu hazır.</p>";

    // 2. DERSLER (Lessons)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `lessons` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `exam_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `slug` varchar(100) NOT NULL,
      `icon` varchar(50) DEFAULT 'fa-book',
      PRIMARY KEY (`id`),
      FOREIGN KEY (`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p>✅ <b>Lessons</b> tablosu hazır.</p>";

    // 3. KONULAR (Topics)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `topics` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `lesson_id` int(11) NOT NULL,
      `name` varchar(200) NOT NULL,
      `slug` varchar(200) NOT NULL,
      `order_no` int(11) DEFAULT 0,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p>✅ <b>Topics</b> tablosu hazır.</p>";

    // 4. KONU ANLATIMLARI (Articles)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `topic_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `content` LONGTEXT NOT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`topic_id`) REFERENCES `topics`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p>✅ <b>Articles</b> tablosu hazır.</p>";

    // 5. QUIZZES TABLOSUNA topic_id EKLEME
    $colCheck = $pdo->query("SHOW COLUMNS FROM `quizzes` LIKE 'topic_id'");
    if($colCheck->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `quizzes` ADD COLUMN `topic_id` INT(11) NULL DEFAULT NULL AFTER `category_id`");
        $pdo->exec("ALTER TABLE `quizzes` ADD CONSTRAINT `fk_quiz_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics`(`id`) ON DELETE SET NULL");
        echo "<p>✅ <b>Quizzes</b> tablosuna topic_id eklendi.</p>";
    }

    // --- ÖRNEK VERİLERİ GÜVENLİ EKLEME ---
    
    // Sınavlar
    $exams = [
        ['LGS (8. Sınıf)', 'lgs', 'Liseye Geçiş Sınavı Hazırlık', 'bg-orange-500'],
        ['YKS (TYT-AYT)', 'yks', 'Üniversiteye Hazırlık', 'bg-indigo-600'],
        ['Genel Kültür', 'genel', 'Kendini Geliştir', 'bg-emerald-500']
    ];
    $stmt = $pdo->prepare("INSERT INTO exams (name, slug, description, color) VALUES (?, ?, ?, ?)");
    foreach ($exams as $ex) {
        // Hata almamak için try-catch içinde, duplicate varsa geç
        try { $stmt->execute($ex); } catch(PDOException $e) {} 
    }

    // Dersler (LGS ID: 1 varsayarak)
    $lessons = [
        [1, 'T.C. İnkılap Tarihi', 'inkilap', 'fa-landmark'],
        [1, 'Matematik', 'matematik-lgs', 'fa-calculator'],
        [2, 'TYT Türkçe', 'tyt-turkce', 'fa-feather']
    ];
    $stmt = $pdo->prepare("INSERT INTO lessons (exam_id, name, slug, icon) VALUES (?, ?, ?, ?)");
    foreach ($lessons as $les) {
        try { $stmt->execute($les); } catch(PDOException $e) {}
    }

    // Konular (İnkılap ID: 1 varsayarak)
    $topics = [
        [1, 'Bir Kahraman Doğuyor', 'bir-kahraman-doguyor', 1],
        [1, 'Milli Uyanış', 'milli-uyanis', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO topics (lesson_id, name, slug, order_no) VALUES (?, ?, ?, ?)");
    foreach ($topics as $top) {
        try { $stmt->execute($top); } catch(PDOException $e) {}
    }

    // Makale (Topic ID: 1)
    $content = "<h2>Mustafa Kemal'in Çocukluğu</h2><p>Mustafa Kemal, 1881 yılında Selanik'te doğdu. Annesi Zübeyde Hanım, babası Ali Rıza Efendi'dir.</p><p>Öğrenim hayatına Mahalle Mektebi'nde başlamış, daha sonra Şemsi Efendi Okulu'na geçmiştir.</p>";
    
    // Önce var mı kontrol et, yoksa ekle
    $checkArt = $pdo->query("SELECT id FROM articles WHERE topic_id = 1");
    if ($checkArt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO articles (topic_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([1, "Mustafa Kemal'in Hayatı", $content]);
    }

    // Quiz Bağlama (Var olan bir quizi konuya bağlayalım - Örn ID: 1)
    $pdo->exec("UPDATE quizzes SET topic_id = 1 WHERE id = 1");

    echo "<hr><h3 style='color:green'>Kurulum Başarılı!</h3>";
    echo "<a href='index.php' style='display:inline-block; padding:10px 20px; background:#4f46e5; color:white; text-decoration:none; border-radius:5px;'>Ana Sayfaya Dön</a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Hata:</h3>" . $e->getMessage();
}
echo "</div></body>";
?>