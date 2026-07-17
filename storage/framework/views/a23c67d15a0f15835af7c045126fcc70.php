<?php $__env->startSection('title', $session['sesiLabel'].' — Selesai'); ?>

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

      <div class="result-screen show" style="display:block;">
        <div class="result-header">
          <div class="result-top-row">
            <div>
              <div class="score-display"><?php echo e($attempt->raw_score); ?></div>
              <div class="score-label">Nilai Akhir (dari 100)</div>
            </div>
            <div class="time-used-box">
              <div class="tub-val"><?php echo e(gmdate('i\m s\s', $attempt->duration_seconds)); ?></div>
              <div class="tub-lbl">Waktu Digunakan</div>
            </div>
          </div>
          <div class="result-grade">
            <?php $pct = $attempt->raw_score; ?>
            <?php if($pct >= 85): ?> 🏆 Sangat Memuaskan — Siap CPNS!
            <?php elseif($pct >= 70): ?> ✅ Baik — Tetap Tingkatkan!
            <?php elseif($pct >= 55): ?> ⚠️ Cukup — Perlu Penguatan Materi
            <?php else: ?> 📚 Kurang — Pelajari Kembali Materi
            <?php endif; ?>
          </div>
        </div>
        <div class="result-stats">
          <div class="rs-item"><div class="rs-val" style="color:var(--correct)"><?php echo e($attempt->correct_count); ?></div><div class="rs-lbl">Benar</div></div>
          <div class="rs-item"><div class="rs-val" style="color:var(--wrong)"><?php echo e($attempt->wrong_count); ?></div><div class="rs-lbl">Salah</div></div>
          <div class="rs-item"><div class="rs-val"><?php echo e($attempt->raw_score); ?></div><div class="rs-lbl">Nilai Akhir</div></div>
          <div class="rs-item"><div class="rs-val" style="color:var(--muted)"><?php echo e($attempt->accuracy()); ?>%</div><div class="rs-lbl">Persentase</div></div>
        </div>
        <div class="result-actions">
          <div class="locked-note">🔒 Sesi <?php echo e($session['id'] + 1); ?> telah dikunci dan tidak dapat diulang langsung. Gunakan tombol "Ulangi Sesi" dari Dashboard bila ingin mencoba lagi.</div>
          <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary btn-link">🏠 Dashboard</a>
          <a href="<?php echo e(route('history', ['session' => $session['id']])); ?>" class="btn btn-secondary btn-link">📄 Lihat Riwayat</a>
          <form method="POST" action="<?php echo e(route('exam.reset', $session['id'])); ?>"
                onsubmit="return confirm('Ulangi <?php echo e($session['title']); ?> dari awal? Nilai sebelumnya akan diganti.');">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-secondary">🔄 Ulangi Sesi <?php echo e($session['id'] + 1); ?></button>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel_ujiancs\resources\views/exam/locked.blade.php ENDPATH**/ ?>