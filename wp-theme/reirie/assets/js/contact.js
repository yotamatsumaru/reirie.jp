/* ============================================================
   REIRIE - contact.js
   お問い合わせフォームのバリデーション・AJAX送信
   ============================================================ */
(function(){
  const form = document.getElementById('reirie-contact-form');
  if (!form) return;

  const result    = document.getElementById('reirie-form-result');
  const submitBtn = form.querySelector('.reirie-form__btn');
  const message   = form.querySelector('#contact-message');
  const counter   = form.querySelector('[data-count]');
  const typeRadios = form.querySelectorAll('input[name="type"]');

  /* ----- 文字数カウンター ----- */
  if (message && counter) {
    const updateCount = () => {
      const len = message.value.length;
      counter.textContent = len;
      counter.parentElement.classList.toggle('is-over', len > 3000);
    };
    message.addEventListener('input', updateCount);
    updateCount();
  }

  /* ----- 種別変更で表示項目を切り替え ----- */
  function updateConditionalFields() {
    const selected = form.querySelector('input[name="type"]:checked');
    const type = selected ? selected.value : 'fanmail';
    form.querySelectorAll('[data-show-for]').forEach(row => {
      const allowed = row.getAttribute('data-show-for').split(',').map(s => s.trim());
      if (allowed.includes(type)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
        // 隠れた項目はクリア
        row.querySelectorAll('input, textarea').forEach(i => { i.value = ''; });
      }
    });
    // 必須マーク（条件付き）の表示切替
    const companyRow = form.querySelector('[data-show-for="press,casting"]');
    if (companyRow) {
      const reqMark = companyRow.querySelector('.req--cond');
      if (reqMark) reqMark.style.display = (type === 'press' || type === 'casting') ? '' : 'none';
    }
  }
  typeRadios.forEach(r => r.addEventListener('change', updateConditionalFields));
  updateConditionalFields();

  /* ----- エラー表示クリア ----- */
  function clearErrors() {
    form.querySelectorAll('.reirie-form__error').forEach(el => { el.textContent = ''; });
    form.querySelectorAll('.is-error').forEach(el => el.classList.remove('is-error'));
  }

  /* ----- エラー表示 ----- */
  function showErrors(errors) {
    Object.entries(errors).forEach(([key, msg]) => {
      const errEl = form.querySelector(`[data-error="${key}"]`);
      if (errEl) errEl.textContent = msg;
      const input = form.querySelector(`[name="${key}"]`);
      if (input) {
        const row = input.closest('.reirie-form__row');
        if (row) row.classList.add('is-error');
      }
    });
    // 最初のエラー位置にスクロール
    const firstErr = form.querySelector('.is-error');
    if (firstErr) {
      firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  /* ----- 結果メッセージ表示 ----- */
  function showResult(type, msg) {
    result.className = 'reirie-form__result is-' + type;
    result.textContent = msg;
    result.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /* ----- 送信処理 ----- */
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    result.className = 'reirie-form__result';
    result.textContent = '';

    // 二重送信防止
    if (submitBtn.disabled) return;
    submitBtn.disabled = true;
    submitBtn.classList.add('is-loading');

    // FormData
    const fd = new FormData(form);

    try {
      const res = await fetch(REIRIE_CONTACT.ajax_url, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const json = await res.json();

      if (json.success) {
        showResult('success', json.data.message);
        form.reset();
        updateConditionalFields();
        if (counter) counter.textContent = '0';
      } else {
        if (json.data && json.data.errors) {
          showErrors(json.data.errors);
        }
        showResult('error', (json.data && json.data.message) ? json.data.message : '送信に失敗しました。');
      }
    } catch (err) {
      console.error(err);
      showResult('error', '通信エラーが発生しました。時間をおいて再度お試しください。');
    } finally {
      submitBtn.disabled = false;
      submitBtn.classList.remove('is-loading');
    }
  });
})();
