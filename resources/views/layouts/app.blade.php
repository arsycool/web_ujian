<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'TKB CPNS — Pranata Komputer')</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('styles')
</head>
<body>
@yield('content')

<script>
  (function () {
    const t = localStorage.getItem('tkbCpnsTheme');
    if (t === 'dark') document.body.classList.add('dark-mode');
  })();

  function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('tkbCpnsTheme', isDark ? 'dark' : 'light');
    document.querySelectorAll('[data-theme-label]').forEach(btn => {
      btn.textContent = isDark ? '☀️ Mode Terang' : '🌙 Mode Gelap';
    });
  }
</script>
@stack('scripts')
</body>
</html>
