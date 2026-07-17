<?php $__env->startSection('title', $session['sesiLabel'].' — Pranata Komputer'); ?>

<?php $__env->startSection('content'); ?>
<div class="wrapper">
  <div class="app-grid">
    <aside class="sidebar">

      <div class="user-badge">
        <div class="user-avatar"><?php echo e($user->initial()); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo e($user->name); ?></div>
          <div class="user-nip"><?php echo e($user->nip); ?></div>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit" class="logout-btn" title="Keluar">↩</button>
        </form>
      </div>

      <a href="<?php echo e(route('dashboard')); ?>" style="text-decoration:none;">
        <button class="btn btn-secondary" style="width:100%;font-size:13px;padding:10px 14px;justify-content:center;">
          ← Kembali ke Dashboard
        </button>
      </a>

      <div class="session-selector">
        <div class="session-label">Pilih Sesi</div>
        <div class="session-btns">
          <?php $__currentLoopData = $sessionStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sid => $done): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('exam.show', $sid)); ?>" style="text-decoration:none;">
              <button type="button" class="session-btn <?php echo e($sid === $session['id'] ? 'active' : ''); ?> <?php echo e($done && $sid !== $session['id'] ? 'done' : ''); ?>">
                <?php if($done && $sid !== $session['id']): ?> 🔒 <?php endif; ?> Sesi <?php echo e($sid + 1); ?>

              </button>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <div class="timer-card" id="timer-card">
        <div class="timer-label">⏱ Waktu Tersisa</div>
        <div class="timer-display" id="timer-display">45:00</div>
        <div class="timer-bar-wrap"><div class="timer-bar" id="timer-bar" style="width:100%"></div></div>
        <div class="timer-hint" id="timer-hint">Timer mulai saat ujian dimulai</div>
      </div>

      <div class="stats-bar">
        <div class="stat"><div class="stat-val" id="s-current"><?php echo e(($attempt->current_index ?? 0) + 1); ?></div><div class="stat-label">Soal ke</div></div>
        <div class="stat"><div class="stat-val green" id="s-correct"><?php echo e($attempt->correct_count); ?></div><div class="stat-label">Benar</div></div>
        <div class="stat"><div class="stat-val" id="s-score-raw">0</div><div class="stat-label">Nilai</div></div>
        <div class="stat"><div class="stat-val red" id="s-wrong"><?php echo e($attempt->wrong_count); ?></div><div class="stat-label">Salah</div></div>
        <div class="stat"><div class="stat-val muted" id="s-remain"><?php echo e(count($session['questions'])); ?></div><div class="stat-label">Sisa</div></div>
      </div>

      <div class="progress-wrap"><div class="progress-bar" id="progress" style="width:0%"></div></div>

      <div style="text-align:center">
        <button class="btn btn-primary" id="start-button-aside" onclick="ExamApp.start()" style="font-size:14px;padding:12px 22px;">Mulai / Lanjutkan</button>
      </div>

      <div style="text-align:center; margin-top:8px; font-size:13px; color:var(--muted)">Progress otomatis disimpan</div>
    </aside>

    <main class="main">

      <div class="header">
        <div class="header-top">
          <div class="logo-box">🖥️</div>
          <div class="header-labels">
            <div class="label-top"><?php echo e($session['sesiLabel']); ?></div>
            <h1>Pranata Komputer</h1>
          </div>
          <button class="theme-switch" data-theme-label onclick="toggleTheme()">🌙 Mode Gelap</button>
        </div>
        <p class="header-desc"><?php echo e($session['headerDesc']); ?></p>
      </div>

      <div id="intro">
        <div class="intro-card">
          <div class="intro-hero">
            <div class="intro-icon-box">📋</div>
            <div class="intro-txt">
              <h2><?php echo e($session['title']); ?></h2>
              <p><?php echo e($session['desc']); ?></p>
            </div>
          </div>
          <div class="info-row">
            <div class="info-item"><div class="info-val"><?php echo e(count($session['questions'])); ?></div><div class="info-lbl">Total Soal</div></div>
            <div class="info-item"><div class="info-val"><?php echo e(count($session['topicsList'])); ?></div><div class="info-lbl">Topik Materi</div></div>
            <div class="info-item"><div class="info-val">100</div><div class="info-lbl">Skor Maks</div></div>
          </div>
          <div class="timer-info-row">
            <span class="tir-item">⏱ Batas Waktu: <strong>45 menit</strong></span>
            <span class="tir-sep">•</span>
            <span class="tir-item">📝 Nilai per soal: <strong>~<?php echo e(round(100 / max(count($session['questions']),1), 2)); ?></strong></span>
            <span class="tir-sep">•</span>
            <span class="tir-item">🎯 Nilai maks: <strong>100</strong></span>
          </div>
          <div class="topics">
            <?php $__currentLoopData = $session['topicsList']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span class="stopic"><?php echo e($t); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php if(($attempt->current_index ?? 0) > 0 || count($attempt->answers ?? []) > 0): ?>
            <div style="text-align:center; margin-bottom:16px; color: var(--accent2); font-size:14px;">Progres sebelumnya ditemukan. Kamu dapat melanjutkan ujian di mana terakhir berhenti.</div>
          <?php endif; ?>
          <div style="text-align:center">
            <button class="btn btn-primary" id="start-button" onclick="ExamApp.start()" style="font-size:15px;padding:15px 48px;">Mulai Ujian →</button>
          </div>
        </div>
      </div>

      <div id="quiz" style="display:none;">
        <div id="question-area"></div>
        <div class="actions">
          <button type="button" class="btn btn-warning" id="btn-check" onclick="ExamApp.checkAnswer()" disabled>⚠️ Koreksi Jawaban</button>
          <button type="button" class="btn btn-secondary" id="btn-exp" onclick="ExamApp.toggleExp()" style="display:none">📖 Pembahasan</button>
          <button type="button" class="btn btn-secondary" id="btn-finish" onclick="ExamApp.finishQuiz()">🛑 Selesai</button>
          <button type="button" class="btn btn-primary" id="btn-next" onclick="ExamApp.nextQuestion()" disabled>Lanjut →</button>
        </div>
      </div>

      <div class="result-screen" id="result">
        <div class="result-header">
          <div class="result-top-row">
            <div>
              <div class="score-display" id="score-pct">0</div>
              <div class="score-label">Nilai Akhirmu (dari 100)</div>
            </div>
            <div class="time-used-box">
              <div class="tub-val" id="time-used-val">—</div>
              <div class="tub-lbl">Waktu Digunakan</div>
            </div>
          </div>
          <div class="result-grade" id="result-grade"></div>
        </div>
        <div class="result-stats">
          <div class="rs-item"><div class="rs-val" style="color:var(--correct)" id="r-correct">0</div><div class="rs-lbl">Benar</div></div>
          <div class="rs-item"><div class="rs-val" style="color:var(--wrong)" id="r-wrong">0</div><div class="rs-lbl">Salah</div></div>
          <div class="rs-item"><div class="rs-val" id="r-score">0</div><div class="rs-lbl">Nilai Akhir</div></div>
          <div class="rs-item"><div class="rs-val" id="r-pct" style="color:var(--muted)">0%</div><div class="rs-lbl">Persentase</div></div>
        </div>
        <div class="result-actions" id="result-actions">
          <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary btn-link">🏠 Dashboard</a>
          <button type="button" class="btn btn-secondary" onclick="ExamApp.scrollToReview()">📋 Lihat Review</button>
        </div>
        <div class="review-section" id="review-section">
          <div class="review-header">▸ Rangkuman Seluruh Jawaban</div>
          <div id="review-list"></div>
        </div>
      </div>

    </main>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
  window.EXAM_DATA = {
    sessionId: <?php echo e($session['id']); ?>,
    questions: <?php echo json_encode($questionsForJs, 15, 512) ?>,
    totalQuestions: <?php echo e(count($session['questions'])); ?>,
    durationSeconds: <?php echo e($durationSeconds); ?>,
    attempt: {
      currentIndex: <?php echo e($attempt->current_index ?? 0); ?>,
      answers: <?php echo json_encode($attempt->answers ?? [], 15, 512) ?>,
    },
    routes: {
      answer: <?php echo json_encode(route('exam.answer', $session['id']), 512) ?>,
      progress: <?php echo json_encode(route('exam.progress', $session['id']), 512) ?>,
      finish: <?php echo json_encode(route('exam.finish', $session['id']), 512) ?>,
    },
    csrfToken: <?php echo json_encode(csrf_token(), 15, 512) ?>,
  };
</script>
<script src="<?php echo e(asset('js/exam.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel_ujiancs\resources\views/exam/show.blade.php ENDPATH**/ ?>