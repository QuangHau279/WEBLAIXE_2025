@extends('layouts.app')

@section('title', 'Biển báo Giao thông')

@push('styles')
<style>
/* ===== Banner Section ===== */
.traffic-signs-banner {
    position: relative;
    width: 100%;
    height: 320px;
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3d7ab5 100%);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.traffic-signs-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 5L55 30L30 55L5 30L30 5z' fill='none' stroke='rgba(255,255,255,0.05)' stroke-width='2'/%3E%3C/svg%3E");
    opacity: 0.5;
}
.traffic-signs-banner .banner-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #fff;
    padding: 20px;
}
.traffic-signs-banner h1 {
    font-size: clamp(28px, 5vw, 48px);
    font-weight: 800;
    margin-bottom: 16px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
}
.traffic-signs-banner p {
    font-size: clamp(14px, 2vw, 18px);
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}
.banner-icons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 24px;
}
.banner-icons img {
    width: 60px;
    height: 60px;
    filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
    animation: float 3s ease-in-out infinite;
}
.banner-icons img:nth-child(2) { animation-delay: 0.5s; }
.banner-icons img:nth-child(3) { animation-delay: 1s; }
.banner-icons img:nth-child(4) { animation-delay: 1.5s; }
.banner-icons img:nth-child(5) { animation-delay: 2s; }

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* ===== Stats Section ===== */
.stats-section {
    background: #fff;
    padding: 40px 0;
    border-bottom: 1px solid #e5e7eb;
}
.stats-grid {
    display: flex;
    justify-content: center;
    gap: 60px;
    flex-wrap: wrap;
}
.stat-item {
    text-align: center;
}
.stat-number {
    font-size: 48px;
    font-weight: 800;
    color: #1e3a5f;
    line-height: 1;
}
.stat-label {
    font-size: 16px;
    color: #6b7280;
    margin-top: 8px;
}

/* ===== Category Cards ===== */
.categories-section {
    padding: 60px 0;
    background: #f8fafc;
}
.categories-section h2 {
    text-align: center;
    font-size: 32px;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 40px;
}
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
.category-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}
.category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}
.category-header {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 20px;
    position: relative;
}
.category-header.cam { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }
.category-header.nguy-hiem { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.category-header.hieu-lenh { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); }
.category-header.chi-dan { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
.category-header.phu { background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%); }

.category-header img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));
    transition: transform 0.3s ease;
}
.category-card:hover .category-header img {
    transform: scale(1.1);
}
.category-body {
    padding: 20px 24px 24px;
}
.category-body h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 8px;
}
.category-body p {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 16px;
}
.category-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #9ca3af;
    background: #f3f4f6;
    padding: 6px 12px;
    border-radius: 20px;
}
.category-count i {
    font-size: 12px;
}

/* ===== CTA Section ===== */
.cta-section {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    padding: 60px 20px;
    text-align: center;
}
.cta-section h2 {
    color: #fff;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 16px;
}
.cta-section p {
    color: rgba(255,255,255,0.8);
    font-size: 16px;
    margin-bottom: 24px;
}
.cta-section .btn {
    background: #fbbf24;
    color: #1e3a5f;
    padding: 14px 32px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}
.cta-section .btn:hover {
    background: #f59e0b;
    transform: translateY(-2px);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .traffic-signs-banner { height: 280px; }
    .banner-icons img { width: 45px; height: 45px; }
    .stats-grid { gap: 30px; }
    .stat-number { font-size: 36px; }
    .category-grid { grid-template-columns: 1fr; max-width: 400px; }
}
</style>
@endpush

@section('content')
{{-- BANNER --}}
<section class="traffic-signs-banner">
    <div class="banner-content">
        <h1>🚦 Biển Báo Giao Thông Việt Nam</h1>
        <p>Hệ thống biển báo đầy đủ theo QCVN 41:2019/BGTVT. Học và ghi nhớ để lái xe an toàn!</p>
        <div class="banner-icons">
            <img src="{{ asset('images/signs/signP102.png') }}" alt="Biển cấm">
            <img src="{{ asset('images/signs/signW201a.png') }}" alt="Biển nguy hiểm">
            <img src="{{ asset('images/signs/signR301a.png') }}" alt="Biển hiệu lệnh">
            <img src="{{ asset('images/signs/signI401.png') }}" alt="Biển chỉ dẫn">
            <img src="{{ asset('images/signs/signS501.png') }}" alt="Biển phụ">
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">{{ \App\Models\TrafficSignCategory::count() }}</div>
                <div class="stat-label">Danh mục</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ \App\Models\TrafficSign::count() }}</div>
                <div class="stat-label">Biển báo</div>
            </div>
        </div>
    </div>
</section>

{{-- CATEGORY CARDS --}}
<section class="categories-section">
    <div class="container">
        <h2>Các Nhóm Biển Báo</h2>
        <div class="category-grid">
            {{-- Biển Cấm --}}
            <a href="{{ route('traffic-signs.show', 'bien-cam') }}" class="category-card">
                <div class="category-header cam">
                    <img src="{{ asset('images/signs/signP101.png') }}" alt="Cấm đi ngược chiều">
                    <img src="{{ asset('images/signs/signP102.png') }}" alt="Cấm đi">
                    <img src="{{ asset('images/signs/signP103a.png') }}" alt="Cấm xe ô tô">
                </div>
                <div class="category-body">
                    <h3>🔴 Biển Báo Cấm</h3>
                    <p>Báo điều cấm hoặc hạn chế mà người tham gia giao thông phải tuân thủ.</p>
                    <span class="category-count"><i class="fas fa-sign"></i> Xem tất cả biển cấm</span>
                </div>
            </a>

            {{-- Biển Nguy hiểm --}}
            <a href="{{ route('traffic-signs.show', 'bien-canh-bao') }}" class="category-card">
                <div class="category-header nguy-hiem">
                    <img src="{{ asset('images/signs/signW201a.png') }}" alt="Chỗ ngoặt nguy hiểm">
                    <img src="{{ asset('images/signs/signW202a.png') }}" alt="Nhiều chỗ ngoặt">
                    <img src="{{ asset('images/signs/signW225.png') }}" alt="Đường trơn">
                </div>
                <div class="category-body">
                    <h3>⚠️ Biển Báo Nguy Hiểm</h3>
                    <p>Cảnh báo các tình huống nguy hiểm trên đường để người lái xe đề phòng.</p>
                    <span class="category-count"><i class="fas fa-sign"></i> Xem tất cả biển nguy hiểm</span>
                </div>
            </a>

            {{-- Biển Hiệu lệnh --}}
            <a href="{{ route('traffic-signs.show', 'bien-hieu-lenh') }}" class="category-card">
                <div class="category-header hieu-lenh">
                    <img src="{{ asset('images/signs/signR301a.png') }}" alt="Đi thẳng">
                    <img src="{{ asset('images/signs/signR303.png') }}" alt="Nơi đỗ xe">
                    <img src="{{ asset('images/signs/signR304.png') }}" alt="Dừng xe">
                </div>
                <div class="category-body">
                    <h3>🔵 Biển Hiệu Lệnh</h3>
                    <p>Bắt buộc người tham gia giao thông phải chấp hành theo hiệu lệnh.</p>
                    <span class="category-count"><i class="fas fa-sign"></i> Xem tất cả biển hiệu lệnh</span>
                </div>
            </a>

            {{-- Biển Chỉ dẫn --}}
            <a href="{{ route('traffic-signs.show', 'bien-chi-dan') }}" class="category-card">
                <div class="category-header chi-dan">
                    <img src="{{ asset('images/signs/signI401.png') }}" alt="Bệnh viện">
                    <img src="{{ asset('images/signs/signI402.png') }}" alt="Trạm xăng">
                    <img src="{{ asset('images/signs/signI406.png') }}" alt="Nhà WC">
                </div>
                <div class="category-body">
                    <h3>🟢 Biển Chỉ Dẫn</h3>
                    <p>Hướng dẫn các thông tin cần thiết để người đi đường biết mà xử lý.</p>
                    <span class="category-count"><i class="fas fa-sign"></i> Xem tất cả biển chỉ dẫn</span>
                </div>
            </a>

            {{-- Biển Phụ --}}
            <a href="{{ route('traffic-signs.show', 'bien-phu') }}" class="category-card">
                <div class="category-header phu">
                    <img src="{{ asset('images/signs/signS501.png') }}" alt="Phạm vi tác dụng">
                    <img src="{{ asset('images/signs/signS502.png') }}" alt="Khoảng cách">
                    <img src="{{ asset('images/signs/signS504.png') }}" alt="Làn đường">
                </div>
                <div class="category-body">
                    <h3>📋 Biển Phụ</h3>
                    <p>Bổ sung thông tin, thuyết minh để hiểu rõ hơn các biển báo chính.</p>
                    <span class="category-count"><i class="fas fa-sign"></i> Xem tất cả biển phụ</span>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-section">
    <h2>Sẵn sàng ôn tập lý thuyết?</h2>
    <p>Áp dụng kiến thức biển báo vào các câu hỏi thi thực tế</p>
    <a href="{{ route('practice.cauhoi') }}" class="btn">Bắt đầu ôn tập ngay</a>
</section>
@endsection
