/* ============================================================
   REIRIE - particles.js
   マウスに追従するキラキラパーティクル
   ============================================================ */
(function(){
  const canvas = document.getElementById('particles-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  let W = window.innerWidth;
  let H = window.innerHeight;
  let dpr = Math.min(window.devicePixelRatio || 1, 2);

  function resize() {
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = W * dpr;
    canvas.height = H * dpr;
    canvas.style.width = W + 'px';
    canvas.style.height = H + 'px';
    ctx.setTransform(1,0,0,1,0,0);
    ctx.scale(dpr, dpr);
  }
  resize();
  window.addEventListener('resize', resize);

  // パレット（明るく華やかな色味に）
  const colors = [
    { r: 255, g: 182, b: 213 }, // light pink
    { r: 255, g: 150, b: 200 }, // bright pink
    { r: 255, g: 200, b: 230 }, // pale pink
    { r: 200, g: 170, b: 255 }, // light purple
    { r: 170, g: 220, b: 255 }, // light sky blue
    { r: 255, g: 230, b: 150 }, // light gold
    { r: 255, g: 255, b: 255 }  // white sparkle
  ];

  // 環境光パーティクル（常時うっすら漂う）— 控えめに
  const ambient = [];
  const ambientCount = window.innerWidth < 768 ? 14 : 28;
  for (let i = 0; i < ambientCount; i++) {
    ambient.push({
      x: Math.random() * W,
      y: Math.random() * H,
      r: Math.random() * 1.6 + 0.6,
      vx: (Math.random() - 0.5) * 0.12,
      vy: (Math.random() - 0.5) * 0.12,
      a: Math.random() * 0.3 + 0.3,
      color: colors[Math.floor(Math.random() * colors.length)],
      twinkle: Math.random() * Math.PI * 2
    });
  }

  // マウス追従パーティクル
  const trail = [];
  const mouse = { x: W/2, y: H/2, prevX: W/2, prevY: H/2, moved: false };
  let isTouchDevice = false;

  window.addEventListener('mousemove', (e) => {
    mouse.prevX = mouse.x;
    mouse.prevY = mouse.y;
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.moved = true;
    spawnTrail();
  });

  window.addEventListener('touchmove', (e) => {
    isTouchDevice = true;
    if (e.touches.length > 0) {
      mouse.prevX = mouse.x;
      mouse.prevY = mouse.y;
      mouse.x = e.touches[0].clientX;
      mouse.y = e.touches[0].clientY;
      mouse.moved = true;
      spawnTrail();
    }
  }, { passive: true });

  function spawnTrail() {
    const dx = mouse.x - mouse.prevX;
    const dy = mouse.y - mouse.prevY;
    const dist = Math.sqrt(dx*dx + dy*dy);
    // マウス速度に応じてパーティクル数を変える — 控えめに
    const count = Math.min(Math.floor(dist / 8) + 1, 3);

    for (let i = 0; i < count; i++) {
      const angle = Math.random() * Math.PI * 2;
      const speed = Math.random() * 1.2 + 0.4;
      const c = colors[Math.floor(Math.random() * colors.length)];
      trail.push({
        x: mouse.x + (Math.random() - 0.5) * 6,
        y: mouse.y + (Math.random() - 0.5) * 6,
        vx: Math.cos(angle) * speed + dx * 0.04,
        vy: Math.sin(angle) * speed + dy * 0.04,
        r: Math.random() * 2.5 + 1.2,
        life: 1,
        decay: Math.random() * 0.015 + 0.013,
        color: c,
        rotation: Math.random() * Math.PI,
        rotationSpeed: (Math.random() - 0.5) * 0.1,
        type: Math.random() < 0.25 ? 'star' : 'circle'
      });
    }
    // 最大数制限
    if (trail.length > 200) trail.splice(0, trail.length - 200);
  }

  // 星型を描く
  function drawStar(ctx, x, y, r, rot) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(rot);
    ctx.beginPath();
    for (let i = 0; i < 5; i++) {
      const a1 = (i * 2 * Math.PI / 5) - Math.PI/2;
      const a2 = a1 + Math.PI/5;
      ctx.lineTo(Math.cos(a1)*r, Math.sin(a1)*r);
      ctx.lineTo(Math.cos(a2)*r*0.45, Math.sin(a2)*r*0.45);
    }
    ctx.closePath();
    ctx.fill();
    ctx.restore();
  }

  // ハート型
  function drawHeart(ctx, x, y, r) {
    ctx.save();
    ctx.translate(x, y);
    ctx.beginPath();
    ctx.moveTo(0, r * 0.3);
    ctx.bezierCurveTo(r, -r*0.5, r*1.4, r*0.5, 0, r*1.2);
    ctx.bezierCurveTo(-r*1.4, r*0.5, -r, -r*0.5, 0, r*0.3);
    ctx.fill();
    ctx.restore();
  }

  let frame = 0;

  function animate() {
    frame++;
    ctx.clearRect(0, 0, W, H);

    // ===== 環境光パーティクル =====
    ambient.forEach((p) => {
      p.x += p.vx;
      p.y += p.vy;
      p.twinkle += 0.05;

      if (p.x < 0) p.x = W;
      if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H;
      if (p.y > H) p.y = 0;

      // マウス引力
      if (mouse.moved) {
        const dx = mouse.x - p.x;
        const dy = mouse.y - p.y;
        const d = Math.sqrt(dx*dx + dy*dy);
        if (d < 200) {
          const f = (1 - d/200) * 0.3;
          p.vx += (dx / d) * f * 0.05;
          p.vy += (dy / d) * f * 0.05;
        }
      }
      // 速度減衰
      p.vx *= 0.99;
      p.vy *= 0.99;

      const twinkleAlpha = p.a * (0.6 + Math.sin(p.twinkle) * 0.4);

      // ソフトなグロー（明るく）
      ctx.beginPath();
      const grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.r * 5);
      grad.addColorStop(0, `rgba(${p.color.r},${p.color.g},${p.color.b},${twinkleAlpha * 0.55})`);
      grad.addColorStop(0.4, `rgba(${p.color.r},${p.color.g},${p.color.b},${twinkleAlpha * 0.2})`);
      grad.addColorStop(1, `rgba(${p.color.r},${p.color.g},${p.color.b},0)`);
      ctx.fillStyle = grad;
      ctx.arc(p.x, p.y, p.r * 5, 0, Math.PI * 2);
      ctx.fill();

      // 本体
      ctx.beginPath();
      ctx.fillStyle = `rgba(${p.color.r},${p.color.g},${p.color.b},${Math.min(twinkleAlpha * 1.2, 0.9)})`;
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();

      // 中心の白いコア（明るく光って見える）
      ctx.beginPath();
      ctx.fillStyle = `rgba(255,255,255,${twinkleAlpha * 0.7})`;
      ctx.arc(p.x, p.y, p.r * 0.45, 0, Math.PI * 2);
      ctx.fill();
    });

    // ===== マウス追従トレイル =====
    for (let i = trail.length - 1; i >= 0; i--) {
      const p = trail[i];
      p.x += p.vx;
      p.y += p.vy;
      p.vx *= 0.96;
      p.vy *= 0.96;
      p.vy += 0.02; // 軽い重力でふわっと落ちる
      p.life -= p.decay;
      p.rotation += p.rotationSpeed;

      if (p.life <= 0) {
        trail.splice(i, 1);
        continue;
      }

      const alpha = p.life;
      const size = p.r * p.life;

      // 外側のソフトグロー（明るめに）
      const grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, size * 6);
      grad.addColorStop(0, `rgba(${p.color.r},${p.color.g},${p.color.b},${alpha * 0.5})`);
      grad.addColorStop(0.4, `rgba(${p.color.r},${p.color.g},${p.color.b},${alpha * 0.2})`);
      grad.addColorStop(1, `rgba(${p.color.r},${p.color.g},${p.color.b},0)`);
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(p.x, p.y, size * 6, 0, Math.PI * 2);
      ctx.fill();

      // 本体
      ctx.fillStyle = `rgba(${p.color.r},${p.color.g},${p.color.b},${Math.min(alpha * 1.1, 0.95)})`;
      if (p.type === 'star') {
        drawStar(ctx, p.x, p.y, size * 1.6, p.rotation);
      } else {
        ctx.beginPath();
        ctx.arc(p.x, p.y, size, 0, Math.PI * 2);
        ctx.fill();
      }

      // 中心の明るい白コア
      ctx.fillStyle = `rgba(255,255,255,${alpha * 0.85})`;
      ctx.beginPath();
      ctx.arc(p.x, p.y, size * 0.4, 0, Math.PI * 2);
      ctx.fill();

      // 小さなハイライト（左上にきらめき）
      ctx.fillStyle = `rgba(255,255,255,${alpha * 0.6})`;
      ctx.beginPath();
      ctx.arc(p.x - size * 0.25, p.y - size * 0.25, size * 0.22, 0, Math.PI * 2);
      ctx.fill();
    }

    // ===== マウス周辺リング（明るめのソフト光）=====
    if (mouse.moved && !isTouchDevice) {
      const ringR = 80 + Math.sin(frame * 0.05) * 8;
      const ringGrad = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, ringR);
      ringGrad.addColorStop(0, 'rgba(255,200,230,0)');
      ringGrad.addColorStop(0.5, 'rgba(255,200,230,0.10)');
      ringGrad.addColorStop(1, 'rgba(255,200,230,0)');
      ctx.fillStyle = ringGrad;
      ctx.beginPath();
      ctx.arc(mouse.x, mouse.y, ringR, 0, Math.PI * 2);
      ctx.fill();
    }

    requestAnimationFrame(animate);
  }
  animate();
})();
