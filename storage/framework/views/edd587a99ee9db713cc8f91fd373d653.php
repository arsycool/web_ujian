<?php $__env->startSection('title', 'Dashboard — TKB CPNS Pranata Komputer'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/dashboard.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <div class="topbar">
    <div class="brand">
      <div class="brand-icon">🖥️</div>
      <div>
        <div class="brand-label">TKB CPNS 2025</div>
        <div class="brand-title">Pranata Komputer</div>
      </div>
    </div>
    <div class="topbar-right">
      <button class="btn-sm outline" data-theme-label onclick="toggleTheme()">🌙 Mode Gelap</button>
      <form method="POST" action="<?php echo e(route('logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn-sm danger">↩ Keluar</button>
      </form>
    </div>
  </div>

  <div class="welcome-strip">
    <div class="welcome-left">
      <div class="welcome-greeting">Halo, Peserta!</div>
      <div class="welcome-name"><?php echo e($user->name); ?></div>
      <div class="welcome-nip">NIP: <?php echo e($user->nip); ?></div>
    </div>
    <div class="welcome-avatar"><?php echo e($user->initial()); ?></div>
  </div>

  <?php if(session('status')): ?>
    <div class="all-done-banner"><?php echo e(session('status')); ?></div>
  <?php endif; ?>

  <?php if($allDone): ?>
    <div class="all-done-banner">
      🎉 Luar biasa! Kamu telah menyelesaikan semua <?php echo e($totalSessions); ?> sesi ujian. Gunakan tombol <strong>Ulangi Sesi</strong> di tiap kartu untuk mencoba lagi, atau <strong>Ulangi Semua Sesi</strong> untuk mulai dari awal.
    </div>
  <?php endif; ?>

  <div class="section-label">📊 Rekap Keseluruhan</div>
  <div class="overall-grid">
    <div class="ov-item">
      <div class="ov-val"><?php echo e($doneCount); ?>/<?php echo e($totalSessions); ?></div>
      <div class="ov-lbl">Sesi Selesai</div>
    </div>
    <div class="ov-item">
      <div class="ov-val gold"><?php echo e($doneCount ? $avgScore : '—'); ?></div>
      <div class="ov-lbl">Rata-rata Nilai</div>
    </div>
    <div class="ov-item">
      <div class="ov-val green"><?php echo e($totalBenar); ?></div>
      <div class="ov-lbl">Total Benar</div>
    </div>
    <div class="ov-item">
      <div class="ov-val red"><?php echo e($totalSalah); ?></div>
      <div class="ov-lbl">Total Salah</div>
    </div>
    <div class="ov-item">
      <div class="ov-val"><?php echo e($doneCount ? $akurasi . '%' : '—'); ?></div>
      <div class="ov-lbl">Akurasi</div>
    </div>
  </div>

  <div class="reset-banner">
    <div class="reset-info">
      <strong>Ingin melihat history?</strong> Semua progres, nilai, dan riwayat akan ditampilkan.
    </div>
    <a href="<?php echo e(route('history')); ?>" class="btn-reset btn-reset-link">👀 History</a>
  </div>

  <div class="reset-banner">
    <div class="reset-info">
      <strong>Ingin mengulang semua sesi dari awal?</strong> Semua progres, nilai, dan riwayat akan direset.
    </div>
    <form method="POST" action="<?php echo e(route('dashboard.reset-all')); ?>"
          onsubmit="return confirm('Yakin ingin menghapus semua progres, nilai, dan riwayat? Semua data akan hilang dan ujian dimulai dari Sesi 1.');">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn-reset">🔄 Ulangi Semua Sesi</button>
    </form>
  </div>

  <div class="section-label">📋 Pilih Sesi Ujian</div>
  <div class="sessions-grid">
    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $prevDone = $s['id'] === 0 || $sessions[$s['id'] - 1]['done'];
        $accessible = $s['done'] || $prevDone;
        $cardClass = 'sesi-card';
        if ($s['done']) $cardClass .= ' done';
        elseif (!$accessible) $cardClass .= ' locked-card';
      ?>
      <div class="<?php echo e($cardClass); ?>">
        <div class="sesi-head">
          <div class="sesi-title"><?php echo e($s['title']); ?></div>
          <?php if($s['done']): ?>
            <span class="sesi-badge badge-done">✅ Selesai</span>
          <?php elseif(!$accessible): ?>
            <span class="sesi-badge badge-locked">🔒 Terkunci</span>
          <?php else: ?>
            <span class="sesi-badge badge-new">🆕 Baru</span>
          <?php endif; ?>
        </div>
        <p class="sesi-desc"><?php echo e($s['desc']); ?></p>

        <?php if($s['done']): ?>
          <div class="sesi-score-row">
            <div class="ssr-item"><div class="ssr-val gold"><?php echo e($s['latest_score']); ?></div><div class="ssr-lbl">Nilai Terakhir</div></div>
            <div class="ssr-item"><div class="ssr-val gold"><?php echo e($s['best_score']); ?></div><div class="ssr-lbl">🏆 Terbaik</div></div>
            <div class="ssr-item"><div class="ssr-val g"><?php echo e($s['correct']); ?></div><div class="ssr-lbl">Benar</div></div>
            <div class="ssr-item"><div class="ssr-val r"><?php echo e($s['wrong']); ?></div><div class="ssr-lbl">Salah</div></div>
          </div>
        <?php endif; ?>

        <div class="stopic-wrap" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
          <?php $__currentLoopData = $s['topics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="stopic"><?php echo e($topic); ?></span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="sesi-actions">
          <?php if($s['done']): ?>
            <form method="POST" action="<?php echo e(route('exam.reset', $s['id'])); ?>"
                  onsubmit="return confirm('Ulangi <?php echo e($s['title']); ?> dari awal? Nilai sebelumnya akan diganti.');">
              <?php echo csrf_field(); ?>
              <button type="submit" class="btn-sesi retry">🔄 Ulangi <?php echo e($s['title']); ?></button>
            </form>
          <?php elseif($accessible): ?>
            <a href="<?php echo e(route('exam.show', $s['id'])); ?>" class="btn-sesi start btn-sesi-link">▶ Mulai / Lanjutkan</a>
          <?php else: ?>
            <button type="button" class="btn-sesi" disabled>🔒 Selesaikan sesi sebelumnya</button>
          <?php endif; ?>
          <a href="<?php echo e(route('history', ['session' => $s['id']])); ?>" class="btn-sesi outline btn-sesi-link">📄 Lihat Riwayat Nilai</a>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel_ujiancs\resources\views/dashboard.blade.php ENDPATH**/ ?>