<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'guest', 'message' => 'Misafir kullanıcı.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Veri alınamadı.']);
    exit;
}

$userId = $_SESSION['user_id'];
$quizId = intval($data['quiz_id']);
$score = intval($data['score']);
$total = intval($data['total']);
$answers = $data['answers']; // YENİ: Cevap listesi (Array)
$percentage = ($total > 0) ? round(($score / $total) * 100) : 0;

try {
    $pdo->beginTransaction(); // İşlemleri güvenli yap (Transaction)

    // 1. Ana sonucu kaydet
    $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $quizId, $score, $total, $percentage]);
    
    // Oluşan Sonuç ID'sini al (Cevapları buna bağlayacağız)
    $resultId = $pdo->lastInsertId();

    // 2. Detaylı cevapları kaydet
    $ansStmt = $pdo->prepare("INSERT INTO user_answers (result_id, question_id, user_choice, is_correct) VALUES (?, ?, ?, ?)");
    
    foreach ($answers as $ans) {
        $ansStmt->execute([
            $resultId,
            $ans['question_id'],
            $ans['user_choice'],
            $ans['is_correct'] ? 1 : 0
        ]);
    }

    $pdo->commit(); // Hepsini onayla
    echo json_encode(['status' => 'success', 'message' => 'Detaylı kayıt başarılı.', 'result_id' => $resultId]);

} catch (PDOException $e) {
    $pdo->rollBack(); // Hata varsa işlemleri geri al
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>