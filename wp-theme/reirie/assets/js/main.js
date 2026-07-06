/* ============================================================
   REIRIE - main.js
   ヘッダー、カスタムカーソル、スクロール表示等
   軽量化: モバイル時はカスタムカーソル無効、IO で動画再生制御
   ============================================================ */
(function(){
  // ----- 環境判定（モバイル / 省データ / reduced-motion） -----
  var IS_MOBILE = (window.REIRIE_ENV && window.REIRIE_ENV.isMobile == 1)
    || /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)
    || window.matchMedia('(max-width: 768px)').matches
    || ('ontouchstart' in window);
  var REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var SAVE_DATA = !!(navigator.connection && navigator.connection.saveData);

  // ----- ヘッダーのスクロール状態（rAF 節約版） -----
  const header = document.getElementById('header');
  if (header) {
    let scrollTicking = false;
    const onScroll = () => {
      if (scrollTicking) return;
      scrollTicking = true;
      requestAnimationFrame(() => {
        if (window.scrollY > 60) header.classList.add('is-scrolled');
        else header.classList.remove('is-scrolled');
        scrollTicking = false;
      });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ----- ハンバーガーメニュー -----
  const hamburger = document.getElementById('hamburger');
  const gnav = document.getElementById('gnav');
  if (hamburger && gnav) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('is-open');
      gnav.classList.toggle('is-open');
    });
    gnav.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        hamburger.classList.remove('is-open');
        gnav.classList.remove('is-open');
      });
    });
  }

  // ----- カスタムカーソル（PC のみ） -----
  const dot = document.getElementById('cursor-dot');
  const ring = document.getElementById('cursor-ring');
  if (!IS_MOBILE && dot && ring) {
    let mx = window.innerWidth/2, my = window.innerHeight/2;
    let rx = mx, ry = my;
    let cursorRAF = null;
    let cursorActive = true;

    window.addEventListener('mousemove', (e) => {
      mx = e.clientX; my = e.clientY;
      dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%, -50%)`;
    }, { passive: true });

    function animateCursor() {
      if (!cursorActive) { cursorRAF = null; return; }
      rx += (mx - rx) * 0.18;
      ry += (my - ry) * 0.18;
      ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
      cursorRAF = requestAnimationFrame(animateCursor);
    }
    animateCursor();

    // タブ非表示で停止
    document.addEventListener('visibilitychange', () => {
      cursorActive = !document.hidden;
      if (cursorActive && !cursorRAF) animateCursor();
    });

    // ホバー時にリングを大きく
    const hoverables = document.querySelectorAll('a, button, .schedule__btn, .more-btn, .contact__card, .news__link, .movie__link, .disco__item, .goods__item, .member__card');
    hoverables.forEach(el => {
      el.addEventListener('mouseenter', () => ring.classList.add('is-hover'));
      el.addEventListener('mouseleave', () => ring.classList.remove('is-hover'));
    });
  } else {
    // モバイル時はカーソル要素を非表示にしておく
    if (dot) dot.style.display = 'none';
    if (ring) ring.style.display = 'none';
  }

  // ----- スケジュール: タブ切替 -----
  const schedTabs = document.querySelectorAll('.schedule__tab');
  const schedViews = document.querySelectorAll('.schedule__view');
  if (schedTabs.length) {
    schedTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.view;
        schedTabs.forEach(t => {
          const on = (t === tab);
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        schedViews.forEach(v => {
          v.classList.toggle('is-active', v.dataset.view === target);
        });
      });
    });
  }

  // ----- スケジュール: 横スクロールカルーセル -----
  const carousels = document.querySelectorAll('[data-carousel]');
  carousels.forEach(carousel => {
    const track = carousel.querySelector('[data-carousel-track]');
    const prev = carousel.querySelector('.schedule-carousel__arrow--prev');
    const next = carousel.querySelector('.schedule-carousel__arrow--next');
    if (!track) return;

    const getStep = () => {
      const card = track.querySelector('.schedule-card');
      if (!card) return 320;
      const style = getComputedStyle(track);
      const gap = parseInt(style.gap || '20', 10) || 20;
      return card.offsetWidth + gap;
    };

    if (prev) prev.addEventListener('click', () => {
      track.scrollBy({ left: -getStep(), behavior: 'smooth' });
    });
    if (next) next.addEventListener('click', () => {
      track.scrollBy({ left: getStep(), behavior: 'smooth' });
    });

    // ドラッグ操作 (PC) — ドラッグ距離が小さければクリックを許可
    let isDown = false, startX = 0, scrollLeft = 0, dragDistance = 0;
    const DRAG_THRESHOLD = 6; // px: これを超えたらクリック扱いしない

    track.addEventListener('mousedown', (e) => {
      // リンク内ボタン等は除外せず、まず押下を記録
      isDown = true;
      dragDistance = 0;
      startX = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
    });
    track.addEventListener('mouseleave', () => {
      isDown = false;
      // すぐにクラス削除すると click 判定に間に合わないため遅延
      setTimeout(() => track.classList.remove('is-dragging'), 50);
    });
    track.addEventListener('mouseup', () => {
      isDown = false;
      setTimeout(() => track.classList.remove('is-dragging'), 50);
    });
    track.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      const x = e.pageX - track.offsetLeft;
      const delta = x - startX;
      dragDistance = Math.abs(delta);
      // 一定量動いて初めて is-dragging を付ける（=リンクのクリックは保護）
      if (dragDistance > DRAG_THRESHOLD) {
        track.classList.add('is-dragging');
        e.preventDefault();
        track.scrollLeft = scrollLeft - delta * 1.5;
      }
    });

    // ドラッグ後のクリックを抑制（誤遷移防止）
    track.addEventListener('click', (e) => {
      if (dragDistance > DRAG_THRESHOLD) {
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);

    // 矢印の表示/非表示を端で切替
    const updateArrows = () => {
      if (!prev || !next) return;
      const sl = track.scrollLeft;
      const max = track.scrollWidth - track.clientWidth - 2;
      prev.style.opacity = sl <= 4 ? '0.35' : '1';
      next.style.opacity = sl >= max ? '0.35' : '1';
    };
    track.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows);
    updateArrows();
  });

  // ----- スクロール表示 (Intersection Observer) -----
  if ('IntersectionObserver' in window) {
    const targets = document.querySelectorAll('.section__head, .news__item, .schedule__item, .disco__item, .movie__item, .member__card, .goods__item, .contact__card');
    targets.forEach(el => el.classList.add('reveal'));

    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => entry.target.classList.add('is-show'), i * 60);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
    targets.forEach(el => io.observe(el));
  }

  // ----- スムーズスクロール (ヘッダー高さ補正) -----
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const headerH = header ? header.offsetHeight : 0;
      const top = target.getBoundingClientRect().top + window.scrollY - headerH + 2;
      window.scrollTo({ top, behavior: REDUCED_MOTION ? 'auto' : 'smooth' });
    });
  });

  // ----- ヒーロー動画 遅延ロード + モバイル自動再生 -----
  // 戦略: 最初はポスター画像だけ表示し、ページ描画後にアイドル中に動画を読み込み開始。
  //       Safari 対策: <source> 動的追加ではなく video.src 直接代入。autoplay は HTML 属性で明示済み。
  const heroVideo = document.querySelector('.hero__video');
  const heroPoster = document.querySelector('.hero__poster');

  // Safari (iOS & macOS) 判定
  var IS_SAFARI = /^((?!chrome|android|crios|fxios|edg).)*safari/i.test(navigator.userAgent);
  var IS_IOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

  if (heroVideo) {
    // muted/playsInline を JS プロパティでも明示（Safari は属性とプロパティ両方で確実に）
    heroVideo.muted = true;
    heroVideo.defaultMuted = true;
    heroVideo.playsInline = true;
    heroVideo.autoplay = true;
    heroVideo.setAttribute('muted', '');
    heroVideo.setAttribute('playsinline', '');
    heroVideo.setAttribute('autoplay', '');
    // volume 0 にもセット（保険）
    try { heroVideo.volume = 0; } catch(e){}

    // 省データモード / reduced-motion → ポスター画像のみ表示
    if (SAVE_DATA || REDUCED_MOTION) {
      heroVideo.style.display = 'none';
      return;
    }

    // モバイル/PC で source を出し分け
    var srcUrl = IS_MOBILE
      ? (heroVideo.getAttribute('data-src-mobile') || heroVideo.getAttribute('data-src-pc'))
      : (heroVideo.getAttribute('data-src-pc')     || heroVideo.getAttribute('data-src-mobile'));

    var sourceInjected = false;
    var playAttempted = false;

    function injectAndPlay() {
      if (sourceInjected || !srcUrl) return;
      sourceInjected = true;
      // Safari は <source> 動的追加で loadeddata が発火しないバグがあるため src 直接代入
      heroVideo.src = srcUrl;
      heroVideo.preload = 'auto';
      heroVideo.load();
      tryPlay();
    }

    function tryPlay() {
      playAttempted = true;
      // 再生前に必ず muted を再確認（Safariはautoplay発動条件として必須）
      heroVideo.muted = true;
      heroVideo.defaultMuted = true;
      try { heroVideo.volume = 0; } catch(e){}

      var p;
      try { p = heroVideo.play(); } catch(e) { p = null; }

      if (p && typeof p.then === 'function') {
        p.catch(function(err){
          // 自動再生ブロック時 → ユーザーの最初の操作で再開
          var resume = function(){
            heroVideo.muted = true;
            heroVideo.play().catch(function(){});
          };
          var evs = ['touchstart','touchend','click','scroll','pointerdown','keydown'];
          var h = function(){
            resume();
            evs.forEach(function(ev){ window.removeEventListener(ev, h); });
          };
          evs.forEach(function(ev){ window.addEventListener(ev, h, { passive:true }); });
        });
      }
    }

    // 再生開始したらポスターをフェードアウト
    heroVideo.addEventListener('playing', function(){
      heroVideo.classList.add('is-playing');
      if (heroPoster) heroPoster.classList.add('is-hidden');
    });

    // Safari 保険: loadedmetadata / loadeddata / canplay でも play() を試行
    ['loadedmetadata','loadeddata','canplay','canplaythrough'].forEach(function(ev){
      heroVideo.addEventListener(ev, function(){
        if (heroVideo.paused) {
          heroVideo.muted = true;
          heroVideo.play().catch(function(){});
        }
      });
    });

    // エラー時は console に出して poster だけ残す
    heroVideo.addEventListener('error', function(){
      // 何もしない（poster がそのまま表示される）
    });

    // ----- 動画ロード開始のタイミング -----
    // Safari / iOS は即時注入（遅延すると autoplay の "ページロード直後" 判定から外れる）
    // それ以外は idle / window.load 後で軽量化
    function startLoad(){
      if (IS_SAFARI || IS_IOS) {
        // Safari は即時実行（autoplay 成立のため遅延しない）
        injectAndPlay();
      } else if ('requestIdleCallback' in window) {
        requestIdleCallback(injectAndPlay, { timeout: 2000 });
      } else {
        setTimeout(injectAndPlay, IS_MOBILE ? 800 : 200);
      }
    }
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      // Safari は interactive でも即時開始
      if (IS_SAFARI || IS_IOS) {
        injectAndPlay();
      } else {
        startLoad();
      }
    } else {
      // DOMContentLoaded で Safari 即時、それ以外は window.load 待ち
      if (IS_SAFARI || IS_IOS) {
        document.addEventListener('DOMContentLoaded', injectAndPlay, { once: true });
      } else {
        window.addEventListener('load', startLoad, { once: true });
      }
    }

    // ----- ヒーローが画面外なら pause（バッテリー節約） -----
    if ('IntersectionObserver' in window) {
      var heroEl = document.querySelector('.hero');
      if (heroEl) {
        var heroIo = new IntersectionObserver(function(entries){
          entries.forEach(function(e){
            if (e.isIntersecting) {
              if (!sourceInjected) injectAndPlay();
              heroVideo.muted = true;
              heroVideo.play().catch(function(){});
            } else {
              heroVideo.pause();
            }
          });
        }, { threshold: 0.05 });
        heroIo.observe(heroEl);
      }
    }

    document.addEventListener('visibilitychange', function(){
      if (!document.hidden) {
        heroVideo.muted = true;
        var heroEl = document.querySelector('.hero');
        if (heroEl) {
          var rect = heroEl.getBoundingClientRect();
          if (rect.bottom > 0 && rect.top < window.innerHeight) {
            heroVideo.play().catch(function(){});
          }
        }
      } else {
        heroVideo.pause();
      }
    });

    heroVideo.addEventListener('error', function(){
      heroVideo.style.opacity = '0';
      // ポスターは残す
    });
  }
})();

/* =========================================
   Schedule Calendar - Ajax 月送り
   (ページリロードしないので scroll-behavior:smooth の影響なし)
========================================= */
(function(){
  document.addEventListener('click', function(e){
    var navLink = e.target.closest ? e.target.closest('.schedule-cal__nav') : null;
    if (!navLink) return;
    if (typeof window.REIRIE_AJAX === 'undefined' || !window.REIRIE_AJAX.url) return;

    var y = navLink.getAttribute('data-cal-y');
    var m = navLink.getAttribute('data-cal-m');
    if (!y || !m) return;

    e.preventDefault();

    var container = document.querySelector('[data-cal-container]');
    if (!container) return;

    // ローディング状態
    if (container.dataset.loading === '1') return;
    container.dataset.loading = '1';
    container.style.transition = 'opacity .15s ease';
    container.style.opacity = '0.5';

    var url = window.REIRIE_AJAX.url
            + (window.REIRIE_AJAX.url.indexOf('?') === -1 ? '?' : '&')
            + 'action=reirie_schedule_cal&y=' + encodeURIComponent(y) + '&m=' + encodeURIComponent(m);

    fetch(url, { credentials: 'same-origin' })
      .then(function(r){ return r.text(); })
      .then(function(html){
        container.innerHTML = html;
        container.setAttribute('data-cal-y', y);
        container.setAttribute('data-cal-m', m);
        container.style.opacity = '1';
        container.dataset.loading = '0';

        // ブラウザURLを更新（リロードなし、ハッシュも付けない＝勝手にスクロールしない）
        try {
          var u = new URL(window.location.href);
          u.searchParams.set('cal_y', y);
          u.searchParams.set('cal_m', m);
          u.hash = ''; // スクロール抑止のためハッシュは消す
          history.replaceState(null, '', u.toString());
        } catch(err){}
      })
      .catch(function(){
        container.style.opacity = '1';
        container.dataset.loading = '0';
      });
  });
})();

/* ===========================================================
   NEWS CAROUSEL — prev/next buttons + scroll snap
   =========================================================== */
(function(){
  var carousels = document.querySelectorAll('[data-news-carousel]');
  if (!carousels.length) return;

  carousels.forEach(function(carousel){
    var track = carousel.querySelector('[data-news-track]');
    var prev  = carousel.querySelector('[data-news-prev]');
    var next  = carousel.querySelector('[data-news-next]');
    if (!track) return;

    function getStep() {
      var card = track.querySelector('.news-card');
      if (!card) return 280;
      var styles = window.getComputedStyle(track);
      var gap = parseFloat(styles.columnGap || styles.gap || '22') || 22;
      return card.getBoundingClientRect().width + gap;
    }

    function updateButtons() {
      if (!prev || !next) return;
      var max = track.scrollWidth - track.clientWidth - 2;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= max;
    }

    if (prev) {
      prev.addEventListener('click', function(){
        track.scrollBy({ left: -getStep(), behavior: 'smooth' });
      });
    }
    if (next) {
      next.addEventListener('click', function(){
        track.scrollBy({ left: getStep(), behavior: 'smooth' });
      });
    }

    track.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);
    // 初期化（スクロール幅が確定するまで少し待つ）
    setTimeout(updateButtons, 100);
    setTimeout(updateButtons, 500);
  });
})();
