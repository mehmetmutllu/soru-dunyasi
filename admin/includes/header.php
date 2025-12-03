<?php
session_start();
// Bir üst klasördeki db.php'ye ulaşmak için "../" kullanıyoruz
require_once '../includes/db.php';

// GÜVENLİK KONTROLÜ
// Giriş yapmamışsa VEYA rolü admin değilse at!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - Soru Dünyası</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex min-h-screen">

    <!-- Sidebar (Sol Menü) -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex">
        <div class="h-20 flex items-center justify-center border-b border-slate-800">
            <span class="text-xl font-bold text-indigo-400"><i class="fa-solid fa-user-shield mr-2"></i>Admin Panel</span>
        </div>
        
        <nav class="flex-grow p-4 space-y-2">
            <a href="index.php" class="block p-3 rounded hover:bg-slate-800 transition flex items-center gap-3">
                <i class="fa-solid fa-chart-line w-6"></i> Özet Durum
            </a>
            <a href="quizzes.php" class="block p-3 rounded hover:bg-slate-800 transition flex items-center gap-3">
                <i class="fa-solid fa-list-check w-6"></i> Test Yönetimi
            </a>
            <a href="../index.php" target="_blank" class="block p-3 rounded hover:bg-slate-800 text-emerald-400 transition flex items-center gap-3">
                <i class="fa-solid fa-arrow-up-right-from-square w-6"></i> Siteyi Görüntüle
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="../logout.php" class="block p-3 rounded bg-red-600 hover:bg-red-700 text-center font-bold transition">
                Çıkış Yap
            </a>
        </div>
    </aside>

    <!-- Mobil İçin Basit Üst Bar (Telefondan girilirse) -->
    <div class="flex-grow flex flex-col h-screen overflow-y-auto">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 md:hidden">
            <span class="font-bold text-slate-800">Admin Panel</span>
            <a href="../index.php" class="text-sm text-indigo-600 font-bold">Siteye Dön</a>
        </header>
        
        <!-- Ana İçerik -->
        <main class="p-6 md:p-10 flex-grow">