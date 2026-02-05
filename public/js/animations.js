/**
 * Animation Utilities - Các hiệu ứng đẹp cho trang web
 * 1. Typing Effect - Gõ chữ từng từ
 * 2. Back to Top - Nút về đầu trang
 * 3. Input Focus Animation - Viền sáng khi focus
 * 4. Confetti Animation - Pháo hoa khi submit thành công
 * 5. Reveal on Scroll (fix) - Đảm bảo hoạt động
 */

(function () {
  'use strict';

  // ===== 1. TYPING EFFECT (Synced with Slideshow) =====
  // Sử dụng: thêm class "typing-effect" và data-texts='["Text 1", "Text 2"]' vào element
  class TypingEffect {
    constructor(element, options = {}) {
      this.element = element;
      this.texts = JSON.parse(element.dataset.texts || '[]');
      this.typeSpeed = options.typeSpeed || 80;
      this.deleteSpeed = options.deleteSpeed || 50;
      this.pauseDuration = options.pauseDuration || 3000; // 3 giây sau khi gõ xong
      this.currentIndex = 0;
      this.currentText = '';
      this.isDeleting = false;

      if (this.texts.length > 0) {
        this.type();
      }
    }

    type() {
      const fullText = this.texts[this.currentIndex];

      if (this.isDeleting) {
        this.currentText = fullText.substring(0, this.currentText.length - 1);
      } else {
        this.currentText = fullText.substring(0, this.currentText.length + 1);
      }

      this.element.innerHTML = `<span class="typing-text">${this.currentText}</span><span class="typing-cursor">|</span>`;

      let timeout = this.isDeleting ? this.deleteSpeed : this.typeSpeed;

      if (!this.isDeleting && this.currentText === fullText) {
        // Hoàn thành gõ, đợi 3 giây rồi chuyển slideshow + xoá
        timeout = this.pauseDuration;
        this.isDeleting = true;

        // Trigger slideshow change sau khi đợi xong
        setTimeout(() => {
          if (window.heroSlideshow && window.heroSlideshow.nextSlide) {
            window.heroSlideshow.nextSlide();
          }
        }, this.pauseDuration - 100); // Chuyển slide ngay trước khi bắt đầu xóa

      } else if (this.isDeleting && this.currentText === '') {
        // Hoàn thành xoá, chuyển text tiếp theo
        this.isDeleting = false;
        this.currentIndex = (this.currentIndex + 1) % this.texts.length;
        timeout = 500;
      }

      setTimeout(() => this.type(), timeout);
    }
  }

  // Auto-init typing effects
  function initTypingEffects() {
    document.querySelectorAll('.typing-effect').forEach(el => {
      new TypingEffect(el);
    });
  }

  // ===== 2. BACK TO TOP BUTTON =====
  function initBackToTop() {
    // Tạo nút
    const btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    btn.setAttribute('aria-label', 'Về đầu trang');
    document.body.appendChild(btn);

    // Hiển thị/ẩn khi scroll
    const toggleVisibility = () => {
      if (window.pageYOffset > 300) {
        btn.classList.add('visible');
      } else {
        btn.classList.remove('visible');
      }
    };

    window.addEventListener('scroll', toggleVisibility, { passive: true });

    // Click để scroll lên
    btn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // ===== 3. INPUT FOCUS ANIMATION =====
  function initInputFocusAnimation() {
    // Thêm wrapper cho các input trong form
    document.querySelectorAll('.lead-form .field').forEach(field => {
      const input = field.querySelector('input, select, textarea');
      const label = field.querySelector('label');

      if (!input || !label) return;

      // Thêm class để styling
      field.classList.add('floating-label-field');

      // Check nếu đã có giá trị
      if (input.value) {
        field.classList.add('has-value');
      }

      input.addEventListener('focus', () => {
        field.classList.add('focused');
      });

      input.addEventListener('blur', () => {
        field.classList.remove('focused');
        if (input.value) {
          field.classList.add('has-value');
        } else {
          field.classList.remove('has-value');
        }
      });

      input.addEventListener('input', () => {
        if (input.value) {
          field.classList.add('has-value');
        } else {
          field.classList.remove('has-value');
        }
      });
    });
  }

  // ===== 4. CONFETTI ANIMATION =====
  class Confetti {
    constructor() {
      this.colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7', '#dfe6e9', '#fd79a8', '#a29bfe'];
    }

    create(x, y) {
      const confetti = document.createElement('div');
      confetti.className = 'confetti-piece';
      confetti.style.left = x + 'px';
      confetti.style.top = y + 'px';
      confetti.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];
      confetti.style.setProperty('--rotate', Math.random() * 360 + 'deg');
      confetti.style.setProperty('--translate-x', (Math.random() - 0.5) * 300 + 'px');
      confetti.style.setProperty('--translate-y', Math.random() * -400 - 100 + 'px');
      document.body.appendChild(confetti);

      setTimeout(() => confetti.remove(), 1500);
    }

    burst(x, y, count = 50) {
      for (let i = 0; i < count; i++) {
        setTimeout(() => this.create(x, y), i * 20);
      }
    }

    celebrate() {
      // Burst từ 3 vị trí
      const width = window.innerWidth;
      this.burst(width * 0.25, window.innerHeight * 0.5, 30);
      this.burst(width * 0.5, window.innerHeight * 0.3, 40);
      this.burst(width * 0.75, window.innerHeight * 0.5, 30);
    }
  }

  window.Confetti = new Confetti();

  // Hook vào form submit
  function initConfettiOnSubmit() {
    const leadForm = document.getElementById('leadForm');
    if (!leadForm) return;

    leadForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('#btnSubmit');
      const originalText = submitBtn.textContent;

      // Hiển thị loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

      try {
        // Lấy dữ liệu form
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        // Giả lập gửi form (thay bằng API thật nếu có)
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Thành công - bắn confetti!
        const rect = submitBtn.getBoundingClientRect();
        window.Confetti.burst(rect.left + rect.width / 2, rect.top, 60);

        // Hiển thị thông báo thành công
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Đăng ký thành công!';
        submitBtn.classList.add('success');

        // Reset form sau 2 giây
        setTimeout(() => {
          leadForm.reset();
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
          submitBtn.classList.remove('success');

          // Reset floating labels
          document.querySelectorAll('.floating-label-field').forEach(f => {
            f.classList.remove('has-value', 'focused');
          });
        }, 3000);

      } catch (error) {
        console.error('Form submit error:', error);
        submitBtn.innerHTML = '<i class="fas fa-times"></i> Có lỗi xảy ra';
        submitBtn.classList.add('error');

        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
          submitBtn.classList.remove('error');
        }, 2000);
      }
    });
  }

  // ===== 5. REVEAL ON SCROLL (Enhanced) =====
  function initRevealOnScroll() {
    const revealElements = document.querySelectorAll('.reveal');
    if (!revealElements.length) return;

    // Thêm class để animation
    revealElements.forEach((el, index) => {
      el.style.transitionDelay = (index * 0.1) + 's';
    });

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(el => observer.observe(el));
  }

  // ===== 6. HERO SLIDESHOW =====
  function initHeroSlideshow() {
    const slideshow = document.querySelector('.hero-slideshow');
    if (!slideshow) return;

    const slides = slideshow.querySelectorAll('.slide');
    const dots = slideshow.querySelectorAll('.dot');
    const prevBtn = slideshow.querySelector('.slideshow-arrow.prev');
    const nextBtn = slideshow.querySelector('.slideshow-arrow.next');

    if (slides.length === 0) return;

    let currentSlide = 0;
    let autoPlayInterval = null;
    const INTERVAL = 5000; // 5 giây

    // Hiển thị slide theo index
    function showSlide(index) {
      // Xử lý index vượt quá giới hạn
      if (index >= slides.length) index = 0;
      if (index < 0) index = slides.length - 1;

      currentSlide = index;

      // Update slides
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
      });

      // Update dots
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
      });

      // Reset progress bar animation
      slideshow.style.animation = 'none';
      slideshow.offsetHeight; // Trigger reflow
      slideshow.style.animation = null;
    }

    // Chuyển slide tiếp theo
    function nextSlide() {
      showSlide(currentSlide + 1);
    }

    // Chuyển slide trước đó
    function prevSlide() {
      showSlide(currentSlide - 1);
    }

    // Bắt đầu auto-play
    function startAutoPlay() {
      stopAutoPlay();
      autoPlayInterval = setInterval(nextSlide, INTERVAL);
    }

    // Dừng auto-play
    function stopAutoPlay() {
      if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
      }
    }

    // Event listeners cho nút điều hướng
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        prevSlide();
        startAutoPlay(); // Restart timer
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        nextSlide();
        startAutoPlay(); // Restart timer
      });
    }

    // Event listeners cho dots
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        showSlide(index);
        startAutoPlay(); // Restart timer
      });
    });

    // Pause khi hover
    slideshow.addEventListener('mouseenter', stopAutoPlay);
    slideshow.addEventListener('mouseleave', startAutoPlay);

    // Touch/swipe support cho mobile
    let touchStartX = 0;
    let touchEndX = 0;

    slideshow.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
      stopAutoPlay();
    }, { passive: true });

    slideshow.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > 50) { // Minimum swipe distance
        if (diff > 0) {
          nextSlide(); // Swipe left = next
        } else {
          prevSlide(); // Swipe right = prev
        }
      }
      startAutoPlay();
    }, { passive: true });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      // Chỉ xử lý khi slideshow đang trong viewport
      const rect = slideshow.getBoundingClientRect();
      const inViewport = rect.top < window.innerHeight && rect.bottom > 0;

      if (!inViewport) return;

      if (e.key === 'ArrowLeft') {
        prevSlide();
      } else if (e.key === 'ArrowRight') {
        nextSlide();
      }
    });

    // Export để typing effect có thể điều khiển
    window.heroSlideshow = {
      nextSlide,
      prevSlide,
      showSlide
    };

    // KHÔNG auto-play - để typing effect điều khiển
    console.log('[Slideshow] Initialized with', slides.length, 'slides (synced with typing)');
  }

  // ===== INIT ALL =====
  function initAll() {
    initTypingEffects();
    initBackToTop();
    initInputFocusAnimation();
    initConfettiOnSubmit();
    initRevealOnScroll();
    initHeroSlideshow();
    console.log('[Animations] All effects initialized');
  }

  // Khởi tạo khi DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Export để sử dụng bên ngoài
  window.AnimationUtils = {
    TypingEffect,
    Confetti: window.Confetti,
    initRevealOnScroll
  };
})();
