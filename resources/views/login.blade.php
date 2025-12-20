<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login - Workspace Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS copied from app.html -->
  <style>
    body { background: #0b1020; color: #e8ecff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .card { background: #121a33; border: 1px solid rgba(255,255,255,0.08); }
    .muted { color: rgba(232,236,255,0.75); }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .badge-soft { background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.14); }
    .btn-primary { background: #4c6fff; border-color: #4c6fff; }
    .form-control, .form-select { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.14); color: #e8ecff; }
    .form-control::placeholder { color: rgba(232,236,255,0.45); }
    .form-control:focus, .form-select:focus { box-shadow: none; border-color: rgba(76,111,255,0.7); }
    .smallcaps { letter-spacing: .08em; text-transform: uppercase; font-size: .78rem; color: rgba(232,236,255,0.7); }
    .toast-container { position: fixed; top: 16px; right: 16px; z-index: 9999; }
    #error-message { color: #ff6b6b; background: rgba(255,107,107,0.1); padding: 12px; border-radius: 6px; margin-bottom: 16px; display: none; }
  </style>
</head>
<body>
  <div class="toast-container" id="toastRoot"></div>

  <div class="container" style="max-width: 500px;">
    <div class="text-center mb-4">
      <h2 class="mb-2">Workspace Booking</h2>
      <div class="muted">Login to continue</div>
    </div>

    <!-- Login card - copied from app.html -->
    <div class="card p-4">
      <div id="error-message"></div>

      <div class="mb-3">
        <div class="smallcaps mb-1">Email</div>
        <input id="email" class="form-control" placeholder="email" value="owner@workspace.com" />
      </div>

      <div class="mb-3">
        <div class="smallcaps mb-1">Password</div>
        <input id="password" type="password" class="form-control" placeholder="password" value="password" />
      </div>

      <div class="d-flex gap-2 mb-3">
        <button class="btn btn-primary w-100" id="btnLogin">Login</button>
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">Demo</button>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="#" data-demo="admin">Admin</a></li>
            <li><a class="dropdown-item" href="#" data-demo="owner">Owner</a></li>
            <li><a class="dropdown-item" href="#" data-demo="manager">Manager</a></li>
            <li><a class="dropdown-item" href="#" data-demo="user">User</a></li>
          </ul>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="badge badge-soft">Token: <span id="tokenState" class="mono">none</span></span>
        <span class="badge badge-soft">User: <span id="userState" class="mono">guest</span></span>
        <span class="badge badge-soft">Roles: <span id="rolesState" class="mono">-</span></span>
      </div>
    </div>

    <div class="text-center mt-3">
      <a href="/" style="color: rgba(232,236,255,0.6); text-decoration: none;">← Back to Home</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ====== CONFIG / STATE (copied from app.html) ======
    const CONFIG = {
      TOKEN_KEY: 'workspace_token',
      USER_KEY: 'workspace_user',
      BASE_URL_KEY: 'workspace_base_url',
    };

    function getBaseUrl() {
      return (localStorage.getItem(CONFIG.BASE_URL_KEY) || '').trim();
    }

    function getToken() { return localStorage.getItem(CONFIG.TOKEN_KEY); }
    function setToken(t) { localStorage.setItem(CONFIG.TOKEN_KEY, t); }
    function clearAuth() {
      localStorage.removeItem(CONFIG.TOKEN_KEY);
      localStorage.removeItem(CONFIG.USER_KEY);
    }

    function getUser() {
      const raw = localStorage.getItem(CONFIG.USER_KEY);
      try { return raw ? JSON.parse(raw) : null; } catch { return null; }
    }
    function setUser(u) { localStorage.setItem(CONFIG.USER_KEY, JSON.stringify(u)); }

    function escapeHtml(str) {
      return String(str)
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");
    }

    function toast(message, type = 'info') {
      const root = document.getElementById('toastRoot');
      const color = { info:'primary', success:'success', warn:'warning', error:'danger' }[type] || 'primary';
      const el = document.createElement('div');
      el.className = `toast align-items-center text-bg-${color} border-0 show mb-2`;
      el.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">${escapeHtml(message)}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
        </div>`;
      el.querySelector('button').onclick = () => el.remove();
      root.appendChild(el);
      setTimeout(() => { el && el.remove(); }, 4500);
    }

    // ====== API CLIENT (copied from app.html) ======
    const api = {
      async request(endpoint, options = {}) {
        const base = getBaseUrl();
        const url = base ? `${base}${endpoint}` : endpoint;
        const headers = Object.assign({ 'Content-Type': 'application/json' }, options.headers || {});
        const token = getToken();
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const res = await fetch(url, Object.assign({}, options, { headers }));
        let payload = null;
        try { payload = await res.json(); } catch { payload = null; }

        if (!res.ok) {
          let msg = (payload && payload.message) ? payload.message : `HTTP ${res.status}`;
          if (payload && payload.errors) {
            msg += ' | ' + JSON.stringify(payload.errors);
          }
          if (res.status === 401) {
            clearAuth();
            updateUI();
          }
          throw new Error(msg);
        }
        return payload;
      },

      async login(email, password) {
        const out = await this.request('/api/auth/login', {
          method: 'POST',
          body: JSON.stringify({ email, password })
        });
        const token = out?.data?.token;
        const user = out?.data?.user;
        if (!token || !user) throw new Error('Login response missing token/user');
        setToken(token);
        setUser(user);
        return out;
      },

      async me() {
        return this.request('/api/auth/me');
      },
    };

    // ====== UI UPDATE ======
    function updateUI() {
      const token = getToken();
      const user = getUser();

      document.getElementById('tokenState').textContent = token ? (token.slice(0,16) + '...') : 'none';
      document.getElementById('userState').textContent = user ? (user.email || user.full_name || user.name || ('id:'+user.id)) : 'guest';
      document.getElementById('rolesState').textContent = user ? JSON.stringify(user.roles || []) : '-';
    }

    function showError(message) {
      const errorDiv = document.getElementById('error-message');
      errorDiv.textContent = message;
      errorDiv.style.display = 'block';
      setTimeout(() => {
        errorDiv.style.display = 'none';
      }, 5000);
    }

    // ====== EVENTS ======
    document.getElementById('btnLogin').onclick = async () => {
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;

      if (!email || !password) {
        showError('Please enter email and password');
        return;
      }

      document.getElementById('btnLogin').disabled = true;
      document.getElementById('btnLogin').textContent = 'Logging in...';

      try {
        await api.login(email, password);
        toast('Login success', 'success');
        updateUI();
        // Redirect to app.html
        setTimeout(() => {
          window.location.href = '/app.html';
        }, 500);
      } catch (e) {
        showError(e.message);
        toast(e.message, 'error');
        document.getElementById('btnLogin').disabled = false;
        document.getElementById('btnLogin').textContent = 'Login';
      }
    };

    // Enter key to login
    document.getElementById('password').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        document.getElementById('btnLogin').click();
      }
    });

    document.getElementById('email').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        document.getElementById('password').focus();
      }
    });

    // Demo accounts (from app.html)
    document.querySelectorAll('[data-demo]').forEach(a => {
      a.onclick = (e) => {
        e.preventDefault();
        const demo = a.dataset.demo;
        const creds = {
          admin: { email: 'admin@workspace.com', password: 'admin123' },
          owner: { email: 'owner@workspace.com', password: 'password' },
          manager: { email: 'manager@workspace.com', password: 'password' },
          user: { email: 'user@workspace.com', password: 'password' },
        };
        const c = creds[demo];
        if (c) {
          document.getElementById('email').value = c.email;
          document.getElementById('password').value = c.password;
        }
      };
    });

    // ====== INIT ======
    (async function init() {
      updateUI();

      // If already logged in with valid token, redirect to app.html
      const token = getToken();
      if (token) {
        try {
          const out = await api.me();
          const user = out?.data?.user;
          if (user) {
            setUser(user);
            toast('Already logged in, redirecting...', 'info');
            setTimeout(() => {
              window.location.href = '/app.html';
            }, 1000);
            return;
          }
        } catch (e) {
          // Token invalid, clear it
          clearAuth();
          updateUI();
        }
      }
    })();
  </script>
</body>
</html>
