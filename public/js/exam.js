// ============================================================
// ExamApp — logika halaman pengerjaan soal (versi Laravel)
// Data soal & progres awal disuntik dari blade lewat window.EXAM_DATA.
// Kebenaran jawaban divalidasi & disimpan di server (ExamController).
// ============================================================
const ExamApp = (() => {
  const data = window.EXAM_DATA;
  const questions = data.questions;

  let current = data.attempt.currentIndex || 0;
  let answered = data.attempt.answers || []; // [{q, selected, correct}]
  let selectedAnswer = null;
  let expVisible = false;

  let timerInterval = null;
  let remaining = data.durationSeconds;

  function getAnswerRecord(index) {
    return answered.find(a => a.q === index) || null;
  }

  function score() { return answered.filter(a => a.correct).length; }
  function wrong() { return answered.filter(a => !a.correct).length; }

  function calcRawScore() {
    if (!questions.length) return 0;
    return Math.round((score() / questions.length) * 100);
  }

  function updateStats() {
    document.getElementById('s-current').textContent = current + 1;
    document.getElementById('s-correct').textContent = score();
    document.getElementById('s-wrong').textContent = wrong();
    document.getElementById('s-score-raw').textContent = calcRawScore();
    document.getElementById('s-remain').textContent = questions.length - current;
    document.getElementById('progress').style.width = ((current / questions.length) * 100) + '%';
  }

  function start() {
    document.getElementById('intro').style.display = 'none';
    document.getElementById('quiz').style.display = 'block';
    startTimer();
    renderQuestion();
  }

  function startTimer() {
    stopTimer();
    updateTimerDisplay();
    timerInterval = setInterval(() => {
      remaining--;
      updateTimerDisplay();
      if (remaining <= 0) {
        stopTimer();
        finishQuiz(true);
      }
    }, 1000);
  }

  function stopTimer() {
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = null;
  }

  function updateTimerDisplay() {
    const m = Math.floor(Math.max(remaining, 0) / 60);
    const s = Math.max(remaining, 0) % 60;
    const el = document.getElementById('timer-display');
    if (el) el.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    const bar = document.getElementById('timer-bar');
    if (bar) bar.style.width = Math.max((remaining / data.durationSeconds) * 100, 0) + '%';
    const hint = document.getElementById('timer-hint');
    if (hint) hint.textContent = `${m} menit tersisa`;
    const card = document.getElementById('timer-card');
    if (card) {
      card.classList.toggle('warn', remaining <= 300 && remaining > 60);
      card.classList.toggle('danger', remaining <= 60);
    }
  }

  function renderQuestion() {
    expVisible = false;
    const q = questions[current];
    const existing = getAnswerRecord(current);
    updateStats();
    selectedAnswer = null;

    const optHtml = q.opts.map((o, i) => `
      <button type="button" class="opt" id="opt-${i}" onclick="ExamApp.selectAnswer(${i})">
        <span class="opt-key">${String.fromCharCode(65 + i)}</span>
        <span>${o}</span>
      </button>`).join('');

    document.getElementById('question-area').innerHTML = `
      <div class="question-card">
        <div class="q-meta">
          <span class="q-num">SOAL ${current + 1} / ${questions.length}</span>
          <span class="q-topic">${q.topic}</span>
        </div>
        <div class="q-text">${q.text}</div>
        <div class="options">${optHtml}</div>
        <div class="instruction" id="instruction">Pilih satu jawaban, lalu tekan ⚠️ Koreksi Jawaban untuk menilai dan melihat pembahasan.</div>
        <div class="explanation" id="explanation">
          <div class="exp-title">▸ Pembahasan Lengkap</div>
          <div class="exp-text" id="exp-text"></div>
        </div>
      </div>`;

    const btnCheck = document.getElementById('btn-check');
    const btnNext = document.getElementById('btn-next');
    const btnExp = document.getElementById('btn-exp');

    if (existing) {
      document.querySelectorAll('.opt').forEach((button, idx) => {
        button.disabled = true;
        if (idx === existing.selected) {
          button.classList.add('selected', existing.correct ? 'correct' : 'wrong');
        }
      });
      btnCheck.textContent = '✅ Sudah Dijawab';
      btnCheck.disabled = true;
      btnNext.disabled = false;
      btnNext.textContent = current === questions.length - 1 ? 'Selesai →' : 'Lanjut →';
      btnExp.style.display = 'none';
      document.getElementById('instruction').textContent = 'Soal ini sudah dijawab. Lanjutkan ke soal berikutnya.';
    } else {
      btnCheck.textContent = '⚠️ Koreksi Jawaban';
      btnCheck.disabled = true;
      btnNext.disabled = true;
      btnNext.textContent = current === questions.length - 1 ? 'Selesai →' : 'Lanjut →';
      btnExp.style.display = 'none';
    }
  }

  function selectAnswer(idx) {
    if (getAnswerRecord(current)) return;
    selectedAnswer = idx;
    document.querySelectorAll('.opt').forEach(o => o.classList.remove('selected'));
    const btn = document.getElementById('opt-' + idx);
    if (btn) btn.classList.add('selected');
    document.getElementById('btn-check').disabled = false;
    document.getElementById('instruction').textContent = 'Jawaban dipilih. Tekan ⚠️ Koreksi Jawaban untuk melihat benar/salah dan pembahasan.';
  }

  async function checkAnswer() {
    if (selectedAnswer === null) {
      document.getElementById('instruction').textContent = 'Silakan pilih dulu satu jawaban sebelum menekan Koreksi Jawaban.';
      return;
    }

    let result;
    try {
      const res = await fetch(data.routes.answer, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': data.csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ question_index: current, selected: selectedAnswer }),
      });
      result = await res.json();
    } catch (e) {
      alert('Gagal menyimpan jawaban. Periksa koneksi lalu coba lagi.');
      return;
    }

    answered = answered.filter(a => a.q !== current);
    answered.push({ q: current, selected: selectedAnswer, correct: result.correct });

    document.querySelectorAll('.opt').forEach(o => o.disabled = true);
    const selBtn = document.getElementById('opt-' + selectedAnswer);
    if (selBtn) selBtn.classList.add(result.correct ? 'correct' : 'wrong');
    if (!result.correct) {
      const correctBtn = document.getElementById('opt-' + result.correct_index);
      if (correctBtn) correctBtn.classList.add('correct');
    }

    document.getElementById('exp-text').innerHTML = result.explanation;
    document.getElementById('explanation').classList.add('show');
    document.getElementById('btn-exp').style.display = 'inline-flex';
    document.getElementById('btn-exp').textContent = '🙈 Sembunyikan';
    expVisible = true;

    document.getElementById('btn-check').disabled = true;
    document.getElementById('btn-check').textContent = '✅ Koreksi Selesai';
    document.getElementById('btn-next').disabled = false;
    document.getElementById('instruction').textContent = 'Pembahasan sudah tersedia di bawah.';

    updateStats();
  }

  function toggleExp() {
    expVisible = !expVisible;
    document.getElementById('explanation').classList.toggle('show', expVisible);
    document.getElementById('btn-exp').textContent = expVisible ? '🙈 Sembunyikan' : '📖 Pembahasan';
  }

  async function nextQuestion() {
    current++;
    try {
      await fetch(data.routes.progress, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': data.csrfToken,
        },
        body: JSON.stringify({ current_index: current }),
      });
    } catch (e) { /* progres lokal tetap jalan meski gagal sync */ }

    if (current >= questions.length) {
      finishQuiz(false);
    } else {
      renderQuestion();
    }
  }

  async function finishQuiz(auto) {
    const remainingCount = questions.length - answered.length;
    if (!auto && remainingCount > 0) {
      const ok = confirm(`Masih ada ${remainingCount} soal yang belum dijawab. Yakin ingin selesai?`);
      if (!ok) return;
    }

    stopTimer();
    const used = data.durationSeconds - Math.max(remaining, 0);

    let payload = {};
    try {
      const res = await fetch(data.routes.finish, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': data.csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ duration_seconds: used }),
      });
      payload = await res.json();
    } catch (e) { /* tetap tampilkan hasil lokal */ }

    showResult(used);

    if (payload.redirect) {
      // beri jeda sedikit agar user sempat lihat hasil sebelum reload state terkunci
      setTimeout(() => { window.location.href = payload.redirect; }, 4000);
    }
  }

  function showResult(usedSeconds) {
    document.getElementById('quiz').style.display = 'none';
    const res = document.getElementById('result');
    res.classList.add('show');
    res.style.display = 'block';

    const raw = calcRawScore();
    const pct = raw;

    document.getElementById('score-pct').textContent = raw;
    document.getElementById('r-correct').textContent = score();
    document.getElementById('r-wrong').textContent = wrong();
    document.getElementById('r-score').textContent = raw;
    document.getElementById('r-pct').textContent = pct + '%';

    const m = Math.floor(usedSeconds / 60);
    const s = usedSeconds % 60;
    document.getElementById('time-used-val').textContent = `${m}m ${s}s`;

    let grade;
    if (pct >= 85) grade = '🏆 Sangat Memuaskan — Siap CPNS!';
    else if (pct >= 70) grade = '✅ Baik — Tetap Tingkatkan!';
    else if (pct >= 55) grade = '⚠️ Cukup — Perlu Penguatan Materi';
    else grade = '📚 Kurang — Pelajari Kembali Materi';
    document.getElementById('result-grade').textContent = grade;

    document.getElementById('review-list').innerHTML = answered
      .sort((a, b) => a.q - b.q)
      .map(a => `
        <div class="review-item">
          <span class="ri-icon">${a.correct ? '✅' : '❌'}</span>
          <span class="ri-num">Soal ${a.q + 1}</span>
          <span class="ri-topic">${questions[a.q].topic}</span>
          <span class="ri-status ${a.correct ? 'ok' : 'no'}">${a.correct ? 'Benar' : 'Salah'}</span>
        </div>`).join('');
  }

  function scrollToReview() {
    document.getElementById('review-section').scrollIntoView({ behavior: 'smooth' });
  }

  return { start, selectAnswer, checkAnswer, toggleExp, nextQuestion, finishQuiz, scrollToReview };
})();
