// Browser-only version of the PHP app, so the GitHub Pages demo works without a server.
// Accounts live in localStorage, the "session" in sessionStorage.
// Passwords are salted + SHA-256 hashed here; the real PHP app uses bcrypt (password_hash).
(function () {
  const card = document.querySelector('[data-demo]');
  if (!card) return;

  const USERS_KEY = 'demo-users';
  const SESSION_KEY = 'demo-session';

  const tabs = card.querySelectorAll('[role="tab"]');
  const panels = card.querySelectorAll('[role="tabpanel"]');
  const tabList = card.querySelector('[role="tablist"]');
  const dashboard = card.querySelector('[data-dashboard]');

  /* ---------- Storage helpers (storage can be blocked, so never trust it) ---------- */
  function read(storageName, key, fallback) {
    try {
      const value = window[storageName].getItem(key);
      return value ? JSON.parse(value) : fallback;
    } catch (e) {
      return fallback;
    }
  }

  function write(storageName, key, value) {
    try {
      const storage = window[storageName];
      if (value === null) storage.removeItem(key);
      else storage.setItem(key, JSON.stringify(value));
    } catch (e) {
      /* ignore: the demo still works for this page view */
    }
  }

  let users = read('localStorage', USERS_KEY, {});
  let memorySession = read('sessionStorage', SESSION_KEY, null);

  /* ---------- Hashing ---------- */
  function toHex(buffer) {
    return Array.from(new Uint8Array(buffer), (byte) => byte.toString(16).padStart(2, '0')).join('');
  }

  async function hashPassword(password, salt) {
    const data = new TextEncoder().encode(salt + password);
    return toHex(await crypto.subtle.digest('SHA-256', data));
  }

  /* ---------- UI helpers ---------- */
  function showTab(name) {
    tabs.forEach((tab) => {
      const active = tab.dataset.tab === name;
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
    });
    panels.forEach((panel) => (panel.hidden = panel.dataset.panel !== name));
  }

  function showMessages(form, type, messages) {
    const box = form.querySelector('[data-messages]');
    box.className = 'alert alert--' + type;
    box.setAttribute('role', type === 'error' ? 'alert' : 'status');
    box.replaceChildren(
      ...messages.map((text) => {
        const p = document.createElement('p');
        p.textContent = text;
        return p;
      })
    );
    box.hidden = messages.length === 0;
  }

  function initials(name) {
    const parts = name.split(/[\s_\-.]+/).filter(Boolean);
    const letters = parts.length > 1 ? parts[0][0] + parts[1][0] : name.slice(0, 2);
    return letters.toUpperCase();
  }

  function render(notice) {
    const username = memorySession && memorySession.username;
    const user = username && users[username.toLowerCase()];

    tabList.hidden = Boolean(user);
    dashboard.hidden = !user;

    if (!user) {
      panels.forEach((panel) => (panel.hidden = panel.dataset.panel !== currentTab()));
      return;
    }

    panels.forEach((panel) => (panel.hidden = true));
    dashboard.querySelector('[data-avatar]').textContent = initials(user.username);
    dashboard.querySelector('[data-name]').textContent = user.username;
    dashboard.querySelector('[data-email]').textContent = user.email;
    dashboard.querySelector('[data-since]').textContent = new Date(user.created).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
    dashboard.querySelector('[data-hash]').textContent = user.hash.slice(0, 18) + '…';

    const note = dashboard.querySelector('[data-messages]');
    note.hidden = !notice;
    note.textContent = notice || '';
    if (notice) dashboard.querySelector('[data-logout]').focus({ preventScroll: true });
  }

  function currentTab() {
    const selected = card.querySelector('[role="tab"][aria-selected="true"]');
    return selected ? selected.dataset.tab : 'register';
  }

  function startSession(username, notice) {
    memorySession = { username };
    write('sessionStorage', SESSION_KEY, memorySession);
    render(notice);
  }

  /* ---------- Tabs (click + arrow keys) ---------- */
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => showTab(tab.dataset.tab));
    tab.addEventListener('keydown', (event) => {
      if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
      const next = tabs[(index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
      showTab(next.dataset.tab);
      next.focus();
    });
  });

  card.querySelectorAll('[data-go]').forEach((button) =>
    button.addEventListener('click', () => {
      showTab(button.dataset.go);
      card.querySelector('#tab-' + button.dataset.go).focus();
    })
  );

  /* ---------- Register (same rules as app/register.php) ---------- */
  card.querySelector('[data-form="register"]').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const username = form.username.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value;
    const confirm = form.password_confirm.value;
    const errors = [];

    if (!username) errors.push('Username is required.');
    else if (!/^[A-Za-z0-9_]{3,30}$/.test(username)) errors.push('Username must be 3–30 letters, numbers or underscores.');

    if (!email) errors.push('Email is required.');
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Please enter a valid email address.');

    if (password.length < 8) errors.push('Password must be at least 8 characters.');
    else if (password !== confirm) errors.push('Passwords do not match.');

    if (!errors.length) {
      if (users[username.toLowerCase()]) errors.push('That username is already taken.');
      if (Object.values(users).some((user) => user.email.toLowerCase() === email.toLowerCase())) {
        errors.push('That email is already registered.');
      }
    }

    if (!errors.length && !(window.crypto && crypto.subtle)) {
      errors.push('This browser cannot hash passwords here. Open the demo over HTTPS.');
    }

    if (errors.length) {
      showMessages(form, 'error', errors);
      return;
    }

    const salt = toHex(crypto.getRandomValues(new Uint8Array(16)));
    users[username.toLowerCase()] = {
      username,
      email,
      salt,
      hash: await hashPassword(password, salt),
      created: Date.now(),
    };
    write('localStorage', USERS_KEY, users);

    form.reset();
    showMessages(form, 'error', []);
    startSession(username, 'Your account is ready. Welcome aboard!');
  });

  /* ---------- Log in (same rules as app/login.php) ---------- */
  card.querySelector('[data-form="login"]').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const username = form.username.value.trim();
    const password = form.password.value;
    const errors = [];

    if (!username) errors.push('Username is required.');
    if (!password) errors.push('Password is required.');

    if (!errors.length) {
      const user = users[username.toLowerCase()];
      const hash = user && window.crypto && crypto.subtle ? await hashPassword(password, user.salt) : null;
      if (!user || hash !== user.hash) errors.push('Username or password is incorrect.');
    }

    if (errors.length) {
      showMessages(form, 'error', errors);
      return;
    }

    form.reset();
    showMessages(form, 'error', []);
    startSession(users[username.toLowerCase()].username, 'You are logged in!');
  });

  /* ---------- Log out ---------- */
  dashboard.querySelector('[data-logout]').addEventListener('click', () => {
    memorySession = null;
    write('sessionStorage', SESSION_KEY, null);
    showTab('login');
    render();
    const loginForm = card.querySelector('[data-form="login"]');
    showMessages(loginForm, 'success', ['You have logged out.']);
    card.querySelector('#tab-login').focus();
  });

  render();
})();
