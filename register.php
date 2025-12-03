<?php
session_start();
require_once 'includes/db.php';

// KONTROL: Eğer kullanıcı zaten giriş yapmışsa, kayıt sayfasına girmesine gerek yok.
// Direkt anasayfaya yönlendiriyoruz.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageType = ''; // success veya error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "Lütfen tüm alanları doldurun.";
        $messageType = "error";
    } elseif ($password !== $passwordConfirm) {
        $message = "Şifreler birbiriyle uyuşmuyor.";
        $messageType = "error";
    } else {
        // E-posta veya kullanıcı adı daha önce alınmış mı kontrol et
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        
        if ($check->rowCount() > 0) {
            $message = "Bu e-posta veya kullanıcı adı zaten kullanılıyor.";
            $messageType = "error";
        } else {
            // Kayıt işlemi
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $message = "Kayıt başarılı! Giriş sayfasına yönlendiriliyorsunuz...";
                $messageType = "success";
                header("refresh:2;url=login.php");
            } else {
                $message = "Bir hata oluştu, lütfen tekrar deneyin.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Soru Dünyası</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        <div class="text-center">
            <div class="bg-indigo-100 text-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Aramıza Katıl</h2>
            <p class="mt-2 text-sm text-gray-600">
                Zaten hesabın var mı? <a href="login.php" class="font-bold text-indigo-600 hover:text-indigo-500">Giriş Yap</a>
            </p>
        </div>

        <?php if($message): ?>
        <div class="p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> text-center font-bold">
            <?= $message ?>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-bold text-gray-700 mb-1">Kullanıcı Adı</label>
                    <input id="username" name="username" type="text" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: Sorubükücü">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">E-Posta Adresi</label>
                    <input id="email" name="email" type="email" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="ornek@mail.com">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Şifre</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="******">
                </div>
                <div class="mb-4">
                    <label for="password_confirm" class="block text-sm font-bold text-gray-700 mb-1">Şifre Tekrar</label>
                    <input id="password_confirm" name="password_confirm" type="password" required class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="******">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-lg shadow-indigo-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-arrow-right text-indigo-400 group-hover:text-indigo-300"></i>
                    </span>
                    Kayıt Ol
                </button>
            </div>
        </form>
    </div>
</body>
</html>