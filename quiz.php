<?php
require_once 'includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <!-- Quiz Kartı -->
    <div id="quiz-container" class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-white/60 p-8 md:p-12 relative overflow-hidden fade-in">
        
        <!-- Arka Plan Süslemeleri -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-100 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-100 rounded-full blur-3xl opacity-50"></div>

        <!-- Yükleniyor Ekranı -->
        <div id="loading-screen" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600 mb-4"></div>
            <h2 class="text-xl font-bold text-gray-600">Sorular Hazırlanıyor...</h2>
        </div>

        <!-- İçerik (Başlangıçta Gizli) -->
        <div id="quiz-content" class="hidden relative z-10">
            <!-- Üst Bilgi -->
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-gray-100 pb-6">
                <div>
                    <span class="text-indigo-600 font-bold uppercase tracking-widest text-xs mb-2 block">Test</span>
                    <h1 id="quiz-title" class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight">...</h1>
                </div>
                <div class="mt-4 md:mt-0 text-right">
                    <div class="text-sm font-bold text-gray-400 mb-1">İlerleme</div>
                    <div class="text-2xl font-black text-indigo-600 font-mono" id="progress-text">1/10</div>
                </div>
            </div>

            <!-- İlerleme Çubuğu -->
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-10 overflow-hidden">
                <div id="progress-bar" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full transition-all duration-700 ease-out shadow-[0_0_10px_rgba(99,102,241,0.5)]" style="width: 0%"></div>
            </div>

            <!-- Soru Alanı -->
            <div class="mb-10 min-h-[100px] flex items-center">
                <h2 id="question-text" class="text-2xl md:text-3xl font-bold text-gray-800 leading-snug">
                    ...
                </h2>
            </div>

            <!-- Şıklar -->
            <div id="options-container" class="grid grid-cols-1 gap-4">
                <!-- Butonlar buraya JS ile gelecek -->
            </div>
        </div>

    </div>
</div>

<script src="assets/js/quiz.js"></script>

<?php require_once 'includes/footer.php'; ?>