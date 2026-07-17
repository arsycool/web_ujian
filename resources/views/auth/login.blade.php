@extends('layouts.app')

@section('title', 'Login — TKB CPNS Pranata Komputer')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="noise"></div>

<button class="theme-btn" data-theme-label onclick="toggleTheme()">🌙 Mode Gelap</button>

<div class="login-wrapper">
  <div class="login-card">
    <div class="brand">
      <div class="brand-icon">🖥️</div>
      <div class="brand-text">
        <div class="tagline">TKB CPNS 2025</div>
        <h1>Pranata Komputer</h1>
      </div>
    </div>

    <h2 class="login-title">Selamat Datang</h2>
    <p class="login-sub">Masuk untuk menyimpan progres dan melihat riwayat ujianmu.</p>

    @if ($errors->any())
      <div class="error-msg" style="display:block;">
        {{ $errors->first() }}
      </div>
    @endif
    @if (session('status'))
      <div class="success-msg" style="display:block;">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
      @csrf
      <div class="field">
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" autocomplete="name" required>
      </div>

      <div class="field">
        <label>NIP / Nomor Peserta</label>
        <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Contoh: 199001012020011001" autocomplete="username" required>
        <div class="field-hint">Gunakan NIP atau nomor peserta ujian CPNS Anda</div>
      </div>

      <div class="field">
        <label>PIN / Kata Sandi</label>
        <input type="password" name="pin" placeholder="Minimal 4 karakter" autocomplete="current-password" minlength="4" required>
      </div>

      <button type="submit" class="btn-login">Masuk & Mulai Ujian →</button>
    </form>

    <div class="divider">atau</div>

    <form method="POST" action="{{ route('login.guest') }}">
      @csrf
      <button type="submit" class="btn-guest">🚶 Lanjutkan Sebagai Tamu</button>
    </form>

    <div class="stats-strip">
      <div class="strip-item"><div class="strip-val">120</div><div class="strip-lbl">Total Soal</div></div>
      <div class="strip-item"><div class="strip-val">4</div><div class="strip-lbl">Sesi Ujian</div></div>
      <div class="strip-item"><div class="strip-val">45m</div><div class="strip-lbl">Per Sesi</div></div>
    </div>
  </div>
</div>
@endsection
