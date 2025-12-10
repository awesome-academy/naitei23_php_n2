<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        h2 { color: #555; font-size: 18px; margin-bottom: 15px; }
        .info { color: #666; font-size: 14px; }
        .btn { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #000; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        .response { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 15px; max-height: 400px; overflow-y: auto; }
        .response pre { white-space: pre-wrap; word-wrap: break-word; font-size: 12px; }
        .loading { color: #007bff; font-style: italic; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .token-info { background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; }
        .list-item { background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .list-item-actions button { margin-left: 5px; padding: 5px 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 API Testing Dashboard</h1>
            <div class="info">User: <strong>{{ auth()->user()->email ?? 'Not logged in' }}</strong></div>
            <div class="token-info">
                <strong>Auth Token:</strong> <span id="currentToken">{{ session('api_token', 'Not set') }}</span>
                <button class="btn btn-warning" onclick="refreshToken()">🔄 Refresh Token</button>
            </div>
        </div>

        <div class="grid">
            <!-- Venues Section -->
            <div class="card">
                <h2>📍 Venues Management</h2>

                <button class="btn" onclick="getVenues()">📋 Get All Venues</button>
                <button class="btn btn-success" onclick="showCreateVenue()">➕ Create Venue</button>

                <div id="createVenueForm" style="display:none; margin-top: 15px;">
                    <h3 style="margin-bottom: 10px;">Create New Venue</h3>
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" id="venueName" placeholder="Coworking Space">
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea id="venueDesc" rows="2" placeholder="Modern workspace..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <input type="text" id="venueAddress" placeholder="123 Street Name">
                    </div>
                    <div class="form-group">
                        <label>City:</label>
                        <input type="text" id="venueCity" placeholder="Hanoi">
                    </div>
                    <button class="btn btn-success" onclick="createVenue()">✅ Submit</button>
                    <button class="btn" onclick="hideCreateVenue()">❌ Cancel</button>
                </div>

                <div id="venuesList" style="margin-top: 15px;"></div>
                <div id="venueResponse" class="response" style="display:none;"></div>
            </div>

            <!-- Spaces Section -->
            <div class="card">
                <h2>🏢 Spaces Management</h2>

                <div class="form-group">
                    <label>Select Venue:</label>
                    <select id="venueSelect" onchange="getSpaces()">
                        <option value="">-- Select Venue --</option>
                    </select>
                </div>

                <button class="btn" onclick="getSpaces()">📋 Get Spaces</button>
                <button class="btn btn-success" onclick="showCreateSpace()">➕ Create Space</button>

                <div id="createSpaceForm" style="display:none; margin-top: 15px;">
                    <h3 style="margin-bottom: 10px;">Create New Space</h3>
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" id="spaceName" placeholder="Meeting Room A">
                    </div>
                    <div class="form-group">
                        <label>Capacity:</label>
                        <input type="number" id="spaceCapacity" placeholder="10">
                    </div>
                    <div class="form-group">
                        <label>Price/Hour:</label>
                        <input type="number" id="spacePriceHour" placeholder="50000">
                    </div>
                    <button class="btn btn-success" onclick="createSpace()">✅ Submit</button>
                    <button class="btn" onclick="hideCreateSpace()">❌ Cancel</button>
                </div>

                <div id="spacesList" style="margin-top: 15px;"></div>
                <div id="spaceResponse" class="response" style="display:none;"></div>
            </div>
        </div>

        <!-- Amenities & Managers -->
        <div class="grid" style="margin-top: 20px;">
            <div class="card">
                <h2>⭐ Amenities</h2>
                <button class="btn" onclick="getAmenities()">📋 Get All Amenities</button>
                <div id="amenitiesResponse" class="response" style="display:none;"></div>
            </div>

            <div class="card">
                <h2>👥 Managers</h2>
                <button class="btn" onclick="getManagers()">📋 Get Managers</button>
                <button class="btn btn-success" onclick="loadAvailableManagers()">➕ Add Manager</button>

                <div id="addManagerForm" style="display:none; margin-top: 15px;">
                    <div class="form-group">
                        <label>Select Manager:</label>
                        <select id="managerSelect">
                            <option value="">-- Loading... --</option>
                        </select>
                        <button class="btn btn-success" onclick="addManager()" style="margin-top: 10px;">✅ Add</button>
                        <button class="btn" onclick="hideAddManagerForm()">❌ Cancel</button>
                    </div>
                </div>

                <div id="managersList" style="margin-top: 15px;"></div>
                <div id="managersResponse" class="response" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api/owner';
        let currentToken = '{{ session("api_token") }}';

        async function refreshToken() {
            try {
                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: '{{ auth()->user()->email }}', password: 'password' })
                });
                const data = await res.json();
                if (data.access_token) {
                    currentToken = data.access_token;
                    document.getElementById('currentToken').textContent = currentToken.substring(0, 30) + '...';
                    alert('Token refreshed!');
                }
            } catch (err) {
                alert('Failed to refresh token');
            }
        }

        async function apiRequest(endpoint, method = 'GET', body = null) {
            const options = {
                method,
                headers: {
                    'Authorization': `Bearer ${currentToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };
            if (body) options.body = JSON.stringify(body);

            const res = await fetch(API_BASE + endpoint, options);
            return await res.json();
        }

        async function getVenues() {
            document.getElementById('venueResponse').style.display = 'block';
            document.getElementById('venueResponse').innerHTML = '<div class="loading">Loading...</div>';

            try {
                const data = await apiRequest('/venues');
                document.getElementById('venueResponse').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;

                // Handle different response formats (pagination: data.data)
                let venues = [];
                if (Array.isArray(data)) {
                    venues = data;
                } else if (data.data && Array.isArray(data.data)) {
                    venues = data.data;
                }

                // Populate venue select
                const select = document.getElementById('venueSelect');
                select.innerHTML = '<option value="">-- Select Venue --</option>';
                if (venues.length > 0) {
                    venues.forEach(v => {
                        select.innerHTML += `<option value="${v.id}">${v.name}</option>`;
                    });
                }

                // Show venues list
                const list = document.getElementById('venuesList');
                list.innerHTML = '<h3 style="margin: 10px 0;">Your Venues:</h3>';
                if (venues.length > 0) {
                    venues.forEach(v => {
                        list.innerHTML += `
                            <div class="list-item">
                                <div><strong>${v.name}</strong> - ${v.city || 'N/A'}</div>
                                <div class="list-item-actions">
                                    <button class="btn" onclick="editVenue(${v.id})">✏️ Edit</button>
                                    <button class="btn btn-danger" onclick="deleteVenue(${v.id})">🗑️ Delete</button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    list.innerHTML += '<div class="info">No venues found.</div>';
                }
            } catch (err) {
                document.getElementById('venueResponse').innerHTML = `<div class="error">${err.message}</div>`;
            }
        }

        async function createVenue() {
            const body = {
                name: document.getElementById('venueName').value,
                description: document.getElementById('venueDesc').value,
                address: document.getElementById('venueAddress').value,
                city: document.getElementById('venueCity').value,
                street: document.getElementById('venueAddress').value,
                latitude: 21.0285,
                longitude: 105.8542
            };

            try {
                const data = await apiRequest('/venues', 'POST', body);
                document.getElementById('venueResponse').style.display = 'block';
                document.getElementById('venueResponse').innerHTML = `<pre class="success">${JSON.stringify(data, null, 2)}</pre>`;
                hideCreateVenue();
                getVenues();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function deleteVenue(id) {
            if (!confirm('Delete this venue?')) return;
            try {
                const data = await apiRequest(`/venues/${id}`, 'DELETE');
                alert('Venue deleted!');
                getVenues();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function getSpaces() {
            const venueId = document.getElementById('venueSelect').value;
            if (!venueId) return alert('Please select a venue first!');

            document.getElementById('spaceResponse').style.display = 'block';
            document.getElementById('spaceResponse').innerHTML = '<div class="loading">Loading...</div>';

            try {
                const data = await apiRequest(`/venues/${venueId}/spaces`);
                document.getElementById('spaceResponse').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;

                // Show spaces list
                const list = document.getElementById('spacesList');
                list.innerHTML = '<h3 style="margin: 10px 0;">Spaces:</h3>';

                // Handle different response formats (pagination: data.data.data)
                let spaces = [];
                if (Array.isArray(data)) {
                    spaces = data;
                } else if (data.data && data.data.data && Array.isArray(data.data.data)) {
                    spaces = data.data.data;
                } else if (data.data && Array.isArray(data.data)) {
                    spaces = data.data;
                }

                if (spaces.length > 0) {
                    spaces.forEach(s => {
                        list.innerHTML += `
                            <div class="list-item">
                                <div><strong>${s.name}</strong> - Capacity: ${s.capacity} | Price/hour: ${parseFloat(s.price_per_hour).toLocaleString()} VND</div>
                                <div class="list-item-actions">
                                    <button class="btn" onclick="editSpace(${venueId}, ${s.id})">✏️ Edit</button>
                                    <button class="btn btn-danger" onclick="deleteSpace(${venueId}, ${s.id})">🗑️ Delete</button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    list.innerHTML += '<div class="info">No spaces found for this venue.</div>';
                }
            } catch (err) {
                document.getElementById('spaceResponse').innerHTML = `<div class="error">${err.message}</div>`;
            }
        }

        async function createSpace() {
            const venueId = document.getElementById('venueSelect').value;
            if (!venueId) return alert('Please select a venue first!');

            const body = {
                name: document.getElementById('spaceName').value,
                capacity: parseInt(document.getElementById('spaceCapacity').value),
                price_per_hour: parseFloat(document.getElementById('spacePriceHour').value),
                space_type_id: 1, // Default to Meeting Room
                open_hour: '08:00',
                close_hour: '22:00'
            };

            try {
                const data = await apiRequest(`/venues/${venueId}/spaces`, 'POST', body);
                document.getElementById('spaceResponse').style.display = 'block';
                document.getElementById('spaceResponse').innerHTML = `<pre class="success">${JSON.stringify(data, null, 2)}</pre>`;
                hideCreateSpace();
                getSpaces();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function deleteSpace(venueId, spaceId) {
            if (!confirm('Delete this space?')) return;
            try {
                const data = await apiRequest(`/venues/${venueId}/spaces/${spaceId}`, 'DELETE');
                alert('Space deleted!');
                getSpaces();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function getAmenities() {
            document.getElementById('amenitiesResponse').style.display = 'block';
            document.getElementById('amenitiesResponse').innerHTML = '<div class="loading">Loading...</div>';

            try {
                // Amenities endpoint is public (no /owner prefix)
                const res = await fetch('/api/amenities', {
                    headers: {
                        'Authorization': `Bearer ${currentToken}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                document.getElementById('amenitiesResponse').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
            } catch (err) {
                document.getElementById('amenitiesResponse').innerHTML = `<div class="error">${err.message}</div>`;
            }
        }

        async function getManagers() {
            const venueId = document.getElementById('venueSelect').value;
            if (!venueId) return alert('Please select a venue first!');

            document.getElementById('managersResponse').style.display = 'block';
            document.getElementById('managersResponse').innerHTML = '<div class="loading">Loading...</div>';

            try {
                const data = await apiRequest(`/venues/${venueId}/managers`);
                document.getElementById('managersResponse').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;

                // Show managers list
                const list = document.getElementById('managersList');
                list.innerHTML = '<h3 style="margin: 10px 0;">Current Managers:</h3>';

                const managers = Array.isArray(data) ? data : (data.data || []);
                if (managers.length > 0) {
                    managers.forEach(m => {
                        list.innerHTML += `
                            <div class="list-item">
                                <div><strong>${m.full_name}</strong> - ${m.email}</div>
                                <div class="list-item-actions">
                                    <button class="btn btn-danger" onclick="removeManager(${m.id})">🗑️ Remove</button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    list.innerHTML += '<div class="info">No managers assigned to this venue.</div>';
                }
            } catch (err) {
                document.getElementById('managersResponse').innerHTML = `<div class="error">${err.message}</div>`;
            }
        }

        async function loadAvailableManagers() {
            const venueId = document.getElementById('venueSelect').value;
            if (!venueId) return alert('Please select a venue first!');

            try {
                // Get all users (potential managers)
                const res = await fetch('/api/users', {
                    headers: {
                        'Authorization': `Bearer ${currentToken}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                const select = document.getElementById('managerSelect');
                select.innerHTML = '<option value="">-- Select Manager --</option>';

                // UserController returns data.users, not data.data
                const users = data.data?.users || data.data || [];
                users.forEach(u => {
                    // Skip admin/owner
                    if (u.email !== 'admin@workspace.com' && u.email !== 'owner@workspace.com') {
                        select.innerHTML += `<option value="${u.id}">${u.full_name} (${u.email})</option>`;
                    }
                });

                document.getElementById('addManagerForm').style.display = 'block';
            } catch (err) {
                alert('Error loading managers: ' + err.message);
            }
        }

        async function addManager() {
            const venueId = document.getElementById('venueSelect').value;
            const userId = document.getElementById('managerSelect').value;
            if (!venueId) return alert('Please select a venue first!');
            if (!userId) return alert('Please select a manager!');

            try {
                const data = await apiRequest(`/venues/${venueId}/managers`, 'POST', { user_id: parseInt(userId) });
                alert('Manager added!');
                hideAddManagerForm();
                getManagers();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function removeManager(userId) {
            const venueId = document.getElementById('venueSelect').value;
            if (!confirm('Remove this manager?')) return;

            try {
                const data = await apiRequest(`/venues/${venueId}/managers/${userId}`, 'DELETE');
                alert('Manager removed!');
                getManagers();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        function hideAddManagerForm() {
            document.getElementById('addManagerForm').style.display = 'none';
        }

        function showCreateVenue() { document.getElementById('createVenueForm').style.display = 'block'; }
        function hideCreateVenue() { document.getElementById('createVenueForm').style.display = 'none'; }
        function showCreateSpace() { document.getElementById('createSpaceForm').style.display = 'block'; }
        function hideCreateSpace() { document.getElementById('createSpaceForm').style.display = 'none'; }

        // Load venues on page load
        window.onload = () => { if (currentToken && currentToken !== '') getVenues(); };
    </script>
</body>
</html>
