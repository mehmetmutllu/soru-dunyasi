<?php
// Hata raporlamayı açalım ki sorun varsa görelim (Production'da kapatılır)
error_reporting(E_ALL);
ini_set('display_errors', 0); // JSON bozmamak için ekrana basmayı kapat

header('Content-Type: application/json; charset=utf-8');

try {
    // db.php dosyasını bulamazsa hata vermesi için require
    require_once '../includes/db.php';

    if (!isset($_GET['id'])) {
        throw new Exception('Test ID bulunamadı');
    }

    $quizId = intval($_GET['id']);

    // Soruları Çek
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Test Bilgilerini Çek
    $quizStmt = $pdo->prepare("SELECT title, description FROM quizzes WHERE id = ?");
    $quizStmt->execute([$quizId]);
    $quizInfo = $quizStmt->fetch(PDO::FETCH_ASSOC);

    if (!$quizInfo) {
        throw new Exception('Test bulunamadı.');
    }

    echo json_encode([
        'info' => $quizInfo,
        'questions' => $questions
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Hata durumunda JSON formatında hata döndür
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>