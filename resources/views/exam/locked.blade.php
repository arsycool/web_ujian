@extends('layouts.app')

@section('title', $session['sesiLabel'].' — Selesai')

@section('content')
<div class="wrapper">
  <div class="app-grid">
    <aside class="sidebar">
      <div class="user-badge">
        <div class="user-avatar">{{ $user->initial() }}</div>
        <div class="user-info">
          <div class="user-name">{{ $user->name }}</div>
          <div class="user-nip">{{ $user->nip }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="logout-btn" title="Keluar">↩</button>
        </form>
      </div>

      <a href="{{ route('dashboard') }}" style="text-decoration:none;">
        <button class="btn btn-secondary" style="width:100%;font-size:13px;padding:10px 14px;justify-content:center;">
          ← Kembali ke Dashboard
        </button>
      </a>

      <div class="session-selector">
        <div class="session-label">Pilih Sesi</div>
        <div class="session-btns">
          @foreach ($sessionStatus as $sid => $done)
            <a href="{{ route('exam.show', $sid) }}" style="text-decoration:none;">
              <button type="button" class="session-btn {{ $sid === $session['id'] ? 'active' : '' }} {{ $done && $sid !== $session['id'] ? 'done' : '' }}">
                @if ($done && $sid !== $session['id']) 🔒 @endif Sesi {{ $sid + 1 }}
              </button>
            </a>
          @endforeach
        </div>
      </div>
    </aside>

    <main class="main">
      <div class="header">
        <div class="header-top">
          <div class="logo-box">🖥️</div>
          <div class="header-labels">
            <div class="label-top">{{ $session['sesiLabel'] }}</div>
            <h1>Pranata Komputer</h1>
          </div>
          <button class="theme-switch" data-theme-label onclick="toggleTheme()">🌙 Mode Gelap</button>
        </div>
        <p class="header-desc">{{ $session['headerDesc'] }}</p>
      </div>

      <div class="result-screen show" style="display:block;">
        <div class="result-header">
          <div class="result-top-row">
            <div>
              <div class="score-display">{{ $attempt->raw_score }}</div>
              <div class="score-label">Nilai Akhir (dari 100)</div>
            </div>
            <div class="time-used-box">
              <div class="tub-val">{{ gmdate('i\m s\s', $attempt->duration_seconds) }}</div>
              <div class="tub-lbl">Waktu Digunakan</div>
            </div>
          </div>
          <div class="result-grade">
            @php $pct = $attempt->raw_score; @endphp
            @if ($pct >= 85) 🏆 Sangat Memuaskan — Siap CPNS!
            @elseif ($pct >= 70) ✅ Baik — Tetap Tingkatkan!
            @elseif ($pct >= 55) ⚠️ Cukup — Perlu Penguatan Materi
            @else 📚 Kurang — Pelajari Kembali Materi
            @endif
          </div>
        </div>
        <div class="result-stats">
          <div class="rs-item"><div class="rs-val" style="color:var(--correct)">{{ $attempt->correct_count }}</div><div class="rs-lbl">Benar</div></div>
          <div class="rs-item"><div class="rs-val" style="color:var(--wrong)">{{ $attempt->wrong_count }}</div><div class="rs-lbl">Salah</div></div>
          <div class="rs-item"><div class="rs-val">{{ $attempt->raw_score }}</div><div class="rs-lbl">Nilai Akhir</div></div>
          <div class="rs-item"><div class="rs-val" style="color:var(--muted)">{{ $attempt->accuracy() }}%</div><div class="rs-lbl">Persentase</div></div>
        </div>
        <div class="result-actions">
          <div class="locked-note">🔒 Sesi {{ $session['id'] + 1 }} telah dikunci dan tidak dapat diulang langsung. Gunakan tombol "Ulangi Sesi" dari Dashboard bila ingin mencoba lagi.</div>
          <a href="{{ route('dashboard') }}" class="btn btn-primary btn-link">🏠 Dashboard</a>
          <a href="{{ route('history', ['session' => $session['id']]) }}" class="btn btn-secondary btn-link">📄 Lihat Riwayat</a>
          <form method="POST" action="{{ route('exam.reset', $session['id']) }}"
                onsubmit="return confirm('Ulangi {{ $session['title'] }} dari awal? Nilai sebelumnya akan diganti.');">
            @csrf
            <button type="submit" class="btn btn-secondary">🔄 Ulangi Sesi {{ $session['id'] + 1 }}</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
