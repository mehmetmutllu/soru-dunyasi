<?php
// Oturum başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soru Dünyası</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <!-- Konfeti Efekti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        h1, h2, h3, .logo-font { font-family: 'Fredoka', sans-serif; }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Dropdown Menü Animasyonu */
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s ease-in-out;
        }
        .group:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Mobil Menü Animasyonu */
        #mobile-menu {
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 400px; /* Menü içeriğine göre artırılabilir */
            opacity: 1;
        }
    </style>
</head>
<body class="flex flex-col text-gray-800">

<nav class="glass-effect border-b border-white/20 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-2 rounded-xl shadow-lg group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-brain text-xl md:text-2xl"></i>
                </div>
                <!-- Mobilde Sadece İkon, Masaüstünde Yazı -->
                <span class="logo-font text-xl md:text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 hidden md:block">Soru Dünyası</span>
                <span class="logo-font text-xl font-bold text-gray-800 md:hidden">Soru Dünyası</span>
            </a>

            <!-- MASAÜSTÜ MENÜ (MD ve Üstü) -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="font-bold text-gray-600 hover:text-indigo-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-house text-sm"></i> Anasayfa
                </a>

                <a href="leaderboard.php" class="font-bold text-gray-600 hover:text-indigo-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-sm text-yellow-500"></i> Liderlik
                </a>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- GİRİŞ YAPMIŞ KULLANICI -->
                    <div class="relative group">
                        <button class="flex items-center gap-3 focus:outline-none py-2">
                            <span class="text-sm font-bold text-gray-600 text-right leading-tight">
                                Merhaba,<br>
                                <span class="text-indigo-600 text-base"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </span>
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg border-2 border-indigo-200">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform group-hover:rotate-180"></i>
                        </button>

                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition">
                                <i class="fa-solid fa-user w-6"></i> Profilim
                            </a>
                            
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="block px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50 font-bold transition">
                                <i class="fa-solid fa-gears w-6"></i> Yönetim Paneli
                            </a>
                            <?php endif; ?>

                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold transition">
                                <i class="fa-solid fa-right-from-bracket w-6"></i> Çıkış Yap
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- MİSAFİR -->
                    <div class="flex items-center gap-3">
                        <a href="login.php" class="font-bold text-gray-600 hover:text-indigo-600 transition px-3 py-2">Giriş Yap</a>
                        <a href="register.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-full transition shadow-lg shadow-indigo-200 transform hover:-translate-y-0.5">
                            Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- MOBİL MENÜ BUTONU (Hamburger) -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-gray-600 hover:text-indigo-600 focus:outline-none p-2">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- MOBİL MENÜ İÇERİĞİ -->
    <div id="mobile-menu" class="md:hidden bg-white border-b border-gray-100 shadow-lg">
        <div class="px-4 pt-2 pb-4 space-y-2">
            <a href="index.php" class="block px-3 py-3 rounded-lg text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">
                <i class="fa-solid fa-house w-6 text-center"></i> Anasayfa
            </a>
            <a href="leaderboard.php" class="block px-3 py-3 rounded-lg text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">
                <i class="fa-solid fa-trophy w-6 text-center text-yellow-500"></i> Liderlik
            </a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="border-t border-gray-100 my-2"></div>
                <div class="px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Hesabım</div>
                
                <a href="profile.php" class="block px-3 py-3 rounded-lg text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">
                    <i class="fa-solid fa-user w-6 text-center"></i> Profilim (<?= htmlspecialchars($_SESSION['username']) ?>)
                </a>
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin/index.php" class="block px-3 py-3 rounded-lg text-base font-bold text-emerald-600 hover:bg-emerald-50">
                    <i class="fa-solid fa-gears w-6 text-center"></i> Yönetim Paneli
                </a>
                <?php endif; ?>

                <a href="logout.php" class="block px-3 py-3 rounded-lg text-base font-bold text-red-600 hover:bg-red-50">
                    <i class="fa-solid fa-right-from-bracket w-6 text-center"></i> Çıkış Yap
                </a>
            <?php else: ?>
                <div class="border-t border-gray-100 my-2 pt-2 grid grid-cols-2 gap-3">
                    <a href="login.php" class="block w-full text-center px-4 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50">
                        Giriş Yap
                    </a>
                    <a href="register.php" class="block w-full text-center px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-md">
                        Kayıt Ol
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Mobil Menü JS -->
<script>
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');

    btn.addEventListener('click', () => {
        menu.classList.toggle('open');
        
        // İkonu değiştir (Hamburger <-> Çarpı)
        const icon = btn.querySelector('i');
        if (menu.classList.contains('open')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
</script>

<main class="flex-grow">