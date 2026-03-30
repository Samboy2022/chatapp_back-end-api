<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API Testing Dashboard - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
        }
        .status-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-item {
            text-align: center;
        }
        .status-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .status-value {
            font-size: 18px;
            font-weight: bold;
        }
        .status-value.connected { color: #10b981; }
        .status-value.disconnected { color: #ef4444; }
        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-info {
            background: #3b82f6;
            color: white;
        }
        .result-box {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            max-height: 400px;
            overflow-y: auto;
        }
        .result-box pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 12px;
            color: #333;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .tab {
            padding: 10px 20px;
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .tab.active {
            background: #667eea;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box strong {
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 API Testing Dashboard</h1>
            <p>Test Stream Video Token Generation & Media Upload APIs</p>
            <p style="margin-top: 10px; font-size: 14px;">
                <strong>Laravel Backend:</strong> {{ config('app.name') }} | 
                <strong>Environment:</strong> {{ config('app.env') }}
            </p>
        </div>

        <div class="status-bar">
            <div class="status-item">
                <div class="status-label">API Status</div>
                <div class="status-value connected" id="apiStatus">Online</div>
            </div>
            <div class="status-item">
                <div class="status-label">Auth Status</div>
                <div class="status-value disconnected" id="authStatus">Not Logged In</div>
            </div>
            <div class="status-item">
                <div class="status-label">Stream Token</div>
                <div class="status-value disconnected" id="streamStatus">No Token</div>
            </div>
            <div class="status-item">
                <div class="status-label">Total Users</div>
                <div class="status-value" style="color: #667eea;">{{ $stats['total_users'] }}</div>
            </div>
        </div>

        <div class="card">
            <h2>🔐 Authentication</h2>
            <div class="info-box">
                <strong>Test Credentials:</strong> streamtest@example.com / password123
            </div>
            <div class="form-group">
                <label>API Base URL</label>
                <input type="text" id="apiUrl" value="{{ $stats['api_url'] }}" placeholder="http://localhost:8000">
            </div>
            <div class="grid">
                <div class="form-group">
                    <label>Email or Phone</label>
                    <input type="text" id="loginEmail" value="streamtest@example.com" placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="loginPassword" value="password123" placeholder="password">
                </div>
            </div>
            <button class="btn btn-primary" onclick="login()">Login</button>
            <button class="btn btn-danger" onclick="logout()">Logout</button>
            <button class="btn btn-info" onclick="getUser()">Get User Info</button>
            <div class="result-box" id="authResult"></div>
        </div>

        <div class="card">
            <h2>🎬 Stream Video Token Tests</h2>
            @if($stats['stream_configured'])
                <div class="info-box">
                    <strong>✅ Stream Configured:</strong> API Key: {{ $stats['stream_api_key'] }}
                </div>
            @else
                <div class="info-box" style="background: #fef2f2; border-left-color: #ef4444;">
                    <strong style="color: #991b1b;">❌ Stream Not Configured:</strong> Please set STREAM_API_KEY and STREAM_API_SECRET in .env
                </div>
            @endif
            
            <div class="tabs">
                <button class="tab active" onclick="switchTab('stream', 0)">Generate Token</button>
                <button class="tab" onclick="switchTab('stream', 1)">Get Config</button>
                <button class="tab" onclick="switchTab('stream', 2)">Validate Credentials</button>
            </div>

            <div class="tab-content active" id="stream-0">
                <p>Generate a Stream Video token for the authenticated user</p>
                <div class="form-group">
                    <label>Call ID (Optional)</label>
                    <input type="text" id="callId" placeholder="test-call-123">
                </div>
                <div class="form-group">
                    <label>Room ID (Optional)</label>
                    <input type="text" id="roomId" placeholder="test-room-456">
                </div>
                <button class="btn btn-success" onclick="generateStreamToken()">Generate Token</button>
                <div class="result-box" id="streamTokenResult"></div>
            </div>

            <div class="tab-content" id="stream-1">
                <p>Get Stream Video configuration</p>
                <button class="btn btn-success" onclick="getStreamConfig()">Get Config</button>
                <div class="result-box" id="streamConfigResult"></div>
            </div>

            <div class="tab-content" id="stream-2">
                <p>Validate Stream API credentials</p>
                <button class="btn btn-success" onclick="validateStreamCredentials()">Validate</button>
                <div class="result-box" id="streamValidateResult"></div>
            </div>
        </div>

        <div class="card">
            <h2>📁 Media Upload Tests</h2>
            <div class="tabs">
                <button class="tab active" onclick="switchTab('media', 0)">Image Upload</button>
                <button class="tab" onclick="switchTab('media', 1)">Avatar Upload</button>
                <button class="tab" onclick="switchTab('media', 2)">Document Upload</button>
                <button class="tab" onclick="switchTab('media', 3)">Audio Upload</button>
                <button class="tab" onclick="switchTab('media', 4)">Status Media</button>
            </div>

            <div class="tab-content active" id="media-0">
                <p>Upload an image file</p>
                <div class="form-group">
                    <label>Select Image</label>
                    <input type="file" id="imageFile" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Chat ID (Optional)</label>
                    <input type="text" id="imageChatId" placeholder="1">
                </div>
                <button class="btn btn-success" onclick="uploadImage()">Upload Image</button>
                <div class="result-box" id="imageUploadResult"></div>
            </div>

            <div class="tab-content" id="media-1">
                <p>Upload user avatar</p>
                <div class="form-group">
                    <label>Select Avatar Image</label>
                    <input type="file" id="avatarFile" accept="image/*">
                </div>
                <button class="btn btn-success" onclick="uploadAvatar()">Upload Avatar</button>
                <div class="result-box" id="avatarUploadResult"></div>
            </div>

            <div class="tab-content" id="media-2">
                <p>Upload a document file</p>
                <div class="form-group">
                    <label>Select Document</label>
                    <input type="file" id="documentFile" accept=".pdf,.doc,.docx,.txt">
                </div>
                <button class="btn btn-success" onclick="uploadDocument()">Upload Document</button>
                <div class="result-box" id="documentUploadResult"></div>
            </div>

            <div class="tab-content" id="media-3">
                <p>Upload an audio file</p>
                <div class="form-group">
                    <label>Select Audio</label>
                    <input type="file" id="audioFile" accept="audio/*">
                </div>
                <button class="btn btn-success" onclick="uploadAudio()">Upload Audio</button>
                <div class="result-box" id="audioUploadResult"></div>
            </div>

            <div class="tab-content" id="media-4">
                <p>Upload status media (image or video)</p>
                <div class="form-group">
                    <label>Select Media</label>
                    <input type="file" id="statusFile" accept="image/*,video/*">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select id="statusType">
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <button class="btn btn-success" onclick="uploadStatusMedia()">Upload Status Media</button>
                <div class="result-box" id="statusUploadResult"></div>
            </div>
        </div>

        <div class="card">
            <h2>🧪 Quick Tests</h2>
            <p>Run all tests at once</p>
            <button class="btn btn-primary" onclick="runAllTests()">Run All Tests</button>
            <button class="btn btn-info" onclick="clearAllResults()">Clear Results</button>
            <div class="result-box" id="quickTestResult"></div>
        </div>
    </div>

    <script>
        let authToken = localStorage.getItem('authToken') || '';
        let apiBaseUrl = '{{ $stats['api_url'] }}';

        // Initialize
        window.onload = function() {
            const savedUrl = localStorage.getItem('apiUrl');
            if (savedUrl) {
                document.getElementById('apiUrl').value = savedUrl;
                apiBaseUrl = savedUrl;
            }
            if (authToken) {
                updateAuthStatus('connected', 'Logged In');
            }
        };

        // Tab switching
        function switchTab(section, index) {
            const card = section === 'stream' ? 2 : 3;
            const tabs = document.querySelectorAll(`.card:nth-child(${card}) .tab`);
            const contents = document.querySelectorAll(`[id^="${section}-"]`);
            
            tabs.forEach((tab, i) => {
                tab.classList.toggle('active', i === index);
            });
            
            contents.forEach((content, i) => {
                content.classList.toggle('active', i === index);
            });
        }

        // Update status
        function updateAuthStatus(status, text) {
            const el = document.getElementById('authStatus');
            el.className = 'status-value ' + status;
            el.textContent = text;
        }

        function updateStreamStatus(status, text) {
            const el = document.getElementById('streamStatus');
            el.className = 'status-value ' + status;
            el.textContent = text;
        }

        function updateApiStatus(status, text) {
            const el = document.getElementById('apiStatus');
            el.className = 'status-value ' + status;
            el.textContent = text;
        }

        // Display result
        function displayResult(elementId, data, isError = false) {
            const el = document.getElementById(elementId);
            const className = isError ? 'error' : 'success';
            el.innerHTML = `<pre class="${className}">${JSON.stringify(data, null, 2)}</pre>`;
        }

        // Login
        async function login() {
            apiBaseUrl = document.getElementById('apiUrl').value;
            localStorage.setItem('apiUrl', apiBaseUrl);
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            try {
                displayResult('authResult', { status: 'Logging in...' });
                
                const response = await fetch(`${apiBaseUrl}/api/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        login: email,
                        password: password
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    authToken = data.data.token;
                    localStorage.setItem('authToken', authToken);
                    updateAuthStatus('connected', 'Logged In');
                    updateApiStatus('connected', 'Connected');
                    displayResult('authResult', data);
                } else {
                    updateAuthStatus('disconnected', 'Login Failed');
                    displayResult('authResult', data, true);
                }
            } catch (error) {
                updateAuthStatus('disconnected', 'Error');
                displayResult('authResult', { error: error.message }, true);
            }
        }

        // Logout
        async function logout() {
            try {
                const response = await fetch(`${apiBaseUrl}/api/auth/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                authToken = '';
                localStorage.removeItem('authToken');
                updateAuthStatus('disconnected', 'Logged Out');
                updateStreamStatus('disconnected', 'No Token');
                displayResult('authResult', { message: 'Logged out successfully' });
            } catch (error) {
                displayResult('authResult', { error: error.message }, true);
            }
        }

        // Get User
        async function getUser() {
            if (!authToken) {
                displayResult('authResult', { error: 'Please login first' }, true);
                return;
            }

            try {
                const response = await fetch(`${apiBaseUrl}/api/auth/user`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                displayResult('authResult', data, !response.ok);
            } catch (error) {
                displayResult('authResult', { error: error.message }, true);
            }
        }

        // Generate Stream Token
        async function generateStreamToken() {
            if (!authToken) {
                displayResult('streamTokenResult', { error: 'Please login first' }, true);
                return;
            }

            const callId = document.getElementById('callId').value;
            const roomId = document.getElementById('roomId').value;

            try {
                displayResult('streamTokenResult', { status: 'Generating token...' });

                const response = await fetch(`${apiBaseUrl}/api/stream/token`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        call_id: callId || undefined,
                        room_id: roomId || undefined
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    updateStreamStatus('connected', 'Token Generated');
                    
                    // Decode JWT to show payload
                    const token = data.data.token;
                    const parts = token.split('.');
                    if (parts.length === 3) {
                        const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
                        data.decoded_payload = payload;
                        data.user_id_type = typeof payload.user_id;
                    }
                    
                    displayResult('streamTokenResult', data);
                } else {
                    updateStreamStatus('disconnected', 'Failed');
                    displayResult('streamTokenResult', data, true);
                }
            } catch (error) {
                updateStreamStatus('disconnected', 'Error');
                displayResult('streamTokenResult', { error: error.message }, true);
            }
        }

        // Get Stream Config
        async function getStreamConfig() {
            if (!authToken) {
                displayResult('streamConfigResult', { error: 'Please login first' }, true);
                return;
            }

            try {
                displayResult('streamConfigResult', { status: 'Fetching config...' });

                const response = await fetch(`${apiBaseUrl}/api/stream/config`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                displayResult('streamConfigResult', data, !response.ok);
            } catch (error) {
                displayResult('streamConfigResult', { error: error.message }, true);
            }
        }

        // Validate Stream Credentials
        async function validateStreamCredentials() {
            if (!authToken) {
                displayResult('streamValidateResult', { error: 'Please login first' }, true);
                return;
            }

            try {
                displayResult('streamValidateResult', { status: 'Validating...' });

                const response = await fetch(`${apiBaseUrl}/api/stream/validate`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                displayResult('streamValidateResult', data, !response.ok);
            } catch (error) {
                displayResult('streamValidateResult', { error: error.message }, true);
            }
        }

        // Upload Image
        async function uploadImage() {
            if (!authToken) {
                displayResult('imageUploadResult', { error: 'Please login first' }, true);
                return;
            }

            const fileInput = document.getElementById('imageFile');
            const chatId = document.getElementById('imageChatId').value;

            if (!fileInput.files[0]) {
                displayResult('imageUploadResult', { error: 'Please select a file' }, true);
                return;
            }

            try {
                displayResult('imageUploadResult', { status: 'Uploading...' });

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('type', 'image');
                if (chatId) formData.append('chat_id', chatId);

                const response = await fetch(`${apiBaseUrl}/api/media/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                displayResult('imageUploadResult', data, !response.ok);
            } catch (error) {
                displayResult('imageUploadResult', { error: error.message }, true);
            }
        }

        // Upload Avatar
        async function uploadAvatar() {
            if (!authToken) {
                displayResult('avatarUploadResult', { error: 'Please login first' }, true);
                return;
            }

            const fileInput = document.getElementById('avatarFile');

            if (!fileInput.files[0]) {
                displayResult('avatarUploadResult', { error: 'Please select a file' }, true);
                return;
            }

            try {
                displayResult('avatarUploadResult', { status: 'Uploading...' });

                const formData = new FormData();
                formData.append('avatar', fileInput.files[0]);

                const response = await fetch(`${apiBaseUrl}/api/media/upload/avatar`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                displayResult('avatarUploadResult', data, !response.ok);
            } catch (error) {
                displayResult('avatarUploadResult', { error: error.message }, true);
            }
        }

        // Upload Document
        async function uploadDocument() {
            if (!authToken) {
                displayResult('documentUploadResult', { error: 'Please login first' }, true);
                return;
            }

            const fileInput = document.getElementById('documentFile');

            if (!fileInput.files[0]) {
                displayResult('documentUploadResult', { error: 'Please select a file' }, true);
                return;
            }

            try {
                displayResult('documentUploadResult', { status: 'Uploading...' });

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('type', 'document');

                const response = await fetch(`${apiBaseUrl}/api/media/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                displayResult('documentUploadResult', data, !response.ok);
            } catch (error) {
                displayResult('documentUploadResult', { error: error.message }, true);
            }
        }

        // Upload Audio
        async function uploadAudio() {
            if (!authToken) {
                displayResult('audioUploadResult', { error: 'Please login first' }, true);
                return;
            }

            const fileInput = document.getElementById('audioFile');

            if (!fileInput.files[0]) {
                displayResult('audioUploadResult', { error: 'Please select a file' }, true);
                return;
            }

            try {
                displayResult('audioUploadResult', { status: 'Uploading...' });

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('type', 'audio');

                const response = await fetch(`${apiBaseUrl}/api/media/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                displayResult('audioUploadResult', data, !response.ok);
            } catch (error) {
                displayResult('audioUploadResult', { error: error.message }, true);
            }
        }

        // Upload Status Media
        async function uploadStatusMedia() {
            if (!authToken) {
                displayResult('statusUploadResult', { error: 'Please login first' }, true);
                return;
            }

            const fileInput = document.getElementById('statusFile');
            const type = document.getElementById('statusType').value;

            if (!fileInput.files[0]) {
                displayResult('statusUploadResult', { error: 'Please select a file' }, true);
                return;
            }

            try {
                displayResult('statusUploadResult', { status: 'Uploading...' });

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('type', type);

                const response = await fetch(`${apiBaseUrl}/api/media/upload/status`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                displayResult('statusUploadResult', data, !response.ok);
            } catch (error) {
                displayResult('statusUploadResult', { error: error.message }, true);
            }
        }

        // Run All Tests
        async function runAllTests() {
            displayResult('quickTestResult', { status: 'Running all tests...' });
            
            const results = {
                timestamp: new Date().toISOString(),
                tests: []
            };

            // Test 1: Login
            try {
                await login();
                results.tests.push({ test: 'Login', status: 'PASS' });
            } catch (e) {
                results.tests.push({ test: 'Login', status: 'FAIL', error: e.message });
            }

            // Wait a bit
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Test 2: Stream Token
            try {
                await generateStreamToken();
                results.tests.push({ test: 'Stream Token', status: 'PASS' });
            } catch (e) {
                results.tests.push({ test: 'Stream Token', status: 'FAIL', error: e.message });
            }

            // Test 3: Stream Config
            try {
                await getStreamConfig();
                results.tests.push({ test: 'Stream Config', status: 'PASS' });
            } catch (e) {
                results.tests.push({ test: 'Stream Config', status: 'FAIL', error: e.message });
            }

            // Test 4: Validate Credentials
            try {
                await validateStreamCredentials();
                results.tests.push({ test: 'Validate Credentials', status: 'PASS' });
            } catch (e) {
                results.tests.push({ test: 'Validate Credentials', status: 'FAIL', error: e.message });
            }

            displayResult('quickTestResult', results);
        }

        // Clear All Results
        function clearAllResults() {
            const resultBoxes = document.querySelectorAll('.result-box');
            resultBoxes.forEach(box => {
                box.innerHTML = '';
            });
        }
    </script>
</body>
</html>
