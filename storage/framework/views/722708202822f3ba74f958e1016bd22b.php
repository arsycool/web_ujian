<?php $__env->startSection('title', 'Riwayat Ujian — TKB CPNS Pranata Komputer'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/history.css')); ?>">
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
      <a href="<?php echo e(route('dashboard')); ?>" class="btn-sm outline btn-link">← Dashboard</a>
    </div>
  </div>

  <div class="page-hero">
    <div class="hero-left">
      <h1>📊 Riwayat Ujian</h1>
      <p><?php echo e(auth()->user()->name); ?> · NIP: <?php echo e(auth()->user()->nip); ?></p>
    </div>
    <div class="hero-right"><?php echo e($recap['total_percobaan']); ?> <span style="font-size:13px;">percobaan</span></div>
  </div>

  <div class="section-label">📈 Rekap Keseluruhan</div>
  <div class="summary-grid">
    <div class="sum-item"><div class="sum-val"><?php echo e($recap['total_percobaan'] ?: '—'); ?></div><div class="sum-lbl">Total Percobaan</div></div>
    <div class="sum-item"><div class="sum-val gold"><?php echo e($recap['nilai_terbaik'] ?? '—'); ?></div><div class="sum-lbl">Nilai Terbaik</div></div>
    <div class="sum-item"><div class="sum-val"><?php echo e($recap['rata_rata'] ?? '—'); ?></div><div class="sum-lbl">Rata-rata</div></div>
    <div class="sum-item"><div class="sum-val green"><?php echo e($recap['total_benar'] ?: '—'); ?></div><div class="sum-lbl">Total Benar</div></div>
    <div class="sum-item"><div class="sum-val red"><?php echo e($recap['total_salah'] ?: '—'); ?></div><div class="sum-lbl">Total Salah</div></div>
  </div>

  <div class="section-label">🎯 Progress Per Sesi</div>
  <div class="session-progress-grid">
    <?php $__currentLoopData = $perSesi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="sp-card">
        <div class="sp-head">
          <span class="sp-title">Sesi <?php echo e($sp['session_id'] + 1); ?></span>
          <span class="sp-badge <?php echo e($sp['selesai'] ? 'done' : 'pending'); ?>"><?php echo e($sp['selesai'] ? '✅ Selesai' : '⏳ Belum'); ?></span>
        </div>
        <div class="sp-stats">
          <div><span class="sp-val gold"><?php echo e($sp['terbaik'] ?? '—'); ?></span><span class="sp-lbl">Terbaik</span></div>
          <div><span class="sp-val"><?php echo e($sp['terakhir'] ?? '—'); ?></span><span class="sp-lbl">Terakhir</span></div>
          <div><span class="sp-val green"><?php echo e($sp['jumlah_percobaan']); ?></span><span class="sp-lbl">Percobaan</span></div>
        </div>
        <?php if(!$sp['selesai']): ?>
          <div class="sp-empty">Belum ada percobaan</div>
        <?php endif; ?>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <div class="section-label">📋 Daftar Riwayat</div>
  <div class="filter-bar">
    <span class="filter-label">Sesi:</span>
    <a href="<?php echo e(route('history', ['sort' => $sort])); ?>" class="filter-btn btn-link <?php echo e($sessionFilter === null || $sessionFilter === '' ? 'active' : ''); ?>">Semua</a>
    <?php $__currentLoopData = [0,1,2,3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route('history', ['session' => $sid, 'sort' => $sort])); ?>" class="filter-btn btn-link <?php echo e((string) $sessionFilter === (string) $sid ? 'active' : ''); ?>">Sesi <?php echo e($sid + 1); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div class="filter-sep"></div>
    <span class="filter-label">Urutkan:</span>
    <form method="GET" action="<?php echo e(route('history')); ?>" onchange="this.submit()" style="display:inline;">
      <?php if($sessionFilter !== null && $sessionFilter !== ''): ?>
        <input type="hidden" name="session" value="<?php echo e($sessionFilter); ?>">
      <?php endif; ?>
      <select class="sort-select" name="sort">
        <option value="terbaru" <?php echo e($sort === 'terbaru' ? 'selected' : ''); ?>>Terbaru</option>
        <option value="terlama" <?php echo e($sort === 'terlama' ? 'selected' : ''); ?>>Terlama</option>
      </select>
    </form>
    <div class="filter-sep"></div>
    <a href="<?php echo e(route('history.export-csv')); ?>" class="filter-btn btn-link" style="margin-left:auto;">⬇ Export CSV</a>
  </div>

  <div class="history-list">
    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="attempt-row">
        <span class="attempt-num">Sesi <?php echo e($a->session_id + 1); ?></span>
        <span class="attempt-score" style="color: <?php echo e($a->raw_score >= 70 ? 'var(--correct)' : ($a->raw_score >= 55 ? '#e6a23c' : 'var(--wrong)')); ?>"><?php echo e($a->raw_score); ?></span>
        <div class="attempt-detail">
          <div class="attempt-stats">
            <span class="attempt-stat ok">✓ <?php echo e($a->correct_count); ?> benar</span>
            <span class="attempt-stat bad">✗ <?php echo e($a->wrong_count); ?> salah</span>
            <span class="attempt-stat">⏱ <?php echo e(gmdate('i\m s\s', $a->duration_seconds)); ?></span>
          </div>
          <div class="attempt-bar-wrap">
            <div class="attempt-bar" style="width: <?php echo e($a->raw_score); ?>%; background: <?php echo e($a->raw_score >= 70 ? 'var(--correct)' : ($a->raw_score >= 55 ? '#e6a23c' : 'var(--wrong)')); ?>;"></div>
          </div>
        </div>
        <span class="attempt-date"><?php echo e($a->finished_at?->translatedFormat('d F Y, H:i')); ?></span>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div style="text-align:center; padding: 60px 20px;">
        <div style="font-size:56px; margin-bottom:12px;">📁</div>
        <h3>Belum Ada Riwayat</h3>
        <p style="color:var(--muted); margin: 8px 0 20px;">Kamu belum mengerjakan ujian apapun. Mulai sekarang!</p>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn-sm outline btn-link">🚀 Mulai Ujian Sekarang</a>
      </div>
    <?php endif; ?>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel_ujiancs\resources\views/history/index.blade.php ENDPATH**/ ?>