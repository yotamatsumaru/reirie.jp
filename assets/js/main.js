/* ============================================================
   REIRIE - main.js
   ヘッダー、カスタムカーソル、スクロール表示等
   ============================================================ */
(function(){
  // ----- ヘッダーのスクロール状態 -----
  const header = document.getElementById('header');
  const onScroll = () => {
    if (window.scrollY > 60) header.classList.add('is-scrolled');
    else header.classList.remove('is-scrolled');
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ----- ハンバーガーメニュー -----
  const hamburger = document.getElementById('hamburger');
  const gnav = document.getElementById('gnav');
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

  // ----- カスタムカーソル -----
  const dot = document.getElementById('cursor-dot');
  const ring = document.getElementById('cursor-ring');
  let mx = window.innerWidth/2, my = window.innerHeight/2;
  let rx = mx, ry = my;

  window.addEventListener('mousemove', (e) => {
    mx = e.clientX; my = e.clientY;
    dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%, -50%)`;
  });

  function animateCursor() {
    rx += (mx - rx) * 0.18;
    ry += (my - ry) * 0.18;
    ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
    requestAnimationFrame(animateCursor);
  }
  animateCursor();

  // ホバー時にリングを大きく
  const hoverables = document.querySelectorAll('a, button, .schedule__btn, .more-btn, .contact__card, .news__link, .movie__link, .disco__item, .goods__item, .member__card');
  hoverables.forEach(el => {
    el.addEventListener('mouseenter', () => ring.classList.add('is-hover'));
    el.addEventListener('mouseleave', () => ring.classList.remove('is-hover'));
  });

  // ----- スクロール表示 (Intersection Observer) -----
  const targets = document.querySelectorAll('.section__head, .news__item, .schedule__item, .disco__item, .movie__item, .member__card, .goods__item, .contact__card');
  targets.forEach(el => el.classList.add('reveal'));

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        // ちょっとずらしてフェード
        setTimeout(() => entry.target.classList.add('is-show'), i * 60);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
  targets.forEach(el => io.observe(el));

  // ----- スムーズスクロール (ヘッダー高さ補正) -----
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const headerH = header.offsetHeight;
      const top = target.getBoundingClientRect().top + window.scrollY - headerH + 2;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });

  // ----- ヒーロー動画フェイルセーフ（モバイル等で再生失敗時にグラデ表示） -----
  const heroVideo = document.querySelector('.hero__video');
  if (heroVideo) {
    heroVideo.addEventListener('error', () => {
      heroVideo.style.display = 'none';
    });
  }
})();
