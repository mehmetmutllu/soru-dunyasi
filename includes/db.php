<?php
$host = 'localhost';
$dbname = 'soru_dunyasi';
$username = 'root';
$password = '12345678'; // AppServ'de bazen şifre '123456' veya 'root' olabilir, boşsa boş bırak.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>