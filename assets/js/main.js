// Shared page behaviour: theme toggle, button ripples, scroll reveal, count-up numbers.
(function () {
  const root = document.documentElement;
  const darkQuery = window.matchMedia('(prefers-color-scheme: dark)');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ---------- Theme ---------- */
  function currentTheme() {
    return root.dataset.theme || (darkQuery.matches ? 'dark' : 'light');
  }

  function syncThemeUi() {
    const isDark = currentTheme() === 'dark';
    root.classList.toggle('is-dark', isDark);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      button.setAttribute('aria-pressed', String(isDark));
    });
  }

  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const next = currentTheme() === 'dark' ? 'light' : 'dark';
      root.dataset.theme = next;
      try {
        localStorage.setItem('theme', next);
      } catch (e) {
        /* storage blocked: the choice lasts for this page view only */
      }
      syncThemeUi();
    });
  });

  darkQuery.addEventListener('change', syncThemeUi);
  syncThemeUi();

  /* ---------- Button ripple ---------- */
  document.addEventListener('pointerdown', (event) => {
    const button = event.target.closest('.btn');
    if (!button || reduceMotion.matches) return;

    let ink = button.querySelector('.btn__ink');
    if (!ink) {
      ink = document.createElement('span');
      ink.className = 'btn__ink';
      ink.setAttribute('aria-hidden', 'true');
      button.appendChild(ink);
    }

    const box = button.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    ripple.style.left = event.clientX - box.left + 'px';
    ripple.style.top = event.clientY - box.top + 'px';
    ink.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove());
  });

  /* ---------- Count-up numbers ---------- */
  function countUp(element) {
    const target = Number(element.dataset.count);
    if (reduceMotion.matches) {
      element.textContent = target;
      return;
    }

    const duration = 1200;
    const start = performance.now();

    function frame(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      element.textContent = Math.round(target * eased);
      if (progress < 1) requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
  }

  /* ---------- Scroll reveal ---------- */
  const revealItems = document.querySelectorAll('[data-reveal], [data-count]');

  if ('IntersectionObserver' in window) {
    // The HTML holds the final numbers (works without JS); start them from zero here.
    if (!reduceMotion.matches) {
      document.querySelectorAll('[data-count]').forEach((element) => (element.textContent = '0'));
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const element = entry.target;
          if (element.hasAttribute('data-count')) countUp(element);
          else element.classList.add('is-visible');
          observer.unobserve(element);
        });
      },
      { threshold: 0.15 }
    );
    revealItems.forEach((element) => observer.observe(element));
  } else {
    revealItems.forEach((element) => {
      element.classList.add('is-visible');
      if (element.hasAttribute('data-count')) element.textContent = element.dataset.count;
    });
  }
})();
