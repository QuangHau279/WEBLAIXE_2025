// ===== Stats: random -> count-up -> stop (clean & forced) =====
(function() {
  'use strict';
  
  let hasAnimated = false;
  
  function formatNumber(n, el) {
    const pad = el.dataset.pad;
    const suffix = el.dataset.suffix || '';
    let s = String(Math.round(n));
    if (pad) s = s.padStart(parseInt(pad, 10), '0');
    return s + suffix;
  }

  function animateStat(el) {
    if (el.dataset.done === '1') return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.target || '0');
    const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReduce) { 
      el.textContent = formatNumber(target, el); 
      return; 
    }

    // 1) Scramble nhanh (random số) - 600ms
    const SCR_MS = 600;
    const STEP = 30;
    const MAX = Math.max(1, Math.floor(target * 1.3));
    
    const scr = setInterval(() => { 
      el.textContent = formatNumber(Math.floor(Math.random() * MAX), el); 
    }, STEP);

    // 2) Đếm mượt tới đúng số - 900ms
    setTimeout(() => {
      clearInterval(scr);
      const DUR = 900;
      const t0 = performance.now();
      const ease = t => 1 - Math.pow(1 - t, 3);
      
      function tick(now) {
        const p = Math.min(1, (now - t0) / DUR);
        el.textContent = formatNumber(target * ease(p), el);
        if (p < 1) {
          requestAnimationFrame(tick);
        } else {
          el.textContent = formatNumber(target, el);
        }
      }
      tick(t0);
    }, SCR_MS);
  }

  function startAnimation() {
    if (hasAnimated) return;
    hasAnimated = true;
    
    const items = document.querySelectorAll('.js-stat');
    items.forEach(animateStat);
  }

  function initStats() {
    const section = document.getElementById('stats');
    if (!section) {
      // Section #stats chỉ có ở trang chủ, không phải lỗi
      return;
    }
    
    const items = document.querySelectorAll('.js-stat');
    if (items.length === 0) {
      console.warn('[stats] No .js-stat elements found');
      return;
    }
    
    console.log('[stats] Found', items.length, 'stat elements');

    // Kiểm tra xem section có trong view không
    function checkInView() {
      const rect = section.getBoundingClientRect();
      const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
      return rect.top < viewportHeight && rect.bottom > 0;
    }

    // Sử dụng IntersectionObserver nếu có
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            console.log('[stats] Section in view, starting animation');
            startAnimation();
            observer.disconnect();
          }
        });
      }, {
        threshold: 0.1,
        rootMargin: '0px'
      });
      
      observer.observe(section);
      console.log('[stats] IntersectionObserver initialized');
  } else {
      // Fallback: dùng scroll event
      let triggered = false;
      function handleScroll() {
        if (!triggered && checkInView()) {
          triggered = true;
          console.log('[stats] Scrolled to section, starting animation');
          startAnimation();
          window.removeEventListener('scroll', handleScroll);
          window.removeEventListener('resize', handleScroll);
        }
      }
      
      window.addEventListener('scroll', handleScroll, { passive: true });
      window.addEventListener('resize', handleScroll);
      
      // Kiểm tra ngay nếu đã trong view
      if (checkInView()) {
        setTimeout(handleScroll, 100);
    }
  }

    // Helper function để test
    window.__runStats = function() {
      hasAnimated = false;
      document.querySelectorAll('.js-stat').forEach(el => {
        el.dataset.done = '';
      });
      startAnimation();
    };
  }

  // Khởi tạo khi DOM sẵn sàng
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStats);
  } else {
    setTimeout(initStats, 100);
  }
})();
let isShown = false;
/* ===== Off-canvas menu: safe init ===== */
(() => {
  const q = (id) => document.getElementById(id);
  const btnMenu = q('btnMenu');
  const btnClose = q('btnCloseMenu');
  const menu = q('menuRight');
  const scrim = q('scrim');

  if (!menu || !btnMenu) {
    console.warn('[menu] thiếu phần tử: menuRight/btnMenu');
    return;
  }

  const showMenu = () => {
    if (isShown)
       close();
    else open();
  }
  const open = () => {
    menu.classList.add('open');
    menu.setAttribute('aria-hidden','false');
    btnMenu.setAttribute('aria-expanded','true');
    scrim?.classList.add('show');
    isShown = true;
  };
  const close = () => {
    menu.classList.remove('open');
    menu.setAttribute('aria-hidden','true');
    btnMenu.setAttribute('aria-expanded','false');
    scrim?.classList.remove('show');
    isShown = false;
  };

  // gắn listener
  btnMenu.addEventListener('click', showMenu);
  btnClose?.addEventListener('click', close);
  scrim?.addEventListener('click', close);
  window.addEventListener('keydown', e => e.key === 'Escape' && close());

  // tiện test
  window.__menuTest = { open, close };
  console.log('[menu] ready');
})();

// ===== Generic reveal on scroll =====
(() => {
  const els = Array.from(document.querySelectorAll('.reveal'));
  if (!els.length) return;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce){ els.forEach(el => el.classList.add('visible')); return; }

  const io = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting){
            e.target.classList.add('visible');
            io.unobserve(e.target);
          }
        });
      }, { threshold:.15 })
    : null;

  els.forEach(el => {
    if (io) io.observe(el); else el.classList.add('visible');
  });
})();

// ===== Chatbox Widget =====
(() => {
  // Lấy API URL từ window.chatApiUrl hoặc default
  const getApiUrl = () => {
    const url = window.chatApiUrl;
    if (url && url !== 'null' && url !== 'undefined' && url.trim() !== '') {
      return url;
    }
    return 'http://localhost:7070/chat';
  };
  
  const API_URL = getApiUrl();
  console.log('[Chatbox] API URL:', API_URL);
  
  let messages = [];
  let isOpen = false;

  // Khởi tạo chatbox HTML nếu chưa có
  function initChatbox() {
    if (document.getElementById('chatbox-window')) return;

    const chatboxHTML = `
      <button class="chatbox-toggle" id="chatbox-toggle" aria-label="Mở chatbox">
        <svg viewBox="0 0 24 24">
          <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
          <path d="M7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/>
        </svg>
      </button>
      
      <div class="chatbox-window" id="chatbox-window" role="dialog" aria-hidden="true">
        <div class="chatbox-header">
          <div class="chatbox-header-title">Trợ lý AI Lý thuyết lái xe</div>
          <button class="chatbox-close" id="chatbox-close" aria-label="Đóng chatbox"></button>
        </div>
        
        <div class="chatbox-messages" id="chatbox-messages" role="log" aria-live="polite">
          <div class="chatbox-message ai">
            <div class="chatbox-message-avatar">🤖</div>
            <div class="chatbox-message-content">
              Xin chào! Tôi là trợ lý AI về lý thuyết lái xe và luật giao thông Việt Nam. Bạn cần hỏi gì?
            </div>
          </div>
        </div>
        
        <div class="chatbox-input-area">
          <input 
            type="file" 
            id="chatbox-camera-input" 
            accept="image/*" 
            style="display: none;"
          />
          <div class="chatbox-camera-wrapper">
            <button class="chatbox-camera" id="chatbox-camera" aria-label="Chụp hình nhận diện biển báo" title="Chụp hình nhận diện biển báo">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
              </svg>
            </button>
            <div class="chatbox-camera-menu" id="chatbox-camera-menu" style="display: none;">
              <button class="chatbox-camera-option" id="chatbox-camera-upload">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Chọn từ máy tính
              </button>
              <button class="chatbox-camera-option" id="chatbox-camera-live">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                  <circle cx="12" cy="13" r="4"/>
                </svg>
                Chụp từ camera
              </button>
            </div>
          </div>
          
          <!-- Modal camera -->
          <div class="chatbox-camera-modal" id="chatbox-camera-modal" style="display: none;">
            <div class="chatbox-camera-modal-content">
              <div class="chatbox-camera-modal-header">
                <h3>Chụp ảnh từ camera</h3>
                <button class="chatbox-camera-modal-close" id="chatbox-camera-modal-close">✕</button>
              </div>
              <div class="chatbox-camera-modal-body">
                <video id="chatbox-camera-video" autoplay playsinline></video>
                <canvas id="chatbox-camera-canvas" style="display: none;"></canvas>
              </div>
              <div class="chatbox-camera-modal-footer">
                <button class="chatbox-camera-capture-btn" id="chatbox-camera-capture">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                  </svg>
                  Chụp ảnh
                </button>
                <button class="chatbox-camera-cancel-btn" id="chatbox-camera-cancel">Hủy</button>
              </div>
            </div>
          </div>
          <textarea 
            class="chatbox-input" 
            id="chatbox-input" 
            placeholder="Nhập câu hỏi của bạn..." 
            rows="1"
            aria-label="Nhập tin nhắn"
          ></textarea>
          <button class="chatbox-send" id="chatbox-send" aria-label="Gửi tin nhắn">
            <svg viewBox="0 0 24 24">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
          </button>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', chatboxHTML);

    // Gắn event listeners
    const toggleBtn = document.getElementById('chatbox-toggle');
    const closeBtn = document.getElementById('chatbox-close');
    const windowEl = document.getElementById('chatbox-window');
    const messagesEl = document.getElementById('chatbox-messages');
    const inputEl = document.getElementById('chatbox-input');
    const sendBtn = document.getElementById('chatbox-send');
    const cameraBtn = document.getElementById('chatbox-camera');
    const cameraInput = document.getElementById('chatbox-camera-input');
    const cameraMenu = document.getElementById('chatbox-camera-menu');
    const cameraUploadOption = document.getElementById('chatbox-camera-upload');
    const cameraLiveOption = document.getElementById('chatbox-camera-live');
    const cameraModal = document.getElementById('chatbox-camera-modal');
    const cameraVideo = document.getElementById('chatbox-camera-video');
    const cameraCanvas = document.getElementById('chatbox-camera-canvas');
    const cameraCaptureBtn = document.getElementById('chatbox-camera-capture');
    const cameraCancelBtn = document.getElementById('chatbox-camera-cancel');
    const cameraModalClose = document.getElementById('chatbox-camera-modal-close');
    
    let cameraStream = null;

    // Toggle camera menu
    cameraBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = cameraMenu.style.display !== 'none';
      cameraMenu.style.display = isVisible ? 'none' : 'block';
    });

    // Đóng menu khi click bên ngoài
    document.addEventListener('click', (e) => {
      if (!cameraBtn.contains(e.target) && !cameraMenu.contains(e.target)) {
        cameraMenu.style.display = 'none';
      }
    });

    // Chọn từ máy tính
    cameraUploadOption.addEventListener('click', () => {
      cameraMenu.style.display = 'none';
      cameraInput.click();
    });

    // Chụp từ camera
    cameraLiveOption.addEventListener('click', async () => {
      cameraMenu.style.display = 'none';
      await openCameraModal();
    });

    // Đóng modal camera
    const closeCameraModal = async () => {
      if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
      }
      cameraModal.style.display = 'none';
      if (cameraVideo.srcObject) {
        cameraVideo.srcObject = null;
      }
    };

    cameraModalClose.addEventListener('click', closeCameraModal);
    cameraCancelBtn.addEventListener('click', closeCameraModal);

    // Mở modal camera và bắt đầu stream
    async function openCameraModal() {
      try {
        cameraModal.style.display = 'flex';
        
        // Yêu cầu quyền truy cập camera
        const stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'environment', // Ưu tiên camera sau (mobile)
            width: { ideal: 1280 },
            height: { ideal: 720 }
          }
        });
        
        cameraStream = stream;
        cameraVideo.srcObject = stream;
        
        // Đảm bảo nút capture sẵn sàng và video đã load
        cameraCaptureBtn.disabled = false;
        cameraVideo.addEventListener('loadedmetadata', () => {
          cameraCaptureBtn.disabled = false;
        }, { once: true });
        
      } catch (error) {
        console.error('[Chatbox] Camera error:', error);
        addMessage('⚠️ Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập hoặc thử chọn ảnh từ máy tính.', 'ai');
        cameraModal.style.display = 'none';
      }
    }

    // Chụp ảnh từ camera
    function capturePhoto() {
      const videoWidth = cameraVideo.videoWidth;
      const videoHeight = cameraVideo.videoHeight;
      
      cameraCanvas.width = videoWidth;
      cameraCanvas.height = videoHeight;
      
      const ctx = cameraCanvas.getContext('2d');
      ctx.drawImage(cameraVideo, 0, 0, videoWidth, videoHeight);
      
      // Chuyển canvas thành blob
      cameraCanvas.toBlob(async (blob) => {
        if (!blob) {
          addMessage('⚠️ Không thể chụp ảnh. Vui lòng thử lại.', 'ai');
          await closeCameraModal();
          return;
        }
        
        // Đóng modal và dừng stream
        await closeCameraModal();
        
        // Hiển thị ảnh đã chụp
        const imageDataUrl = URL.createObjectURL(blob);
        addMessageWithImage(imageDataUrl, 'user');
        
        // Kiểm tra kích thước blob (max 10MB)
        if (blob.size > 10 * 1024 * 1024) {
          addMessage('⚠️ Ảnh quá lớn. Vui lòng thử lại với điều kiện ánh sáng tốt hơn.', 'ai');
          return;
        }
        
        // Tạo File object từ blob với MIME type rõ ràng
        const file = new File([blob], 'camera-capture.jpg', { 
          type: 'image/jpeg',
          lastModified: Date.now()
        });
        
        // Kiểm tra file trước khi gửi
        console.log('[Chatbox] Camera capture file info:', {
          name: file.name,
          size: file.size,
          type: file.type,
          sizeMB: (file.size / 1024 / 1024).toFixed(2)
        });
        
        // Hiển thị typing indicator
        const typingId = addTypingIndicator();
        
        try {
          // Gửi ảnh lên server để nhận diện
          const formData = new FormData();
          formData.append('image', file);
          
          // Thêm CSRF token nếu có
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
          if (csrfToken) {
            formData.append('_token', csrfToken);
          }
          
          const response = await fetch('/api/detect-traffic-sign', {
            method: 'POST',
            headers: csrfToken ? {
              'X-CSRF-TOKEN': csrfToken
            } : {},
            body: formData
          });

          removeTypingIndicator(typingId);

          // Kiểm tra Content-Type trước khi parse
          const contentType = response.headers.get('content-type') || '';
          const isJson = contentType.includes('application/json');

          if (!response.ok) {
            let errorText = 'Lỗi khi nhận diện biển báo';
            try {
              if (isJson) {
                const errorJson = await response.json();
                errorText = errorJson.message || errorJson.error || errorText;
              } else {
                errorText = await response.text();
                // Nếu là HTML, lấy message ngắn gọn
                if (errorText.includes('<')) {
                  errorText = `Lỗi ${response.status}: ${response.statusText}`;
                }
              }
            } catch (e) {
              errorText = `Lỗi ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorText);
          }

          // Đảm bảo response là JSON
          if (!isJson) {
            const text = await response.text();
            console.error('[Chatbox] Non-JSON response:', text.substring(0, 200));
            throw new Error('Server trả về dữ liệu không phải JSON. Có thể có lỗi xảy ra.');
          }

          const result = await response.json();
          
          // Hiển thị kết quả nhận diện
          displayDetectionResult(result);
          
          // Giải phóng URL object
          URL.revokeObjectURL(imageDataUrl);
          
        } catch (error) {
          removeTypingIndicator(typingId);
          console.error('[Chatbox] Detection error:', error);
          addMessage('⚠️ Có lỗi xảy ra khi nhận diện biển báo: ' + error.message, 'ai');
          URL.revokeObjectURL(imageDataUrl);
        }
      }, 'image/jpeg', 0.9);
    }

    // Auto-resize textarea
    inputEl.addEventListener('input', () => {
      inputEl.style.height = 'auto';
      inputEl.style.height = inputEl.scrollHeight + 'px';
    });

    // Toggle chatbox
    toggleBtn.addEventListener('click', () => {
      isOpen = !isOpen;
      windowEl.classList.toggle('open');
      windowEl.setAttribute('aria-hidden', !isOpen);
      if (isOpen) {
        inputEl.focus();
      }
    });

    closeBtn.addEventListener('click', () => {
      isOpen = false;
      windowEl.classList.remove('open');
      windowEl.setAttribute('aria-hidden', 'true');
    });

    // Send message
    const sendMessage = async () => {
      const message = inputEl.value.trim();
      if (!message || sendBtn.disabled) return;

      // Disable input
      sendBtn.disabled = true;
      inputEl.disabled = true;

      // Add user message
      addMessage(message, 'user');
      inputEl.value = '';
      inputEl.style.height = 'auto';

      // Show typing indicator
      const typingId = addTypingIndicator();

      try {
        console.log('[Chatbox] Sending message to:', API_URL);
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message })
        });

        removeTypingIndicator(typingId);

        console.log('[Chatbox] Response status:', response.status, response.statusText);

        // Xử lý lỗi 429 (quá giới hạn) trước khi parse JSON
        if (response.status === 429) {
          try {
            const errorData = await response.json();
            const errorMsg = errorData?.message || 'Đã đạt giới hạn số câu hỏi';
            const suggestion = errorData?.suggestion || '';
            addMessage(`⚠️ ${errorMsg}\n\n${suggestion}`, 'ai');
            return;
          } catch (e) {
            // Nếu không parse được JSON, dùng text
            const errorText = await response.text();
            addMessage(`⚠️ Đã đạt giới hạn số câu hỏi. ${errorText}`, 'ai');
            return;
          }
        }

        if (!response.ok) {
          const errorText = await response.text();
          console.error('[Chatbox] Server error:', response.status, errorText);
          throw new Error(`Server error: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        console.log('[Chatbox] Response data:', data);
        
        const answer = data?.answer || 'Xin lỗi, tôi không hiểu câu hỏi này.';
        
        // Hiển thị số câu còn lại nếu có
        let displayAnswer = answer;
        if (data?.remaining !== undefined && data?.limit !== undefined) {
          const remaining = data.remaining;
          const limit = data.limit;
          if (remaining <= 3 && remaining > 0) {
            displayAnswer += `\n\n⚠️ Bạn còn ${remaining}/${limit} câu hỏi trong session này.`;
          } else if (remaining === 0) {
            displayAnswer += `\n\n⚠️ Bạn đã sử dụng hết ${limit} câu hỏi trong session này.`;
          }
        }

        addMessage(displayAnswer, 'ai');
      } catch (error) {
        removeTypingIndicator(typingId);
        console.error('[Chatbox] Error details:', error);
        
        let errorMessage = 'Xin lỗi, có lỗi xảy ra khi kết nối với server. ';
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
          errorMessage += 'Vui lòng kiểm tra xem chatbox API server đã chạy chưa (http://localhost:7070).';
        } else if (error.message.includes('CORS')) {
          errorMessage += 'Lỗi CORS. Vui lòng kiểm tra cấu hình server.';
        } else {
          errorMessage += error.message || 'Vui lòng thử lại sau.';
        }
        
        addMessage(errorMessage, 'ai');
      } finally {
        sendBtn.disabled = false;
        inputEl.disabled = false;
        inputEl.focus();
      }
    };

    sendBtn.addEventListener('click', sendMessage);
    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    // Xử lý chụp hình nhận diện biển báo
    cameraBtn.addEventListener('click', () => {
      cameraInput.click();
    });

    cameraInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      // Kiểm tra loại file
      if (!file.type.startsWith('image/')) {
        addMessage('⚠️ Vui lòng chọn file ảnh hợp lệ (JPG, PNG).', 'ai');
        return;
      }
      
      // Kiểm tra kích thước file (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        addMessage('⚠️ Ảnh quá lớn. Vui lòng chọn ảnh nhỏ hơn 10MB.', 'ai');
        return;
      }
      
      // Log file info để debug
      console.log('[Chatbox] Upload file info:', {
        name: file.name,
        size: file.size,
        type: file.type,
        sizeMB: (file.size / 1024 / 1024).toFixed(2)
      });

      // Hiển thị ảnh đã chụp
      const reader = new FileReader();
      reader.onload = async (event) => {
        const imageDataUrl = event.target.result;
        
        // Thêm message với ảnh
        addMessageWithImage(imageDataUrl, 'user');
        
        // Hiển thị typing indicator
        const typingId = addTypingIndicator();
        
        try {
          // Gửi ảnh lên server để nhận diện
          const formData = new FormData();
          formData.append('image', file);
          
          // Thêm CSRF token nếu có
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
          if (csrfToken) {
            formData.append('_token', csrfToken);
          }
          
          const response = await fetch('/api/detect-traffic-sign', {
            method: 'POST',
            headers: csrfToken ? {
              'X-CSRF-TOKEN': csrfToken
            } : {},
            body: formData
          });

          removeTypingIndicator(typingId);

          // Kiểm tra Content-Type trước khi parse
          const contentType = response.headers.get('content-type') || '';
          const isJson = contentType.includes('application/json');

          if (!response.ok) {
            let errorText = 'Lỗi khi nhận diện biển báo';
            try {
              if (isJson) {
                const errorJson = await response.json();
                errorText = errorJson.message || errorJson.error || errorText;
              } else {
                errorText = await response.text();
                // Nếu là HTML, lấy message ngắn gọn
                if (errorText.includes('<')) {
                  errorText = `Lỗi ${response.status}: ${response.statusText}`;
                }
              }
            } catch (e) {
              errorText = `Lỗi ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorText);
          }

          // Đảm bảo response là JSON
          if (!isJson) {
            const text = await response.text();
            console.error('[Chatbox] Non-JSON response:', text.substring(0, 200));
            throw new Error('Server trả về dữ liệu không phải JSON. Có thể có lỗi xảy ra.');
          }

          const result = await response.json();
          
          // Hiển thị kết quả nhận diện
          displayDetectionResult(result);
          
        } catch (error) {
          removeTypingIndicator(typingId);
          console.error('[Chatbox] Detection error:', error);
          addMessage('⚠️ Có lỗi xảy ra khi nhận diện biển báo: ' + error.message, 'ai');
        }
      };
      
      reader.readAsDataURL(file);
      
      // Reset input để có thể chọn lại cùng file
      e.target.value = '';
    });

    // Helper functions
    function addMessage(content, type) {
      const time = new Date().toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
      
      const messageEl = document.createElement('div');
      messageEl.className = `chatbox-message ${type}`;
      messageEl.innerHTML = `
        <div class="chatbox-message-avatar">${type === 'user' ? 'Bạn' : '🤖'}</div>
        <div class="chatbox-message-content">
          ${content.replace(/\n/g, '<br>')}
          <div class="chatbox-message-time">${time}</div>
        </div>
      `;
      
      messagesEl.appendChild(messageEl);
      scrollToBottom();
    }

    function addMessageWithImage(imageDataUrl, type) {
      const time = new Date().toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
      
      const messageEl = document.createElement('div');
      messageEl.className = `chatbox-message ${type}`;
      messageEl.innerHTML = `
        <div class="chatbox-message-avatar">${type === 'user' ? 'Bạn' : '🤖'}</div>
        <div class="chatbox-message-content">
          <img src="${imageDataUrl}" alt="Ảnh đã chụp" class="chatbox-message-image" />
          <div class="chatbox-message-time">${time}</div>
        </div>
      `;
      
      messagesEl.appendChild(messageEl);
      scrollToBottom();
    }

    function displayDetectionResult(result) {
      const time = new Date().toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
      
      // Nếu có lỗi, hiển thị thông báo lỗi
      if (!result.success && result.message) {
        let errorMsg = result.message;
        if (result.errors) {
          // Hiển thị chi tiết lỗi validation
          const errorDetails = Object.values(result.errors).flat().join(', ');
          if (errorDetails) {
            errorMsg += ': ' + errorDetails;
          }
        }
        addMessage('⚠️ ' + errorMsg, 'ai');
        return;
      }
      
      let resultHTML = '<div class="chatbox-detection-result">';
      resultHTML += '<h4>🔍 Kết quả nhận diện biển báo</h4>';
      
      if (result.detections && result.detections.length > 0) {
        result.detections.forEach((detection, index) => {
          resultHTML += `<div class="chatbox-detection-item">`;
          resultHTML += `<strong>Biển báo ${index + 1}:</strong> ${detection.label || 'Không xác định'}`;
          if (detection.confidence) {
            resultHTML += ` <span style="color: #666;">(${(detection.confidence * 100).toFixed(1)}%)</span>`;
          }
          if (detection.description) {
            resultHTML += `<br><small>${detection.description}</small>`;
          }
          resultHTML += `</div>`;
        });
      } else {
        resultHTML += '<div class="chatbox-detection-item">Không phát hiện biển báo nào trong ảnh.</div>';
      }
      
      resultHTML += '</div>';
      
      const messageEl = document.createElement('div');
      messageEl.className = 'chatbox-message ai';
      messageEl.innerHTML = `
        <div class="chatbox-message-avatar">🤖</div>
        <div class="chatbox-message-content">
          ${resultHTML}
          <div class="chatbox-message-time">${time}</div>
        </div>
      `;
      
      messagesEl.appendChild(messageEl);
      scrollToBottom();
    }

    function addTypingIndicator() {
      const typingEl = document.createElement('div');
      typingEl.className = 'chatbox-typing';
      typingEl.innerHTML = '<span></span><span></span><span></span>';
      typingEl.dataset.id = Date.now();
      messagesEl.appendChild(typingEl);
      scrollToBottom();
      return typingEl.dataset.id;
    }

    function removeTypingIndicator(id) {
      const typingEl = messagesEl.querySelector(`.chatbox-typing[data-id="${id}"]`);
      if (typingEl) typingEl.remove();
    }

    function scrollToBottom() {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // Thêm message welcome vào history
    messages.push({ type: 'ai', content: 'Xin chào! Tôi là trợ lý AI về lý thuyết lái xe và luật giao thông Việt Nam. Bạn cần hỏi gì?' });
  }

  // Khởi tạo khi DOM sẵn sàng
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatbox);
  } else {
    initChatbox();
  }
})();

// ===== Toast Notification =====
function showToast(message, type = 'success') {
  // Remove existing toast
  const existingToast = document.querySelector('.toast');
  if (existingToast) {
    existingToast.remove();
  }
  
  // Create toast element
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const icon = type === 'success' ? '✅' : '❌';
  toast.innerHTML = `
    <span class="toast-icon">${icon}</span>
    <span class="toast-message">${message}</span>
    <button class="toast-close" onclick="this.parentElement.remove()">×</button>
  `;
  
  document.body.appendChild(toast);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.style.animation = 'toastSlideIn 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    }
  }, 5000);
}

// ===== Lead Form Submission =====
(function() {
  'use strict';
  
  function initLeadForm() {
    const form = document.getElementById('leadForm');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (!form || !btnSubmit) {
      console.warn('[Lead Form] Form or button not found');
      return;
    }
    
    console.log('[Lead Form] Initialized');
    
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!CSRF_TOKEN) {
      console.error('[Lead Form] CSRF token not found');
    }
    
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      console.log('[Lead Form] Form submitted');
      
      // Disable button
      btnSubmit.disabled = true;
      const originalText = btnSubmit.textContent;
      btnSubmit.textContent = 'Đang xử lý...';
      
      // Get form data
      const formData = new FormData(form);
      const data = {
        name: formData.get('name')?.trim(),
        phone: formData.get('phone')?.trim(),
        license: formData.get('license') || null,
      };
      
      console.log('[Lead Form] Submitting data:', data);
      
      // Validate
      if (!data.name || !data.phone) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'error');
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
        return;
      }
      
      try {
        const response = await fetch('/api/leads', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(data),
        });
        
        console.log('[Lead Form] Response status:', response.status);
        
        const result = await response.json();
        console.log('[Lead Form] Response data:', result);
        
        if (result.success) {
          // Success: show toast and reset form
          showToast(result.message || 'Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.', 'success');
          form.reset();
        } else {
          // Error: show error message
          const errorMsg = result.message || result.errors ? Object.values(result.errors || {}).flat().join(', ') : 'Có lỗi xảy ra. Vui lòng thử lại.';
          showToast(errorMsg, 'error');
        }
      } catch (error) {
        console.error('[Lead Form] Error:', error);
        showToast('Có lỗi kết nối. Vui lòng thử lại sau.', 'error');
      } finally {
        // Re-enable button
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
      }
    });
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeadForm);
  } else {
    initLeadForm();
  }
})();