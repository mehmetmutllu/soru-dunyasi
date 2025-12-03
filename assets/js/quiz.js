let currentQuestionIndex = 0;
let questions = [];
let userAnswers = []; // YENİ: Cevapları burada biriktireceğiz
let score = 0;
let savedResultId = null; // Kayıt sonrası ID'yi tutmak için

const urlParams = new URLSearchParams(window.location.search);
const quizId = urlParams.get('id');

document.addEventListener('DOMContentLoaded', () => {
    if(quizId) {
        fetchQuestions(quizId);
    } else {
        showError("Geçersiz Test ID");
    }
});

async function fetchQuestions(id) {
    try {
        const response = await fetch(`api/get_questions.php?id=${id}&t=${new Date().getTime()}`);
        const data = await response.json();
        
        if (data.error) {
            showError(data.error);
            return;
        }

        if(data.questions && data.questions.length > 0) {
            questions = data.questions;
            
            const titleEl = document.getElementById('quiz-title');
            if(titleEl) titleEl.innerText = data.info.title;
            
            const loadingEl = document.getElementById('loading-screen');
            const contentEl = document.getElementById('quiz-content');
            
            if(loadingEl) loadingEl.classList.add('hidden');
            if(contentEl) {
                contentEl.classList.remove('hidden');
                contentEl.classList.add('fade-in');
            }

            showQuestion();
        } else {
            showError("Bu testte henüz soru bulunmuyor.");
        }
    } catch (error) {
        console.error(error);
        showError("Sunucu ile bağlantı kurulamadı.");
    }
}

function showQuestion() {
    const q = questions[currentQuestionIndex];
    const container = document.getElementById('options-container');
    const questionText = document.getElementById('question-text');
    
    // Progress Bar
    const progressPercent = ((currentQuestionIndex) / questions.length) * 100;
    document.getElementById('progress-bar').style.width = `${progressPercent}%`;
    document.getElementById('progress-text').innerText = `${currentQuestionIndex + 1}/${questions.length}`;

    // Soru Metni
    questionText.style.opacity = 0;
    setTimeout(() => {
        questionText.innerText = q.question_text;
        questionText.style.opacity = 1;
        questionText.style.transition = "opacity 0.5s";
    }, 200);

    container.innerHTML = '';
    const options = ['a', 'b', 'c', 'd', 'e'];
    
    options.forEach((opt, index) => {
        if(q[`option_${opt}`]) {
            const btn = document.createElement('button');
            btn.className = `
                w-full text-left p-5 rounded-2xl border-2 border-gray-100 bg-white 
                hover:border-indigo-500 hover:shadow-lg hover:shadow-indigo-100 
                transition-all duration-200 font-semibold text-lg text-gray-700
                flex items-center group relative overflow-hidden transform hover:-translate-y-1
            `;
            btn.style.animation = `fadeIn 0.5s ease-out forwards ${index * 0.1}s`;
            btn.style.opacity = '0'; 
            
            // Soru ID'sini de fonksiyona gönderiyoruz
            btn.onclick = () => checkAnswer(opt, q.correct_answer, btn, q.id);
            
            btn.innerHTML = `
                <span class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 font-bold flex items-center justify-center mr-5 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm uppercase text-sm border border-gray-200 group-hover:border-indigo-600">
                    ${opt}
                </span>
                <span class="relative z-10">${q[`option_${opt}`]}</span>
            `;
            container.appendChild(btn);
        }
    });
}

function checkAnswer(selected, correct, btnElement, qId) {
    const buttons = document.getElementById('options-container').querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);

    const isCorrect = (selected === correct);
    
    // YENİ: Cevabı listeye ekle
    userAnswers.push({
        question_id: qId,
        user_choice: selected,
        is_correct: isCorrect
    });

    const iconSpan = btnElement.querySelector('span:first-child');

    if(isCorrect) {
        score++;
        btnElement.className = "w-full text-left p-5 rounded-2xl border-2 border-green-500 bg-green-50 text-green-800 font-bold flex items-center shadow-md transform scale-[1.02] transition-all";
        iconSpan.className = "w-10 h-10 rounded-xl bg-green-500 text-white font-bold flex items-center justify-center mr-5 shadow-sm uppercase text-sm";
        iconSpan.innerHTML = '<i class="fa-solid fa-check"></i>';
        
        if(typeof confetti === 'function') {
            confetti({ particleCount: 50, spread: 60, origin: { y: 0.7 }, colors: ['#22c55e', '#ffffff'] });
        }
    } else {
        btnElement.className = "w-full text-left p-5 rounded-2xl border-2 border-red-500 bg-red-50 text-red-800 font-bold flex items-center shadow-md opacity-80";
        iconSpan.className = "w-10 h-10 rounded-xl bg-red-500 text-white font-bold flex items-center justify-center mr-5 shadow-sm uppercase text-sm";
        iconSpan.innerHTML = '<i class="fa-solid fa-xmark"></i>';

        // Doğruyu göster
        buttons.forEach(btn => {
            const letter = btn.querySelector('span:first-child').innerText.trim().toLowerCase();
            if(letter === correct) {
                 btn.classList.add('border-green-500', 'bg-green-50');
                 btn.querySelector('span:first-child').classList.add('bg-green-500', 'text-white', 'border-green-500');
                 btn.querySelector('span:first-child').classList.remove('bg-gray-100', 'text-gray-500');
            }
        });
    }

    if (currentQuestionIndex === questions.length - 1) {
        document.getElementById('progress-bar').style.width = '100%';
    }

    setTimeout(() => {
        currentQuestionIndex++;
        if(currentQuestionIndex < questions.length) {
            showQuestion();
        } else {
            showResult();
        }
    }, 1500);
}

function showResult() {
    const container = document.getElementById('quiz-container');
    const percentage = Math.round((score / questions.length) * 100);
    
    let title, message, colorClass, icon;

    if(percentage >= 80) {
        title = "Muhteşem! 🎉";
        message = "Konuya tamamen hakimsin.";
        colorClass = "text-green-600";
        icon = "fa-trophy";
        if(typeof confetti === 'function') confetti({ particleCount: 150, spread: 100, origin: { y: 0.6 } });
    } else if(percentage >= 50) {
        title = "Güzel İş! 👍";
        message = "İyi gidiyorsun ama biraz tekrar iyi olabilir.";
        colorClass = "text-yellow-500";
        icon = "fa-star";
    } else {
        title = "Pes Etmek Yok! 💪";
        message = "Hatalar öğrenmenin bir parçasıdır.";
        colorClass = "text-orange-500";
        icon = "fa-book-open";
    }

    container.innerHTML = `
        <div class="text-center py-10 fade-in">
            <div class="w-24 h-24 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
                <i class="fa-solid ${icon} text-5xl ${colorClass}"></i>
            </div>
            <h2 class="text-4xl font-extrabold text-gray-900 mb-2">${title}</h2>
            <p class="text-gray-500 mb-8 font-medium">${message}</p>
            
            <div class="flex justify-center items-end gap-2 mb-8">
                <span class="text-6xl font-black ${colorClass}">${percentage}</span>
                <span class="text-2xl font-bold text-gray-400 mb-2">/100</span>
            </div>
            
            <div id="save-message" class="text-sm font-bold mb-6 h-6"></div>

            <div class="inline-flex gap-4 mb-10">
                <div class="bg-green-50 border border-green-100 px-6 py-3 rounded-2xl">
                    <div class="text-xs text-green-600 font-bold uppercase">Doğru</div>
                    <div class="text-2xl font-bold text-green-700">${score}</div>
                </div>
                <div class="bg-red-50 border border-red-100 px-6 py-3 rounded-2xl">
                    <div class="text-xs text-red-600 font-bold uppercase">Yanlış</div>
                    <div class="text-2xl font-bold text-red-700">${questions.length - score}</div>
                </div>
            </div>

            <!-- BUTONLAR -->
            <div class="flex flex-col md:flex-row justify-center gap-4">
                <a href="index.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                    Ana Sayfa
                </a>
                
                <!-- İNCELE BUTONU (Başlangıçta gizli, kayıt bitince açılacak) -->
                <a id="review-btn" href="#" class="hidden bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Cevapları İncele
                </a>

                <button onclick="location.reload()" class="bg-white border-2 border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-50 transition">
                    <i class="fa-solid fa-rotate-right mr-2"></i> Tekrar
                </button>
            </div>
        </div>
    `;

    // Kaydetme işlemini başlat
    saveQuizResult(quizId, score, questions.length);
}

async function saveQuizResult(qId, userScore, totalQ) {
    const msgBox = document.getElementById('save-message');
    if(msgBox) msgBox.innerHTML = '<span class="text-gray-400"><i class="fa-solid fa-spinner fa-spin"></i> Sonuç kaydediliyor...</span>';

    try {
        const response = await fetch('api/save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                quiz_id: qId,
                score: userScore,
                total: totalQ,
                answers: userAnswers // YENİ: Cevapları da gönderiyoruz
            })
        });
        const data = await response.json();
        
        if (msgBox) {
            if (data.status === 'success') {
                msgBox.innerHTML = '<span class="text-green-500"><i class="fa-solid fa-check-circle"></i> Sonucunuz kaydedildi!</span>';
                
                // İncele butonunu aktifleştir
                const reviewBtn = document.getElementById('review-btn');
                if(reviewBtn) {
                    reviewBtn.href = `review.php?result_id=${data.result_id}`;
                    reviewBtn.classList.remove('hidden');
                    reviewBtn.classList.add('inline-flex', 'items-center');
                }

            } else if (data.status === 'guest') {
                msgBox.innerHTML = '<a href="login.php" class="text-indigo-500 underline hover:text-indigo-600">Kaydetmek için giriş yapın.</a>';
            } else {
                msgBox.innerHTML = '<span class="text-red-500">Hata: ' + data.message + '</span>';
            }
        }
    } catch (error) {
        console.error("Hata:", error);
        if(msgBox) msgBox.innerHTML = '<span class="text-red-500">Bağlantı hatası.</span>';
    }
}

function showError(msg) {
    const container = document.getElementById('quiz-container');
    if(container) {
        container.innerHTML = `<div class="text-center py-20 text-red-500 font-bold">${msg}</div>`;
    }
}