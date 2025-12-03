<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. LİDERLERİ ÇEK
$stmt = $pdo->prepare("
    SELECT u.id, u.username, 
           SUM(r.score) as total_score, 
           COUNT(r.id) as tests_solved
    FROM users u
    JOIN quiz_results r ON u.id = r.user_id
    GROUP BY u.id
    ORDER BY total_score DESC, MAX(r.created_at) ASC
    LIMIT 100
");
$stmt->execute();
$leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. KULLANICININ KENDİ SIRASINI BUL
$myRankData = null;
if ($currentUserId) {
    $myScoreStmt = $pdo->prepare("SELECT SUM(score) FROM quiz_results WHERE user_id = ?");
    $myScoreStmt->execute([$currentUserId]);
    $myScore = $myScoreStmt->fetchColumn() ?: 0;

    $rankStmt = $pdo->prepare("
        SELECT COUNT(*) + 1 
        FROM (
            SELECT user_id, SUM(score) as total 
            FROM quiz_results 
            GROUP BY user_id 
            HAVING total > ?
        ) as better_players
    ");
    $rankStmt->execute([$myScore]);
    $myRank = $rankStmt->fetchColumn();

    $myRankData = ['rank' => $myRank, 'score' => $myScore];
}
?>

<div class="bg-gray-50 min-h-screen pb-32">
    
    <!-- 1. HEADER & KÜRSÜ ALANI -->
    <!-- overflow-hidden ekleyerek taşan efektleri gizledik -->
    <div class="bg-[#1e1b4b] pb-8 pt-8 rounded-b-[2.5rem] shadow-2xl relative overflow-hidden">
        
        <!-- Arkaplan Efektleri -->
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-indigo-900 via-[#1e1b4b] to-slate-900 opacity-90"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-[#1e1b4b] to-transparent z-10"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4">
            
            <!-- Başlık (Margin artırıldı) -->
            <div class="text-center mb-16 mt-4">
                <h1 class="text-3xl md:text-5xl font-black text-white mb-2 tracking-tight drop-shadow-lg">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500">Şampiyonlar</span> Ligi
                </h1>
                <p class="text-indigo-200 text-sm md:text-lg font-medium opacity-80">En çok doğruyu yapan, bilgi dünyasının kralları.</p>
            </div>

            <?php if(count($leaders) > 0): ?>
            <!-- KÜRSÜ (PODIUM) -->
            <!-- Flex items-end ile hepsini tabana hizalıyoruz, mobilde de yan yana (row) kalıyorlar ama gap az -->
            <div class="flex items-end justify-center gap-2 md:gap-6 pb-4 min-h-[250px]">
                
                <!-- 2. SIRA (SOL) -->
                <div class="order-1 w-1/3 max-w-[150px] md:max-w-[220px] flex flex-col items-center">
                    <?php if(isset($leaders[1])): ?>
                    <div class="relative w-full">
                        <!-- Avatar -->
                        <div class="absolute -top-6 md:-top-8 left-1/2 -translate-x-1/2 z-20">
                            <div class="w-12 h-12 md:w-20 md:h-20 rounded-full bg-slate-200 border-2 md:border-4 border-[#1e1b4b] shadow-xl flex items-center justify-center text-sm md:text-2xl font-bold text-slate-600 relative">
                                <?= strtoupper(substr($leaders[1]['username'], 0, 1)) ?>
                                <div class="absolute -bottom-1 -right-1 md:-bottom-2 md:-right-1 bg-slate-600 text-white w-5 h-5 md:w-7 md:h-7 rounded-full flex items-center justify-center text-[10px] md:text-sm font-bold border border-white">2</div>
                            </div>
                        </div>
                        <!-- Kart -->
                        <div class="bg-gradient-to-b from-slate-100 to-slate-300 rounded-t-xl md:rounded-t-2xl pt-8 md:pt-14 pb-3 md:pb-5 px-1 md:px-3 shadow-xl text-center relative overflow-hidden border-t-4 border-slate-400 h-[120px] md:h-[180px] flex flex-col justify-end w-full">
                            <h3 class="font-bold text-slate-800 text-xs md:text-lg truncate w-full mb-0 md:mb-1 px-1"><?= htmlspecialchars($leaders[1]['username']) ?></h3>
                            <div class="text-slate-600 font-black text-sm md:text-2xl"><?= $leaders[1]['total_score'] ?> P</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Boş 2. Sıra -->
                    <div class="w-full h-[120px] md:h-[180px] opacity-20 bg-slate-800 rounded-t-xl"></div>
                    <?php endif; ?>
                </div>

                <!-- 1. SIRA (ORTA - BÜYÜK) -->
                <div class="order-2 w-1/3 max-w-[160px] md:max-w-[260px] flex flex-col items-center z-20 -mb-2">
                    <?php if(isset($leaders[0])): ?>
                    <div class="relative w-full transform scale-110 origin-bottom">
                        <!-- Taç -->
                        <i class="fa-solid fa-crown text-3xl md:text-5xl text-yellow-400 absolute -top-10 md:-top-14 left-1/2 -translate-x-1/2 drop-shadow-lg animate-bounce" style="animation-duration: 3s;"></i>
                        
                        <!-- Avatar -->
                        <div class="absolute -top-8 md:-top-12 left-1/2 -translate-x-1/2 z-20">
                            <div class="w-16 h-16 md:w-24 md:h-24 rounded-full bg-yellow-400 border-4 md:border-[6px] border-[#1e1b4b] shadow-[0_0_20px_rgba(250,204,21,0.5)] flex items-center justify-center text-xl md:text-4xl font-bold text-yellow-900 relative">
                                <?= strtoupper(substr($leaders[0]['username'], 0, 1)) ?>
                                <div class="absolute -bottom-2 -right-1 md:-bottom-3 md:-right-1 bg-yellow-500 text-white w-6 h-6 md:w-9 md:h-9 rounded-full flex items-center justify-center text-xs md:text-xl font-bold border-2 border-[#1e1b4b] shadow-lg">1</div>
                            </div>
                        </div>
                        
                        <!-- Kart (Sadece burada overflow-hidden var ve shine efekti içinde) -->
                        <div class="bg-gradient-to-b from-yellow-100 to-yellow-400 rounded-t-2xl md:rounded-t-3xl pt-10 md:pt-16 pb-4 md:pb-8 px-2 md:px-4 shadow-2xl text-center relative overflow-hidden h-[150px] md:h-[220px] flex flex-col justify-end border-t-4 border-yellow-300 w-full">
                            <!-- Shine Efekti -->
                            <div class="absolute top-0 -inset-full h-full w-1/2 z-0 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-40 animate-shine pointer-events-none"></div>
                            
                            <div class="relative z-10">
                                <h3 class="font-extrabold text-yellow-900 text-sm md:text-xl truncate w-full mb-0 md:mb-1 px-1"><?= htmlspecialchars($leaders[0]['username']) ?></h3>
                                <div class="text-yellow-800 font-black text-lg md:text-4xl tracking-tight mb-1 md:mb-2"><?= $leaders[0]['total_score'] ?> P</div>
                                <span class="inline-block bg-yellow-600/20 text-yellow-800 px-2 md:px-3 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold border border-yellow-600/30">
                                    🏆 LİDER
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 3. SIRA (SAĞ) -->
                <div class="order-3 w-1/3 max-w-[150px] md:max-w-[220px] flex flex-col items-center">
                    <?php if(isset($leaders[2])): ?>
                    <div class="relative w-full">
                        <!-- Avatar -->
                        <div class="absolute -top-6 md:-top-8 left-1/2 -translate-x-1/2 z-20">
                            <div class="w-12 h-12 md:w-20 md:h-20 rounded-full bg-orange-200 border-2 md:border-4 border-[#1e1b4b] shadow-xl flex items-center justify-center text-sm md:text-2xl font-bold text-orange-700 relative">
                                <?= strtoupper(substr($leaders[2]['username'], 0, 1)) ?>
                                <div class="absolute -bottom-1 -right-1 md:-bottom-2 md:-right-1 bg-orange-500 text-white w-5 h-5 md:w-7 md:h-7 rounded-full flex items-center justify-center text-[10px] md:text-sm font-bold border border-white shadow-sm">3</div>
                            </div>
                        </div>
                        <!-- Kart -->
                        <div class="bg-gradient-to-b from-orange-50 to-orange-200 rounded-t-xl md:rounded-t-2xl pt-8 md:pt-14 pb-3 md:pb-5 px-1 md:px-3 shadow-xl text-center relative overflow-hidden border-t-4 border-orange-300 h-[100px] md:h-[160px] flex flex-col justify-end w-full">
                            <h3 class="font-bold text-orange-900 text-xs md:text-lg truncate w-full mb-0 md:mb-1 px-1"><?= htmlspecialchars($leaders[2]['username']) ?></h3>
                            <div class="text-orange-700 font-black text-sm md:text-2xl"><?= $leaders[2]['total_score'] ?> P</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Boş 3. Sıra -->
                    <div class="w-full h-[100px] md:h-[160px] opacity-20 bg-slate-800 rounded-t-xl"></div>
                    <?php endif; ?>
                </div>

            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. LİSTE ALANI (4-100 ARASI) -->
    <!-- Podyumun hemen altına, üstüne binmeden yerleştirildi -->
    <div class="max-w-3xl mx-auto px-4 mt-8 relative z-30 mb-20">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden fade-in">
            <div class="p-5 border-b border-gray-100 bg-gray-50/80 backdrop-blur-sm flex justify-between items-center sticky top-0 z-10">
                <span class="text-sm font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-ol"></i> Sıralama
                </span>
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Top 100</span>
            </div>
            
            <div class="divide-y divide-gray-100 min-h-[100px]">
                <?php if(count($leaders) > 3): ?>
                    <?php for($i = 3; $i < count($leaders); $i++): 
                        $isMe = ($currentUserId == $leaders[$i]['id']);
                        $rowClass = $isMe ? "bg-indigo-50 border-l-4 border-indigo-600" : "hover:bg-gray-50";
                    ?>
                    <div class="flex items-center p-4 <?= $rowClass ?> transition duration-200">
                        <div class="w-10 text-center font-bold text-gray-400 text-sm md:text-lg mr-2">
                            #<?= $i + 1 ?>
                        </div>
                        <div class="flex items-center gap-3 md:gap-4 flex-grow">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gray-100 text-gray-600 font-bold flex items-center justify-center border border-gray-200 shadow-sm shrink-0 text-xs md:text-base">
                                <?= strtoupper(substr($leaders[$i]['username'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-sm md:text-base truncate <?= $isMe ? 'text-indigo-700' : '' ?>">
                                    <?= htmlspecialchars($leaders[$i]['username']) ?>
                                    <?php if($isMe): ?><span class="ml-2 text-[10px] bg-indigo-600 text-white px-2 py-0.5 rounded-full shadow-sm align-middle">SEN</span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right pl-2">
                            <span class="block font-black text-indigo-600 text-base md:text-lg"><?= $leaders[$i]['total_score'] ?></span>
                            <span class="text-[10px] text-gray-400 uppercase font-bold hidden md:block">Puan</span>
                        </div>
                    </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <!-- LİSTE BOŞ MESAJI -->
                    <div class="p-8 text-center text-gray-400 flex flex-col items-center justify-center h-40">
                        <i class="fa-solid fa-users mb-2 text-2xl opacity-50"></i>
                        <p class="text-sm font-medium">Henüz 4. ve sonrası için sıralama oluşmadı.</p>
                        <p class="text-xs mt-1 text-gray-300">Yarışmaya katıl ve listeyi doldur!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 3. SENİN SIRALAMAN (STICKY FOOTER) -->
    <?php if($currentUserId && $myRankData): ?>
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 shadow-[0_-5px_30px_rgba(0,0,0,0.15)] py-3 px-4 z-50 animate-slide-up">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg md:text-xl shadow-lg border-2 border-indigo-400 relative">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        <div class="absolute -top-2 -left-2 bg-yellow-400 text-yellow-900 text-[10px] px-2 py-0.5 rounded-full font-bold shadow-sm border border-white">
                            #<?= $myRankData['rank'] ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Senin Sıralaman</div>
                        <div class="text-sm font-bold text-gray-800 flex items-center gap-1">
                            <?php if($myRankData['rank'] <= 100): ?>
                                <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded text-xs hidden md:inline-flex">Listedesin! 🎉</span>
                                <span class="text-green-600 md:hidden text-xs">Listedesin!</span>
                            <?php else: ?>
                                <span class="text-orange-500 bg-orange-50 px-2 py-0.5 rounded text-xs hidden md:inline-flex">Yükselmelisin! 🚀</span>
                                <span class="text-orange-500 md:hidden text-xs">Yüksel!</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-xl md:text-2xl font-black text-indigo-600 leading-none"><?= $myRankData['score'] ?></div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">Toplam Puan</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
@keyframes shine {
    100% { left: 125%; }
}
.animate-shine {
    animation: shine 3s infinite;
}
@keyframes slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.5s ease-out forwards;
}
/* Özel Blob Animasyonu */
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
</style>

<?php require_once 'includes/footer.php'; ?>