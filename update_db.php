<?php
require_once 'includes/db.php';

try {
    // 1. users tablosuna 'streak' ve 'last_seen' sütunlarını ekle
    // IF NOT EXISTS diyerek hata vermesini önlüyoruz (MySQL sürümlerine göre değişebilir, bu yüzden catch bloğu var)
    $sql = "ALTER TABLE users 
            ADD COLUMN streak INT DEFAULT 0, 
            ADD COLUMN last_seen DATE DEFAULT NULL";
            
    $pdo->exec($sql);
    
    echo "<h1 style='color:green'>✅ Veritabanı Başarıyla Güncellendi!</h1>";
    echo "<p>Artık Streak (Günlük Seri) sistemi çalışacak.</p>";
    echo "<a href='index.php'>Ana Sayfaya Dön</a>";

} catch (PDOException $e) {
    // Eğer sütunlar zaten varsa hata verir, sorun değil.
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "<h1 style='color:orange'>ℹ️ Tablo Zaten Güncel</h1>";
        echo "<p>Bu güncelleme daha önce yapılmış.</p>";
        echo "<a href='index.php'>Ana Sayfaya Dön</a>";
    } else {
        echo "<h1 style='color:red'>❌ Hata Oluştu</h1>";
        echo $e->getMessage();
    }
}
?>