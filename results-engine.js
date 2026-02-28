/**
 * results-engine.js
 * يمسح DOM بعد كل حساب ويضخّ الـ class الصحيح تلقائياً
 * على أي عنصر يحمل class="result-card" أو class="result-area"
 *
 * القواعد:
 *   - يحتوي على نص أو class يدل على "بيع" / خسارة  → result-negative
 *   - يحتوي على نص أو class يدل على "احتفظ" / ربح  → result-positive
 *   - يحتوي على نص أو class يدل على "راجع" / تحذير → result-warning
 *
 * كيف يعمل:
 *   استدعِ  ResultsEngine.paint()  بعد أي عملية رسم نتيجة
 *   أو اتركه يعمل تلقائياً عبر MutationObserver
 */

(function () {
  'use strict';

  /* ===== كلمات المفاتيح ===== */
  const NEGATIVE_WORDS = [
    'بيع', 'sell', 'خسار', 'تراجع', 'خطر', 'سلبي', 'ضعيف',
    'تجاوز', 'مرتفع جداً', 'منخفض جداً', 'خسران', 'result-negative',
    'result-bad', 'result-sell', 'result-danger', 'dot-sell'
  ];

  const POSITIVE_WORDS = [
    'احتفظ', 'hold', 'ممتاز', 'رائع', 'إيجابي', 'آمن', 'ضمن الحد',
    'مثالي', 'نجح', 'حققت', 'ربح', 'تجاوزت', 'result-positive',
    'result-good', 'result-hold', 'result-safe', 'dot-hold'
  ];

  const WARNING_WORDS = [
    'راجع', 'review', 'تحذير', 'انتبه', 'توقف', 'مراجعة', 'تحقق',
    'ارتفاع طفيف', 'انخفاض طفيف', 'result-warning', 'result-review',
    'result-neutral'
  ];

  /* ===== تحديد نوع البطاقة ===== */
  function classify(el) {
    // الأولوية لـ title-class الموجودة مسبقاً
    const titleEl = el.querySelector('.result-title, .status-text, .amount-text, h3, h2');
    const text    = (el.textContent || '').toLowerCase();
    const classes = el.className || '';

    // check class-based hints first (أسرع)
    if (/result-negative|result-bad|result-sell|result-danger/.test(classes)) return 'negative';
    if (/result-positive|result-good|result-hold|result-safe/.test(classes))  return 'positive';
    if (/result-warning|result-review|result-neutral/.test(classes))           return 'warning';

    // فحص title-class
    if (titleEl) {
      const tc = (titleEl.className || '') + ' ' + (titleEl.textContent || '');
      if (/sell|بيع|خسار/.test(tc))        return 'negative';
      if (/hold|احتفظ|ممتاز|آمن/.test(tc)) return 'positive';
      if (/review|راجع|تحذير/.test(tc))    return 'warning';
    }

    // فحص النص الكامل
    let negScore = 0, posScore = 0, warnScore = 0;
    NEGATIVE_WORDS.forEach(w => { if (text.includes(w)) negScore++; });
    POSITIVE_WORDS.forEach(w => { if (text.includes(w)) posScore++; });
    WARNING_WORDS.forEach(w  => { if (text.includes(w)) warnScore++; });

    if (negScore === 0 && posScore === 0 && warnScore === 0) return null;
    if (negScore >= posScore && negScore >= warnScore) return 'negative';
    if (warnScore > posScore)                           return 'warning';
    return 'positive';
  }

  /* ===== تطبيق اللون ===== */
  const TYPE_CLASS = {
    positive: 'result-positive',
    negative: 'result-negative',
    warning:  'result-warning'
  };

  function applyColor(el) {
    // لا تعالج بطاقات داخل results-grid — لها ألوان ثابتة في CSS
    if (el.closest('.results-grid')) return;

    const type = classify(el);
    if (!type) return;

    // أزل أي class سابق
    el.classList.remove('result-positive', 'result-negative', 'result-warning',
                        'result-good', 'result-bad', 'result-hold',
                        'result-sell', 'result-danger', 'result-review',
                        'result-safe', 'result-neutral');
    el.classList.add(TYPE_CLASS[type]);
  }

  /* ===== المسح الكامل ===== */
  function paint() {
    document.querySelectorAll(
      '.result-card, .result-area, .result-box, [class*="result-section"]:not(.results-section)'
    ).forEach(applyColor);
  }

  /* ===== MutationObserver — يراقب أي تغيير في DOM ===== */
  const observer = new MutationObserver(function (mutations) {
    let needsPaint = false;
    mutations.forEach(function (m) {
      if (m.type === 'childList' && m.addedNodes.length > 0) needsPaint = true;
      if (m.type === 'attributes' && m.attributeName === 'style') needsPaint = true;
    });
    if (needsPaint) setTimeout(paint, 50);
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['style', 'class']
  });

  /* ===== واجهة عامة ===== */
  window.ResultsEngine = { paint };

  /* ===== تشغيل عند التحميل ===== */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', paint);
  } else {
    paint();
  }

})();
