<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#007bff">
  <title>@yield('title','LyThuyetLaiXe.vn')</title>
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  @stack('styles')
</head>
<body>
  <header class="site-header">
    <div class="container nav">
      <a href="{{ route('home') }}" class="logo">LYTHUYETLAIXE.VN</a>
      <nav class="main-nav">
        <ul>
          <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Trang Chủ</a></li>
          <li><a href="{{ route('practice.cauhoi') }}">Ôn Thi</a></li>
          <li><a href="{{ route('simulation') }}">Mô Phỏng</a></li>
          <li><a href="{{ route('bienbao') }}">Biển Báo</a></li>
        </ul>
      </nav>
      <button class="btn-menu" id="btnMenu" aria-controls="menuRight" aria-expanded="false">
        <span class="hamburger"></span>
      </button>
    </div>
  </header>

  {{-- Offcanvas phải (đè lên 1 phần bên phải khi mở) --}}
  <aside id="menuRight" class="offcanvas" aria-hidden="true" role="dialog">
    <header class="nav" style="height:60px">
      <!-- <span class="menu-title">Menu</span>
      <button class="btn-menu" id="btnCloseMenu" aria-label="Đóng menu">✕</button> -->
    </header>
    <nav>
      <a href="{{ route('home') }}">
        <i class="fas fa-home"></i> Trang chủ
      </a>
      <a href="{{ route('practice.cauhoi') }}">
        <i class="fas fa-book"></i> Ôn thi lý thuyết
      </a>
      <a href="{{ route('simulation') }}">
        <i class="fas fa-laptop"></i> Mô phỏng
      </a>
      <a href="{{ route('videothuchanh') }}">
        <i class="fas fa-video"></i> Thực hành lái xe
      </a>
      <a href="{{ route('xemay') }}">
        <i class="fas fa-motorcycle"></i> Ôn tập xe máy
      </a>
      <a href="{{ route('thi.thu') }}">
        <i class="fas fa-clipboard-check"></i> Thi thử
      </a>
      <a href="{{ route('bienbao') }}">
        <i class="fas fa-sign"></i> Biển báo
      </a>
    </nav>
  </aside>
  <div id="scrim" class="scrim" aria-hidden="true"></div>

  <main>
    @yield('content')
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col about-us">
          <h3>LYTHUYETLAIXE.VN</h3>
          <p>Hệ thống ôn thi lý thuyết GPLX uy tín, nơi giúp bạn tự tin vượt qua kỳ thi sát hạch.</p>
        </div>
        <div class="footer-col">
          <h3>Liên Kết Nhanh</h3>
          <ul>
            <li><a href="{{ route('practice.cauhoi') }}">Ôn Thi Lý Thuyết</a></li>
            <li><a href="{{ route('simulation') }}">Mô Phỏng</a></li>
            <li><a href="{{ route('bienbao') }}">Biển Báo</a></li>
          </ul>
        </div>
        <div class="footer-col contact">
          <h3>Liên Hệ</h3>
          <p><i class="fas fa-phone"></i> 0981.6688.75</p>
          <p><i class="fas fa-envelope"></i> contact@lythuyetlaixe.vn</p>
          <p><i class="fas fa-map-marker-alt"></i> Việt Nam</p>
        </div>
      </div>
      <div class="copyright">
        &copy; 2025 LyThuyetLaiXe.vn. Bảo lưu mọi quyền.
      </div>
    </div>
  </footer>

  <script>
    // Set API URL cho chatbox
    (function() {
      const apiUrl = @json(config('services.chat.api_url', 'http://localhost:7070/chat'));
      window.chatApiUrl = apiUrl || 'http://localhost:7070/chat';
      console.log('[Chatbox Config] API URL set to:', window.chatApiUrl);
    })();
  </script>
  <script src="{{ asset('js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
