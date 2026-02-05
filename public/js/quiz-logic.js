// public/js/quiz-logic.js - Ôn tập xe ô tô (600 câu) với chapter filtering và câu liệt
document.addEventListener('DOMContentLoaded', async function () {
  const gridEl = document.querySelector('.question-grid');
  const box = document.getElementById('question-container');
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const chapterTabs = document.getElementById('chapterTabs');

  const config = window.QUIZ_CONFIG || {};
  const chapters = config.chapters || [];

  let fullGridData = [];  // [{stt, chuong, cauliet}, ...] - raw data from API
  let fullGrid = [];      // [1,2,3,...600] -> stt only
  let grid = [];          // grid hiện tại (có thể lọc theo chương)
  let idx = 0;           // index hiện tại trong grid[]
  const cache = {};       // cache câu theo stt
  let currentFilter = 0;  // 0 = tất cả, 1-6 = chương, 'liet' = câu điểm liệt

  const skeleton = `<div class="question-content"><p>Đang tải câu hỏi...</p></div>`;

  const tpl = (q) => {
    const imgs = (q.images || []).map(im => `
      <figure class="q-img">
        <img src="${im.url}" alt="${im.alt || ''}" loading="lazy" decoding="async">
      </figure>`).join('');

    const answers = (q.cau_tra_lois || []).map((a, i) => `
      <label class="option" data-correct="${a.caudung ? '1' : '0'}">
        <input type="radio" name="answer-${q.id}" value="${a.id}">
        <span>${a.noidung}</span>
      </label>`).join('');

    // Câu 362: hiển thị 2 hình ảnh ngang
    const isQuestion362 = q.stt === 362 || q.stt === '362';
    const imagesClass = isQuestion362 && (q.images || []).length === 2
      ? 'question-images question-images-horizontal'
      : 'question-images';

    return `
      <div class="question-content">
        <p class="question-text"><strong>Câu ${q.stt}:</strong> ${q.noidung}</p>
        ${imgs ? `<div class="${imagesClass}">${imgs}</div>` : ``}
        <div class="answer-options">${answers}</div>
        <div id="feedback" class="feedback"></div>
      </div>`;
  };

  async function getQuestion(stt) {
    if (cache[stt]) return cache[stt];
    const res = await fetch(`/api/cau-hoi/${stt}`);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    cache[stt] = data;
    return data;
  }

  function renderGrid() {
    gridEl.innerHTML = grid.map((stt, i) =>
      `<a href="#" class="question-number" data-i="${i}" data-stt="${stt}">${stt}</a>`
    ).join('');
  }

  function setActiveCell() {
    gridEl.querySelectorAll('.question-number').forEach(el => el.classList.remove('active-question'));
    const active = gridEl.querySelector(`.question-number[data-i="${idx}"]`);
    if (active) active.classList.add('active-question');
  }

  function markGridAfterAnswer(isCorrect) {
    const cell = gridEl.querySelector(`.question-number[data-i="${idx}"]`);
    if (!cell) return;
    cell.classList.remove('answered-correct', 'answered-wrong');
    cell.classList.add(isCorrect ? 'answered-correct' : 'answered-wrong');
  }

  async function show(i) {
    if (i < 0 || i >= grid.length) return;
    idx = i;
    setActiveCell();

    box.innerHTML = skeleton;

    try {
      const stt = grid[i];
      const q = await getQuestion(stt);
      box.innerHTML = tpl(q);

      prevBtn.disabled = i === 0;
      nextBtn.disabled = i === grid.length - 1;

      // prefetch câu kế
      if (grid[i + 1]) getQuestion(grid[i + 1]).catch(() => { });
    } catch (e) {
      box.innerHTML = `<p>Lỗi tải dữ liệu. Vui lòng thử lại.</p>`;
      console.error(e);
    }
  }

  // Cập nhật số lượng câu trong tab câu liệt
  function updateLietTabCount() {
    if (!chapterTabs) return;
    const lietCount = fullGridData.filter(item => item.cauliet === 1).length;
    const lietTab = chapterTabs.querySelector('.chapter-tab[data-chapter="liet"]');
    if (lietTab) {
      const countEl = lietTab.querySelector('.tab-count');
      if (countEl) countEl.textContent = lietCount;
    }
  }

  // Lọc grid theo chương hoặc câu liệt
  function filterByChapter(chapterKey) {
    currentFilter = chapterKey;

    // Update active tab
    if (chapterTabs) {
      chapterTabs.querySelectorAll('.chapter-tab').forEach(tab => {
        const tabChapter = tab.dataset.chapter;
        if (chapterKey === 'liet') {
          tab.classList.toggle('active', tabChapter === 'liet');
        } else {
          tab.classList.toggle('active', parseInt(tabChapter) === chapterKey);
        }
      });
    }

    const chapterInfoEl = document.getElementById('chapterInfo');

    if (chapterKey === 'liet') {
      // Lọc câu điểm liệt
      grid = fullGridData
        .filter(item => item.cauliet === 1)
        .map(item => item.stt);

      if (chapterInfoEl) {
        chapterInfoEl.innerHTML = `<strong>Câu điểm liệt:</strong> ${grid.length} câu quan trọng - Nếu sai sẽ trượt bài thi!`;
      }
    } else if (chapterKey === 0) {
      // Tất cả
      grid = [...fullGrid];
      const chapter = chapters.find(c => c.id === 0);
      if (chapterInfoEl && chapter) {
        chapterInfoEl.innerHTML = `<strong>${chapter.name}:</strong> ${chapter.desc} (${grid.length} câu)`;
      }
    } else {
      // Lọc theo chương
      const chapter = chapters.find(c => c.id === chapterKey);
      if (chapter) {
        grid = fullGrid.filter(stt => stt >= chapter.start && stt <= chapter.end);
        if (chapterInfoEl) {
          chapterInfoEl.innerHTML = `<strong>${chapter.name}:</strong> ${chapter.desc} (${grid.length} câu)`;
        }
      }
    }

    renderGrid();

    // Hiển thị câu đầu tiên
    if (grid.length > 0) {
      show(0);
    } else {
      box.innerHTML = `<p>Không có câu hỏi.</p>`;
    }
  }

  // xử lý chọn đáp án
  box.addEventListener('change', (e) => {
    const input = e.target.closest('input[type="radio"]');
    if (!input) return;
    const wrap = input.closest('.answer-options');
    if (!wrap) return;

    wrap.querySelectorAll('input[type="radio"]').forEach(r => r.disabled = true);
    wrap.querySelectorAll('.option').forEach(o => o.classList.remove('correct', 'wrong'));

    const chosen = input.closest('.option');
    const isCorrect = chosen.dataset.correct === '1' || chosen.dataset.correct === 'true';
    const fb = box.querySelector('#feedback');

    if (isCorrect) {
      chosen.classList.add('correct');
      if (fb) fb.textContent = '✅ Chính xác!';
    } else {
      chosen.classList.add('wrong');
      const right = wrap.querySelector('.option[data-correct="1"], .option[data-correct="true"]');
      if (right) right.classList.add('correct');
      if (fb) fb.textContent = '❌ Chưa đúng!';
    }

    markGridAfterAnswer(isCorrect);
  });

  // Chapter tabs click handler
  if (chapterTabs) {
    chapterTabs.addEventListener('click', (e) => {
      const tab = e.target.closest('.chapter-tab');
      if (!tab) return;
      const chapterKey = tab.dataset.chapter;
      if (chapterKey === 'liet') {
        filterByChapter('liet');
      } else {
        filterByChapter(parseInt(chapterKey));
      }
    });
  }

  // dựng lưới số câu
  try {
    const res = await fetch('/api/cau-hoi/grid');
    const data = await res.json();

    // API trả về array of {stt, chuong, cauliet}
    if (Array.isArray(data) && data.length > 0) {
      if (typeof data[0] === 'object') {
        fullGridData = data;
        fullGrid = data.map(item => item.stt);
      } else {
        // Fallback: API cũ trả về array số
        fullGrid = data;
        fullGridData = data.map(stt => ({ stt, chuong: 1, cauliet: 0 }));
      }
    }
  } catch (e) {
    console.error(e);
    fullGridData = [];
    fullGrid = [];
  }

  if (fullGrid.length === 0) {
    box.innerHTML = `<p>Lỗi tải dữ liệu. Vui lòng tải lại trang.</p>`;
    return;
  }

  grid = [...fullGrid];
  renderGrid();
  updateLietTabCount();

  gridEl.addEventListener('click', (e) => {
    const a = e.target.closest('.question-number'); if (!a) return;
    e.preventDefault(); show(+a.dataset.i);
  });

  prevBtn.onclick = () => show(idx - 1);
  nextBtn.onclick = () => show(idx + 1);

  // Khởi tạo - nếu có initialStt, tìm và hiển thị
  let startIdx = 0;
  const initialStt = config.initialStt;
  if (initialStt) {
    const foundIdx = grid.findIndex(stt => stt == initialStt);
    if (foundIdx >= 0) {
      startIdx = foundIdx;
    }
  }
  show(startIdx);

  // ========= TÌM KIẾM =========
  const searchInput = document.getElementById('qSearch');
  const searchResults = document.getElementById('search-results');
  let searchTimeout = null;

  async function performSearch(query) {
    if (!query || query.trim() === '') {
      if (searchResults) searchResults.style.display = 'none';
      return;
    }

    try {
      const res = await fetch(`/api/cau-hoi/search?q=${encodeURIComponent(query)}`);
      const data = await res.json();
      const items = data.items || [];

      if (!searchResults) return;

      if (items.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results">Không tìm thấy kết quả</div>';
        searchResults.style.display = 'block';
        return;
      }

      searchResults.innerHTML = items.map(item => `
        <div class="search-result-item" data-stt="${item.stt}">
          <div>
            <span class="search-result-stt">Câu ${item.stt}</span>
          </div>
          <div class="search-result-snippet">${item.snippet || ''}</div>
        </div>
      `).join('');

      searchResults.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', function () {
          const stt = parseInt(this.dataset.stt, 10);

          // Chuyển về "Tất cả" nếu câu không nằm trong filter hiện tại
          if (currentFilter !== 0 && !grid.includes(stt)) {
            filterByChapter(0);
          }

          const foundIdx = grid.findIndex(g => g == stt);
          if (foundIdx >= 0) {
            show(foundIdx);
            if (searchInput) searchInput.value = '';
            searchResults.style.display = 'none';
            const gridItem = gridEl.querySelector(`[data-stt="${stt}"]`);
            if (gridItem) {
              gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
        });
      });

      searchResults.style.display = 'block';
    } catch (e) {
      console.error('Search error:', e);
      if (searchResults) {
        searchResults.innerHTML = '<div class="search-no-results">Lỗi tìm kiếm</div>';
        searchResults.style.display = 'block';
      }
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      const query = e.target.value.trim();

      clearTimeout(searchTimeout);

      if (query.length === 0) {
        if (searchResults) searchResults.style.display = 'none';
        return;
      }

      if (/^\d+$/.test(query)) {
        const num = parseInt(query, 10);
        if (num >= 1 && num <= 600) {
          if (currentFilter !== 0 && !grid.includes(num)) {
            filterByChapter(0);
          }
          const foundIdx = grid.findIndex(g => g == num);
          if (foundIdx >= 0) {
            show(foundIdx);
            if (searchResults) searchResults.style.display = 'none';
            const gridItem = gridEl.querySelector(`[data-stt="${num}"]`);
            if (gridItem) {
              gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
          }
        }
      }

      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 300);
    });

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const query = this.value.trim();

        if (/^\d+$/.test(query)) {
          const num = parseInt(query, 10);
          if (num >= 1 && num <= 600) {
            if (currentFilter !== 0 && !grid.includes(num)) filterByChapter(0);
            const foundIdx = grid.findIndex(g => g == num);
            if (foundIdx >= 0) {
              show(foundIdx);
              if (searchResults) searchResults.style.display = 'none';
              const gridItem = gridEl.querySelector(`[data-stt="${num}"]`);
              if (gridItem) {
                gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
              }
            }
          }
        } else if (query.length > 0 && searchResults) {
          const firstResult = searchResults.querySelector('.search-result-item');
          if (firstResult) {
            firstResult.click();
          }
        }
      } else if (e.key === 'Escape') {
        if (searchResults) searchResults.style.display = 'none';
        this.blur();
      }
    });

    document.addEventListener('click', function (e) {
      if (searchInput && searchResults &&
        !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });
  }
});
