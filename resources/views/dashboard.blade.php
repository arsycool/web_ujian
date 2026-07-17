@extends('layouts.app')

@section('title', 'Dashboard — TKB CPNS Pranata Komputer')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
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
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-sm danger">↩ Keluar</button>
      </form>
    </div>
  </div>

  <div class="welcome-strip">
    <div class="welcome-left">
      <div class="welcome-greeting">Halo, Peserta!</div>
      <div class="welcome-name">{{ $user->name }}</div>
      <div class="welcome-nip">NIP: {{ $user->nip }}</div>
    </div>
    <div class="welcome-avatar">{{ $user->initial() }}</div>
  </div>

  @if (session('status'))
    <div class="all-done-banner">{{ session('status') }}</div>
  @endif

  @if ($allDone)
    <div class="all-done-banner">
      🎉 Luar biasa! Kamu telah menyelesaikan semua {{ $totalSessions }} sesi ujian. Gunakan tombol <strong>Ulangi Sesi</strong> di tiap kartu untuk mencoba lagi, atau <strong>Ulangi Semua Sesi</strong> untuk mulai dari awal.
    </div>
  @endif

  <div class="section-label">📊 Rekap Keseluruhan</div>
  <div class="overall-grid">
    <div class="ov-item">
      <div class="ov-val">{{ $doneCount }}/{{ $totalSessions }}</div>
      <div class="ov-lbl">Sesi Selesai</div>
    </div>
    <div class="ov-item">
      <div class="ov-val gold">{{ $doneCount ? $avgScore : '—' }}</div>
      <div class="ov-lbl">Rata-rata Nilai</div>
    </div>
    <div class="ov-item">
      <div class="ov-val green">{{ $totalBenar }}</div>
      <div class="ov-lbl">Total Benar</div>
    </div>
    <div class="ov-item">
      <div class="ov-val red">{{ $totalSalah }}</div>
      <div class="ov-lbl">Total Salah</div>
    </div>
    <div class="ov-item">
      <div class="ov-val">{{ $doneCount ? $akurasi . '%' : '—' }}</div>
      <div class="ov-lbl">Akurasi</div>
    </div>
  </div>

  <div class="reset-banner">
    <div class="reset-info">
      <strong>Ingin melihat history?</strong> Semua progres, nilai, dan riwayat akan ditampilkan.
    </div>
    <a href="{{ route('history') }}" class="btn-reset btn-reset-link">👀 History</a>
  </div>

  <div class="reset-banner">
    <div class="reset-info">
      <strong>Ingin mengulang semua sesi dari awal?</strong> Semua progres, nilai, dan riwayat akan direset.
    </div>
    <form method="POST" action="{{ route('dashboard.reset-all') }}"
          onsubmit="return confirm('Yakin ingin menghapus semua progres, nilai, dan riwayat? Semua data akan hilang dan ujian dimulai dari Sesi 1.');">
      @csrf
      <button type="submit" class="btn-reset">🔄 Ulangi Semua Sesi</button>
    </form>
  </div>

  <div class="section-label">📋 Pilih Sesi Ujian</div>
  <div class="sessions-grid">
    @foreach ($sessions as $s)
      @php
        $prevDone = $s['id'] === 0 || $sessions[$s['id'] - 1]['done'];
        $accessible = $s['done'] || $prevDone;
        $cardClass = 'sesi-card';
        if ($s['done']) $cardClass .= ' done';
        elseif (!$accessible) $cardClass .= ' locked-card';
      @endphp
      <div class="{{ $cardClass }}">
        <div class="sesi-head">
          <div class="sesi-title">{{ $s['title'] }}</div>
          @if ($s['done'])
            <span class="sesi-badge badge-done">✅ Selesai</span>
          @elseif (!$accessible)
            <span class="sesi-badge badge-locked">🔒 Terkunci</span>
          @else
            <span class="sesi-badge badge-new">🆕 Baru</span>
          @endif
        </div>
        <p class="sesi-desc">{{ $s['desc'] }}</p>

        @if ($s['done'])
          <div class="sesi-score-row">
            <div class="ssr-item"><div class="ssr-val gold">{{ $s['latest_score'] }}</div><div class="ssr-lbl">Nilai Terakhir</div></div>
            <div class="ssr-item"><div class="ssr-val gold">{{ $s['best_score'] }}</div><div class="ssr-lbl">🏆 Terbaik</div></div>
            <div class="ssr-item"><div class="ssr-val g">{{ $s['correct'] }}</div><div class="ssr-lbl">Benar</div></div>
            <div class="ssr-item"><div class="ssr-val r">{{ $s['wrong'] }}</div><div class="ssr-lbl">Salah</div></div>
          </div>
        @endif

        <div class="stopic-wrap" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
          @foreach ($s['topics'] as $topic)
            <span class="stopic">{{ $topic }}</span>
          @endforeach
        </div>

        <div class="sesi-actions">
          @if ($s['done'])
            <form method="POST" action="{{ route('exam.reset', $s['id']) }}"
                  onsubmit="return confirm('Ulangi {{ $s['title'] }} dari awal? Nilai sebelumnya akan diganti.');">
              @csrf
              <button type="submit" class="btn-sesi retry">🔄 Ulangi {{ $s['title'] }}</button>
            </form>
          @elseif ($accessible)
            <a href="{{ route('exam.show', $s['id']) }}" class="btn-sesi start btn-sesi-link">▶ Mulai / Lanjutkan</a>
          @else
            <button type="button" class="btn-sesi" disabled>🔒 Selesaikan sesi sebelumnya</button>
          @endif
          <a href="{{ route('history', ['session' => $s['id']]) }}" class="btn-sesi outline btn-sesi-link">📄 Lihat Riwayat Nilai</a>
        </div>
      </div>
    @endforeach
  </div>

</div>
@endsection
