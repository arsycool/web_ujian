@extends('layouts.app')

@section('title', 'Riwayat Ujian — TKB CPNS Pranata Komputer')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/history.css') }}">
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
      <a href="{{ route('dashboard') }}" class="btn-sm outline btn-link">← Dashboard</a>
    </div>
  </div>

  <div class="page-hero">
    <div class="hero-left">
      <h1>📊 Riwayat Ujian</h1>
      <p>{{ auth()->user()->name }} · NIP: {{ auth()->user()->nip }}</p>
    </div>
    <div class="hero-right">{{ $recap['total_percobaan'] }} <span style="font-size:13px;">percobaan</span></div>
  </div>

  <div class="section-label">📈 Rekap Keseluruhan</div>
  <div class="summary-grid">
    <div class="sum-item"><div class="sum-val">{{ $recap['total_percobaan'] ?: '—' }}</div><div class="sum-lbl">Total Percobaan</div></div>
    <div class="sum-item"><div class="sum-val gold">{{ $recap['nilai_terbaik'] ?? '—' }}</div><div class="sum-lbl">Nilai Terbaik</div></div>
    <div class="sum-item"><div class="sum-val">{{ $recap['rata_rata'] ?? '—' }}</div><div class="sum-lbl">Rata-rata</div></div>
    <div class="sum-item"><div class="sum-val green">{{ $recap['total_benar'] ?: '—' }}</div><div class="sum-lbl">Total Benar</div></div>
    <div class="sum-item"><div class="sum-val red">{{ $recap['total_salah'] ?: '—' }}</div><div class="sum-lbl">Total Salah</div></div>
  </div>

  <div class="section-label">🎯 Progress Per Sesi</div>
  <div class="session-progress-grid">
    @foreach ($perSesi as $sp)
      <div class="sp-card">
        <div class="sp-head">
          <span class="sp-title">Sesi {{ $sp['session_id'] + 1 }}</span>
          <span class="sp-badge {{ $sp['selesai'] ? 'done' : 'pending' }}">{{ $sp['selesai'] ? '✅ Selesai' : '⏳ Belum' }}</span>
        </div>
        <div class="sp-stats">
          <div><span class="sp-val gold">{{ $sp['terbaik'] ?? '—' }}</span><span class="sp-lbl">Terbaik</span></div>
          <div><span class="sp-val">{{ $sp['terakhir'] ?? '—' }}</span><span class="sp-lbl">Terakhir</span></div>
          <div><span class="sp-val green">{{ $sp['jumlah_percobaan'] }}</span><span class="sp-lbl">Percobaan</span></div>
        </div>
        @if (!$sp['selesai'])
          <div class="sp-empty">Belum ada percobaan</div>
        @endif
      </div>
    @endforeach
  </div>

  <div class="section-label">📋 Daftar Riwayat</div>
  <div class="filter-bar">
    <span class="filter-label">Sesi:</span>
    <a href="{{ route('history', ['sort' => $sort]) }}" class="filter-btn btn-link {{ $sessionFilter === null || $sessionFilter === '' ? 'active' : '' }}">Semua</a>
    @foreach ([0,1,2,3] as $sid)
      <a href="{{ route('history', ['session' => $sid, 'sort' => $sort]) }}" class="filter-btn btn-link {{ (string) $sessionFilter === (string) $sid ? 'active' : '' }}">Sesi {{ $sid + 1 }}</a>
    @endforeach
    <div class="filter-sep"></div>
    <span class="filter-label">Urutkan:</span>
    <form method="GET" action="{{ route('history') }}" onchange="this.submit()" style="display:inline;">
      @if ($sessionFilter !== null && $sessionFilter !== '')
        <input type="hidden" name="session" value="{{ $sessionFilter }}">
      @endif
      <select class="sort-select" name="sort">
        <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
        <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Terlama</option>
      </select>
    </form>
    <div class="filter-sep"></div>
    <a href="{{ route('history.export-csv') }}" class="filter-btn btn-link" style="margin-left:auto;">⬇ Export CSV</a>
  </div>

  <div class="history-list">
    @forelse ($attempts as $a)
      <div class="attempt-row">
        <span class="attempt-num">Sesi {{ $a->session_id + 1 }}</span>
        <span class="attempt-score" style="color: {{ $a->raw_score >= 70 ? 'var(--correct)' : ($a->raw_score >= 55 ? '#e6a23c' : 'var(--wrong)') }}">{{ $a->raw_score }}</span>
        <div class="attempt-detail">
          <div class="attempt-stats">
            <span class="attempt-stat ok">✓ {{ $a->correct_count }} benar</span>
            <span class="attempt-stat bad">✗ {{ $a->wrong_count }} salah</span>
            <span class="attempt-stat">⏱ {{ gmdate('i\m s\s', $a->duration_seconds) }}</span>
          </div>
          <div class="attempt-bar-wrap">
            <div class="attempt-bar" style="width: {{ $a->raw_score }}%; background: {{ $a->raw_score >= 70 ? 'var(--correct)' : ($a->raw_score >= 55 ? '#e6a23c' : 'var(--wrong)') }};"></div>
          </div>
        </div>
        <span class="attempt-date">{{ $a->finished_at?->translatedFormat('d F Y, H:i') }}</span>
      </div>
    @empty
      <div style="text-align:center; padding: 60px 20px;">
        <div style="font-size:56px; margin-bottom:12px;">📁</div>
        <h3>Belum Ada Riwayat</h3>
        <p style="color:var(--muted); margin: 8px 0 20px;">Kamu belum mengerjakan ujian apapun. Mulai sekarang!</p>
        <a href="{{ route('dashboard') }}" class="btn-sm outline btn-link">🚀 Mulai Ujian Sekarang</a>
      </div>
    @endforelse
  </div>

</div>
@endsection
