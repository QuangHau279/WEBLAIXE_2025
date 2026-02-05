@extends('layouts.app')
@section('title','Học lý thuyết 600 câu GPLX')

@section('content')
  {{-- HERO SLIDESHOW SECTION - Clean Banner --}}
  <section class="hero-slideshow hero-clean">
    {{-- Slides --}}
    <div class="slideshow-slides">
      <div class="slide active" style="background-image:url('{{ asset('images/hinhanh/banner1.png') }}')"></div>
      <div class="slide" style="background-image:url('{{ asset('images/hinhanh/banner2.png') }}')"></div>
      <div class="slide" style="background-image:url('{{ asset('images/hinhanh/banner3.png') }}')"></div>
    </div>
    
    {{-- Light Overlay for visibility --}}
    <div class="hero-overlay hero-overlay-light"></div>
    
    {{-- Navigation Dots --}}
    <div class="slideshow-dots">
      <button class="dot active" data-slide="0" aria-label="Slide 1"></button>
      <button class="dot" data-slide="1" aria-label="Slide 2"></button>
      <button class="dot" data-slide="2" aria-label="Slide 3"></button>
    </div>
    
    {{-- Navigation Arrows --}}
    <button class="slideshow-arrow prev" aria-label="Slide trước"><i class="fas fa-chevron-left"></i></button>
    <button class="slideshow-arrow next" aria-label="Slide sau"><i class="fas fa-chevron-right"></i></button>
  </section>

  {{-- HERO CARD - Floating between banner and trust bar --}}
  <section class="hero-card-section">
    <div class="container">
      <div class="hero-card-wrapper">
        {{-- Left Column: Main CTA --}}
        <div class="hero-card-left">
          <h1 class="typing-effect" data-texts='["ĐẬU 100% NGAY LẦN ĐẦU", "HỌC LÝ THUYẾT DỄ DÀNG", "TỰ TIN VƯỢT QUA KỲ THI"]'></h1>
          <p class="hero-subtitle">Hệ thống ôn thi lý thuyết GPLX hàng đầu với bộ đề 600 câu cập nhật theo luật mới và 100% nội dung chuẩn.</p>
          
          <!-- <p class="hero-desc">Đừng để việc học lái xe làm bạn lo lắng. Đăng ký ngay để được xếp lịch học sớm nhất và nhận mẹo thi bao đậu.</p> -->
          
          <a href="{{ route('practice.cauhoi') }}" class="btn btn-cta">BẮT ĐẦU ÔN TẬP NGAY</a>
          <!-- <span class="social-proof">Đã giúp hơn 5000+ học viên vượt qua kỳ thi thành công.</span> -->
          
          {{-- Contact Info --}}
          <div class="hero-contact">
            <div class="contact-item">
              <i class="fas fa-phone-alt"></i>
              <div>
                <span class="label">Gọi điện ngay</span>
                <strong>0981686875</strong>
              </div>
            </div>
            <div class="contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <div>
                <span class="label">Vị Trí</span>
                <strong>Thới Hòa, TP. Hồ Chí Minh</strong>
              </div>
            </div>
          </div>
        </div>
        
        {{-- Right Column: Benefits --}}
        <div class="hero-card-right">
          <ul class="hero-benefits">
            <li>
              <i class="fas fa-check-circle"></i>
              <span>Cam kết phí trọn gói, không phát sinh.</span>
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              <span>Giáo viên giàu kinh nghiệm, nhiệt tình, không vội vĩnh.</span>
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              <span>Được thi thử trên xe chip chấm điểm tự động.</span>
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              <span>Hỗ trợ bổ túc tay lái sau khi có bằng.</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- CÁC KHÓA HỌC --}}
  <section class="courses-section">
    <div class="container">
      <h2>CÁC KHÓA HỌC NỔI BẬT</h2>
      <div class="course-grid">
        <div class="course-card">
          <i class="fas fa-car"></i>
          <h3>Ôn Thi Lý Thuyết GPLX</h3>
          <p>Toàn diện 600 câu hỏi từ cơ bản đến nâng cao. Cập nhật theo luật mới nhất.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('practice.cauhoi') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
        <div class="course-card popular">
          <i class="fas fa-laptop"></i>
          <h3>Ôn Thi Mô Phỏng</h3>
          <p>120 tình huống mô phỏng thực tế, tập trung vào kỹ năng thực hành và an toàn.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('simulation') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
        <div class="course-card">
          <i class="fas fa-motorcycle"></i>
          <h3>Ôn Tập Xe Máy (A1)</h3>
          <p>250 câu hỏi chuyên biệt cho bằng lái xe máy, giúp bạn tự tin thi đậu.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('xemay') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
        <div class="course-card">
          <i class="fas fa-clipboard-check"></i>
          <h3>Thi Thử Trực Tuyến</h3>
          <p>5 bộ đề thi thử chuẩn, giúp bạn làm quen với format thi thật.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('thi.thu') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
        <div class="course-card">
          <i class="fas fa-road"></i>
          <h3>Các Tình Huống Thực Tế</h3>
          <p>Video hướng dẫn các tình huống thực tế khi lái xe trên đường.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('videothuchanh') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
        <div class="course-card">
          <i class="fas fa-sign"></i>
          <h3>Biển Báo Giao Thông</h3>
          <p>Hệ thống biển báo đầy đủ với hình ảnh minh họa và giải thích chi tiết.</p>
          <span class="price">Miễn phí</span>
          <a href="{{ route('bienbao') }}" class="btn btn-secondary">Xem Chi Tiết</a>
        </div>
      </div>
    </div>
  </section>

  {{-- CÁC KHÓA HỌC THEO HẠNG --}}
  <section class="courses-section" style="background-color: #fff; padding: 60px 0;">
    <div class="container">
      <h2 style="margin-bottom: 40px;">ĐĂNG KÝ KHÓA HỌC</h2>
      <div class="course-grid" style="grid-template-columns: repeat(3, 1fr); max-width: 1000px; margin: 0 auto;">
        <div class="course-card">
          <i class="fas fa-car-side" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
          <h3>Hạng B, B1</h3>
          <p>Khóa học lái xe ô tô hạng B và B1. Toàn diện từ lý thuyết đến thực hành.</p>
          <span class="price">Chỉ từ 18.645.000 VNĐ</span>
          <a href="#dang-ky-form" class="btn btn-secondary" data-license="B">Đăng Ký Ngay</a>
        </div>
        <div class="course-card popular">
          <i class="fas fa-truck" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
          <h3>Hạng C1</h3>
          <p>Nâng hạng lên C1 để lái xe tải, xe khách. Phù hợp cho mục đích kinh doanh.</p>
          <span class="price">Chỉ từ 20.645.000 VNĐ</span>
          <a href="#dang-ky-form" class="btn btn-secondary" data-license="C1">Đăng Ký Ngay</a>
        </div>
        <div class="course-card">
          <i class="fas fa-motorcycle" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
          <h3>Hạng A, A1</h3>
          <p>Khóa học lái xe máy hạng A và A1. Cấp tốc, tập trung vào kỹ năng thực hành.</p>
          <span class="price">Chỉ từ 700.000 VNĐ</span>
          <a href="#dang-ky-form" class="btn btn-secondary" data-license="A1">Đăng Ký Ngay</a>
        </div>
      </div>
    </div>
  </section>

  {{-- CTA BANNER --}}
  <section class="cta-banner">
    <div class="container">
      <h2>Sẵn Sàng Bắt Đầu Ôn Tập Ngay Hôm Nay?</h2>
      <p>Đăng ký để nhận thông báo về các cập nhật mới và tài liệu học tập miễn phí.</p>
      <a href="{{ route('practice.cauhoi') }}" class="btn btn-primary">BẮT ĐẦU ÔN TẬP MIỄN PHÍ</a>
    </div>
  </section>

  {{-- FORM ĐĂNG KÝ --}}
  <section class="lead" id="dang-ky-form" aria-label="Đăng ký tư vấn">
    <div class="container">
      <div class="lead-card reveal">
        <h3>Đăng Ký Nhận Ưu Đãi</h3>
        <form class="form lead-form" id="leadForm">
          @csrf
          <div class="field">
            <label for="name">Tên</label>
            <input id="name" name="name" placeholder="Nguyễn Văn A" required>
          </div>
          <div class="field">
            <label for="phone">SDT</label>
            <input id="phone" name="phone" placeholder="09xx xxx xxx" pattern="[0-9\s\+]{8,}" required>
          </div>
          <div class="field full">
            <label for="license">Hạng</label>
            <select id="license" name="license" required>
              <option value="" disabled selected>Chọn</option>
              <option>A1</option><option>B1</option><option>B</option><option>C1</option>
            </select>
          </div>
          <div class="field full">
            <button class="btn" id="btnSubmit" type="submit">Đăng ký</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  @push('scripts')
  <script>
    // Xử lý scroll mượt và tự động điền hạng khi click "Đăng Ký Ngay"
    document.addEventListener('DOMContentLoaded', function() {
      const registerButtons = document.querySelectorAll('a[href="#dang-ky-form"]');
      const licenseSelect = document.getElementById('license');
      
      registerButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Lấy hạng từ data-license attribute
          const license = this.getAttribute('data-license');
          
          // Scroll mượt đến form
          const formSection = document.getElementById('dang-ky-form');
          if (formSection) {
            formSection.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start' 
            });
            
            // Tự động điền hạng vào select sau khi scroll
            setTimeout(function() {
              if (license && licenseSelect) {
                licenseSelect.value = license;
                // Trigger change event để các script khác có thể lắng nghe
                licenseSelect.dispatchEvent(new Event('change'));
              }
            }, 500); // Đợi scroll xong rồi mới điền
          }
        });
      });
    });
  </script>
  @endpush
@endsection
