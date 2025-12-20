<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Profile - Workspace Booking</title>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: linear-gradient(to bottom, #0b1020 0%, #0f1729 100%);
      color: #e8ecff;
      min-height: 100vh;
    }
    .container { max-width: 1200px; margin: 0 auto; padding: 2.5rem 1.5rem; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
    .header h1 { font-size: 2rem; font-weight: 600; letter-spacing: -0.025em; color: #fff; }
    .header p { margin-top: 0.25rem; color: rgba(232, 236, 255, 0.6); }
    .header-actions { display: flex; gap: 0.5rem; }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; justify-content: center;
      padding: 0.625rem 1rem; border-radius: 0.5rem;
      font-size: 0.875rem; font-weight: 500;
      border: 1px solid; cursor: pointer;
      text-decoration: none; transition: all 0.15s;
    }
    .btn-outline { background: transparent; border-color: rgba(255, 255, 255, 0.14); color: #e8ecff; }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.05); }
    .btn-warning { border-color: #fbbf24; color: #fbbf24; }
    .btn-warning:hover { background: rgba(251, 191, 36, 0.1); }

    /* Grid */
    .grid { display: grid; gap: 1.5rem; }
    .grid-cols-3 { grid-template-columns: 1fr; }
    @media (min-width: 768px) {
      .grid-cols-3 { grid-template-columns: 1fr 2fr; }
    }

    /* Card */
    .card {
      background: rgba(18, 26, 51, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 1rem; padding: 1.5rem;
    }

    /* Avatar section */
    .avatar-section { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .avatar {
      width: 7rem; height: 7rem; border-radius: 50%;
      object-fit: cover; box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.08);
    }
    .avatar-section h2 { margin-top: 1rem; font-size: 1.25rem; font-weight: 600; }
    .avatar-section .email { margin-top: 0.25rem; font-size: 0.875rem; color: rgba(232, 236, 255, 0.6); }

    /* Badges */
    .badges { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; justify-content: center; }
    .badge {
      padding: 0.375rem 0.75rem; border-radius: 9999px;
      font-size: 0.75rem; font-weight: 500;
      background: rgba(76, 111, 255, 0.15); color: #93b4ff;
      border: 1px solid rgba(76, 111, 255, 0.3);
    }
    .badge.admin { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
    .badge.owner { background: rgba(251, 191, 36, 0.15); color: #fcd34d; border-color: rgba(251, 191, 36, 0.3); }
    .badge.manager { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }

    /* Stats */
    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1.5rem; width: 100%; }
    .stat-card {
      background: rgba(11, 16, 32, 0.4); border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 0.75rem; padding: 0.75rem; text-align: center;
    }
    .stat-card .label { font-size: 0.75rem; color: rgba(232, 236, 255, 0.6); }
    .stat-card .value { margin-top: 0.25rem; font-size: 1.5rem; font-weight: 600; }
    .member-since { margin-top: 1.5rem; font-size: 0.75rem; color: rgba(232, 236, 255, 0.5); }

    /* Info section */
    .info-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .info-header h2 { font-size: 1.125rem; font-weight: 600; }
    .info-grid { display: grid; gap: 1.5rem; }
    @media (min-width: 768px) {
      .info-grid { grid-template-columns: 1fr 1fr; }
    }
    .info-field { }
    .info-field.full { grid-column: 1 / -1; }
    .info-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(232, 236, 255, 0.6); }
    .info-value { margin-top: 0.25rem; font-weight: 500; }

    /* Loading state */
    .loading { color: rgba(232, 236, 255, 0.5); }

    /* Footer note */
    .footer-note {
      margin-top: 2rem; padding-top: 1.25rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.75rem; color: rgba(232, 236, 255, 0.5);
      line-height: 1.5;
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <div>
        <h1>My Profile</h1>
        <p>Workspace Booking Dashboard</p>
      </div>

      <div class="header-actions">
        <a href="/app.html" class="btn btn-outline">Back to App</a>
        <button id="btnLogout" class="btn btn-outline btn-warning">Logout</button>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-3">
      <!-- Left: Avatar & Stats -->
      <div class="card">
        <div class="avatar-section">
          <img id="avatar" class="avatar"
               src="https://ui-avatars.com/api/?name=User&background=0b1020&color=e8ecff&size=112"
               alt="avatar" />

          <h2 id="name" class="loading">Loading...</h2>
          <div id="email" class="email loading">—</div>

          <div class="badges" id="roleBadges">
            <!-- Injected by JS -->
          </div>

          <div class="stats">
            <div class="stat-card">
              <div class="label">Bookings</div>
              <div id="bookingCount" class="value loading">—</div>
            </div>
            <div class="stat-card">
              <div class="label">Payments</div>
              <div id="paymentCount" class="value loading">—</div>
            </div>
          </div>

          <p id="memberSince" class="member-since loading">—</p>
        </div>
      </div>

      <!-- Right: Personal Info -->
      <div class="card">
        <div class="info-header">
          <h2>Personal Information</h2>
          <button class="btn btn-outline" id="btnEditProfile" onclick="openEditProfileModal()">
            Edit Profile
          </button>
        </div>

        <div class="info-grid">
          <div class="info-field">
            <div class="info-label">Full Name</div>
            <div id="fullName" class="info-value loading">—</div>
          </div>

          <div class="info-field">
            <div class="info-label">Phone Number</div>
            <div id="phone" class="info-value loading">—</div>
          </div>

          <div class="info-field">
            <div class="info-label">Email Verified</div>
            <div id="emailVerified" class="info-value loading">—</div>
          </div>

          <div class="info-field">
            <div class="info-label">Account Status</div>
            <div id="accountStatus" class="info-value loading">—</div>
          </div>

          <div class="info-field">
            <div class="info-label">User ID</div>
            <div id="userId" class="info-value loading">—</div>
          </div>

          <div class="info-field">
            <div class="info-label">Joined</div>
            <div id="joinedDate" class="info-value loading">—</div>
          </div>
        </div>

        <div class="footer-note">
          💡 Tip: If this page redirects to /login, your token may have expired. Please login again.
        </div>
      </div>
    </div>
  </div>

  <script>
    // ====== CONFIG (matching app.html) ======
    const TOKEN_KEY = "workspace_token";
    const USER_KEY  = "workspace_user";

    function getToken() {
      return localStorage.getItem(TOKEN_KEY);
    }

    function clearAuth() {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
    }

    function logoutAndRedirect() {
      // Call API logout
      fetch('/api/auth/logout', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${getToken()}`,
          'Accept': 'application/json',
        }
      }).catch(() => {});

      clearAuth();
      window.location.href = "/login";
    }

    async function apiFetch(path, opts = {}) {
      const token = getToken();
      const headers = Object.assign({
        "Accept": "application/json",
        "Content-Type": "application/json",
      }, opts.headers || {});

      if (token) headers["Authorization"] = `Bearer ${token}`;

      const res = await fetch(path, Object.assign({}, opts, { headers }));

      if (res.status === 401) {
        clearAuth();
        window.location.href = "/login";
        return null;
      }

      return res;
    }

    function badge(text) {
      const span = document.createElement("span");
      span.className = `badge ${text}`;
      span.textContent = text;
      return span;
    }

    function formatDate(isoString) {
      if (!isoString) return "—";
      const d = new Date(isoString);
      if (Number.isNaN(d.getTime())) return "—";
      return d.toLocaleDateString('vi-VN', {
        year: "numeric",
        month: "short",
        day: "2-digit"
      });
    }

    async function loadProfile() {
      const token = getToken();
      if (!token) {
        window.location.href = "/login";
        return;
      }

      try {
        // 1) Load user info from /api/auth/me
        const meRes = await apiFetch("/api/auth/me", { method: "GET" });
        if (!meRes) return;

        const meJson = await meRes.json();

        // API returns: { success: true, data: { user: {...} } }
        const user = meJson.data?.user || meJson.data || meJson;

        // Update UI
        document.getElementById("userId").textContent = user.id ?? "—";
        document.getElementById("name").textContent = user.full_name || user.name || "—";
        document.getElementById("fullName").textContent = user.full_name || user.name || "—";
        document.getElementById("email").textContent = user.email ?? "—";
        document.getElementById("phone").textContent = user.phone_number || "Not provided";

        // Email verified
        const isVerified = user.is_verified || user.email_verified_at;
        document.getElementById("emailVerified").textContent = isVerified ? "✅ Yes" : "❌ No";

        // Account status
        const isActive = user.is_active !== false;
        document.getElementById("accountStatus").textContent = isActive ? "✅ Active" : "⚠️ Inactive";

        // Dates
        document.getElementById("joinedDate").textContent = formatDate(user.created_at);
        document.getElementById("memberSince").textContent = `Member since ${formatDate(user.created_at)}`;

        // Avatar fallback
        const avatarUrl = user.profile_avatar_url || user.avatar_url || user.avatar || null;
        const name = user.full_name || user.name || "User";
        const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0b1020&color=e8ecff&size=112`;
        document.getElementById("avatar").src = avatarUrl || fallback;

        // Roles
        const roleBadges = document.getElementById("roleBadges");
        roleBadges.innerHTML = "";

        const roles = user.roles || [];
        if (roles.length === 0) {
          roleBadges.appendChild(badge("user"));
        } else {
          roles.forEach(r => roleBadges.appendChild(badge(r)));
        }

        // 2) Load booking count
        try {
          const bRes = await apiFetch("/api/bookings?per_page=1", { method: "GET" });
          if (bRes) {
            const bJson = await bRes.json();
            const total = bJson?.data?.total || bJson?.meta?.total || bJson?.total || 0;
            document.getElementById("bookingCount").textContent = total;
          }
        } catch (e) {
          document.getElementById("bookingCount").textContent = "—";
        }

        // 3) Load payment count
        try {
          const pRes = await apiFetch("/api/payments?per_page=1", { method: "GET" });
          if (pRes) {
            const pJson = await pRes.json();
            const total = pJson?.data?.total || pJson?.meta?.total || pJson?.total || 0;
            document.getElementById("paymentCount").textContent = total;
          }
        } catch (e) {
          document.getElementById("paymentCount").textContent = "—";
        }

        // Remove loading classes
        document.querySelectorAll('.loading').forEach(el => {
          el.classList.remove('loading');
        });

      } catch (err) {
        console.error("Error loading profile:", err);
        alert("Failed to load profile. Redirecting to login...");
        logoutAndRedirect();
      }
    }

    // Event listeners
    document.getElementById("btnLogout").addEventListener("click", logoutAndRedirect);

    // ====== EDIT PROFILE MODAL ======
    function openEditProfileModal() {
      const user = JSON.parse(localStorage.getItem(USER_KEY) || '{}');

      const html = `
        <div id="modalEditProfile" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:9999">
          <div style="background:#0b1020;border:1px solid rgba(255,255,255,0.1);border-radius:1rem;width:90%;max-width:500px;padding:2rem">
            <h2 style="color:#fff;margin-bottom:1.5rem;font-size:1.5rem">Edit Profile</h2>

            <div style="margin-bottom:1.5rem">
              <label style="display:block;color:rgba(232,236,255,0.7);font-size:0.875rem;margin-bottom:0.5rem">Full Name</label>
              <input type="text" id="editFullName" value="${user.full_name || user.name || ''}" style="width:100%;padding:0.75rem;background:#1a2332;border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;color:#e8ecff;font-size:1rem">
              <small style="color:#ff6b6b;display:none" id="errFullName"></small>
            </div>

            <div style="margin-bottom:1.5rem">
              <label style="display:block;color:rgba(232,236,255,0.7);font-size:0.875rem;margin-bottom:0.5rem">Phone Number</label>
              <input type="tel" id="editPhone" value="${user.phone_number || ''}" placeholder="+84 123 456 789" style="width:100%;padding:0.75rem;background:#1a2332;border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;color:#e8ecff;font-size:1rem">
              <small style="color:#ff6b6b;display:none" id="errPhone"></small>
            </div>

            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:2rem">
              <button onclick="document.getElementById('modalEditProfile').remove()" style="padding:0.625rem 1rem;background:transparent;border:1px solid rgba(255,255,255,0.2);border-radius:0.5rem;color:#e8ecff;cursor:pointer;font-weight:500">
                Cancel
              </button>
              <button onclick="submitEditProfile()" style="padding:0.625rem 1rem;background:#3b82f6;border:none;border-radius:0.5rem;color:#fff;cursor:pointer;font-weight:500">
                Save Changes
              </button>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', html);
    }

    async function submitEditProfile() {
      const fullName = document.getElementById('editFullName').value.trim();
      const phone = document.getElementById('editPhone').value.trim();

      // Clear errors
      document.getElementById('errFullName').style.display = 'none';
      document.getElementById('errPhone').style.display = 'none';

      // Validate
      if (!fullName) {
        document.getElementById('errFullName').textContent = 'Full name is required';
        document.getElementById('errFullName').style.display = 'block';
        return;
      }

      try {
        const res = await apiFetch('/api/auth/profile', {
          method: 'PUT',
          body: JSON.stringify({
            full_name: fullName,
            phone_number: phone || null
          })
        });

        if (!res) return;

        const data = await res.json();

        if (res.status === 422 && data.errors) {
          if (data.errors.full_name) {
            document.getElementById('errFullName').textContent = data.errors.full_name[0];
            document.getElementById('errFullName').style.display = 'block';
          }
          if (data.errors.phone_number) {
            document.getElementById('errPhone').textContent = data.errors.phone_number[0];
            document.getElementById('errPhone').style.display = 'block';
          }
          return;
        }

        // Success: update UI
        const updatedUser = data.data?.user || data.data || {};
        document.getElementById('name').textContent = updatedUser.full_name || fullName;
        document.getElementById('fullName').textContent = updatedUser.full_name || fullName;
        document.getElementById('phone').textContent = updatedUser.phone_number || 'Not provided';

        // Update localStorage
        const currentUser = JSON.parse(localStorage.getItem(USER_KEY) || '{}');
        currentUser.full_name = updatedUser.full_name || fullName;
        currentUser.phone_number = updatedUser.phone_number || null;
        localStorage.setItem(USER_KEY, JSON.stringify(currentUser));

        // Close modal
        document.getElementById('modalEditProfile').remove();

        // Show success
        alert('Profile updated successfully!');
      } catch (err) {
        console.error('Error updating profile:', err);
        alert('Failed to update profile. Please try again.');
      }
    }

    // Load on page ready
    loadProfile();
  </script>
</body>
</html>


