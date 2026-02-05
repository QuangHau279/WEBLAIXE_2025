@extends('layouts.app')
@section('title','Ôn tập')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/quiz-style.css') }}">
@endpush

@section('content')
{{-- BANNER FULL-WIDTH (ngoài container) --}}
<section class="quiz-banner-section" style="background-image:url('{{ asset('images/hinhanh/banner1.png') }}')">
  <div class="quiz-banner-overlay"></div>
</section>

{{-- CONTENT (trong container) --}}
<div class="container quiz-page">
  <h1 class="quiz-heading">Xe máy | Luật mới</h1>

  {{-- CHAPTER TABS - Đặt ở trên, nổi bật --}}
  <div class="chapter-tabs" id="chapterTabs">
    <button class="chapter-tab active" data-chapter="0">
      Tất cả <span class="tab-count">250</span>
    </button>
    <button class="chapter-tab" data-chapter="1">
      Chương 1 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab" data-chapter="2">
      Chương 2 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab" data-chapter="3">
      Chương 3 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab" data-chapter="4">
      Chương 4 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab" data-chapter="5">
      Chương 5 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab" data-chapter="6">
      Chương 6 <span class="tab-count"></span>
    </button>
    <button class="chapter-tab chapter-tab-liet" data-chapter="liet">
      <i class="fas fa-exclamation-triangle"></i> Câu điểm liệt <span class="tab-count">0</span>
    </button>
  </div>

  {{-- Hiển thị tên chương đang chọn --}}
  <div class="chapter-info" id="chapterInfo">
    <strong>Tất cả:</strong> 250 câu hỏi lý thuyết xe máy
  </div>

  <div class="quiz-layout quiz-wrapper">
    {{-- SIDEBAR --}}
    <aside class="quiz-sidebar">
      <div class="sidebar-card" style="position: relative;">
        <label class="sidebar-label">Tìm kiếm</label>
        <input id="qSearch" class="sidebar-input" placeholder="Số câu (1-250) hoặc từ khóa...">
        <div id="search-results" class="search-results" style="display: none;"></div>
      </div>

      <div class="question-grid" id="questionGrid">
        {{-- Grid sẽ được render bằng JavaScript --}}
      </div>

      <div class="sidebar-actions">
        <a class="pill" href="{{ route('thi.thu') }}">Thi thử</a>
      </div>
    </aside>

    {{-- MAIN --}}
    <section class="quiz-main">
      <div class="question-card">
        <div id="question-container"><p>Đang tải câu hỏi…</p></div>
        <div class="navigation-buttons">
          <button id="prev-btn" class="nav-btn" disabled>&lt;&lt; Câu trước</button>
          <button id="next-btn" class="nav-btn" disabled>Câu sau &gt;&gt;</button>
        </div>
      </div>
    </section>
  </div>

  <div class="quiz-extra img-holder" style="height:340px">
    <span>Nội dung thêm sau</span>
  </div>
</div>
@endsection

@push('scripts')
  <script>
    window.QUIZ_CONFIG = {
      apiBase: "{{ url('/api/xe-may') }}",
      initialStt: {{ $initialStt ?? 'null' }},
      isXeMay: true,
      maxQuestions: 250,
      chapters: [
        { id: 0, name: 'Tất cả', desc: '250 câu hỏi lý thuyết xe máy' },
        { id: 1, name: 'Chương 1', desc: 'Quy định chung và quy tắc giao thông đường bộ' },
        { id: 2, name: 'Chương 2', desc: 'Văn hóa giao thông, đạo đức người lái xe' },
        { id: 3, name: 'Chương 3', desc: 'Kỹ thuật lái xe' },
        { id: 4, name: 'Chương 4', desc: 'Cấu tạo và sửa chữa' },
        { id: 5, name: 'Chương 5', desc: 'Báo hiệu đường bộ' },
        { id: 6, name: 'Chương 6', desc: 'Giải thế sa hình và kỹ năng xử lý tình huống' }
      ]
    };
    window.QUESTION_API = window.QUIZ_CONFIG.apiBase;
  </script>
  <script src="{{ asset('js/quiz-logic-xemay.js') }}" defer></script>
@endpush
