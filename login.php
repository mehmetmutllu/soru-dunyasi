<?php
session_start();
require_once 'includes/db.php';

// Zaten giriş yapmışsa anasayfaya at
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Lütfen e-posta ve şifrenizi girin.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Giriş Başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit;
        } else {
            $message = "E-posta veya şifre hatalı.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Soru Dünyası</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        <div class="text-center">
            <div class="bg-indigo-100 text-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Tekrar Hoşgeldin!</h2>
            <p class="mt-2 text-sm text-gray-600">
                Hesabın yok mu? <a href="register.php" class="font-bold text-indigo-600 hover:text-indigo-500">Kayıt Ol</a>
            </p>
        </div>

        <?php if($message): ?>
        <div class="p-4 rounded-lg bg-red-100 text-red-700 text-center font-bold">
            <?= $message ?>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">E-Posta Adresi</label>
                    <input id="email" name="email" type="email" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="ornek@mail.com">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Şifre</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="******">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-lg shadow-indigo-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-lock text-indigo-400 group-hover:text-indigo-300"></i>
                    </span>
                    Giriş Yap
                </button>
            </div>
            <div class="text-center">
                 <a href="index.php" class="text-sm text-gray-400 hover:text-gray-600">← Anasayfaya dön</a>
            </div>
        </form>
    </div>
</body>
</html>