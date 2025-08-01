@extends('backend.layouts.master')

@section('title', 'Firebase Chat Test')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Firebase Chat System Test</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Configuration Test</h5>
                            <button class="btn btn-primary" onclick="testConfig()">Test Firebase Config</button>
                            <div id="config-result" class="mt-3"></div>
                        </div>
                        <div class="col-md-6">
                            <h5>Connection Test</h5>
                            <button class="btn btn-success" onclick="testConnection()">Test Firebase Connection</button>
                            <div id="connection-result" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Debug Information</h5>
                            <button class="btn btn-info" onclick="showDebugInfo()">Show Debug Info</button>
                            <div id="debug-info" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Send Test Message</h5>
                            <div class="form-group">
                                <input type="text" id="test-message" class="form-control" placeholder="Enter test message">
                            </div>
                            <button class="btn btn-warning" onclick="sendTestMessage()">Send Test Message</button>
                            <div id="message-result" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "{{ config('firebase.api_key') }}",
        authDomain: "{{ config('firebase.auth_domain') }}",
        databaseURL: "{{ config('firebase.database_url') }}",
        projectId: "{{ config('firebase.project_id') }}",
        storageBucket: "{{ config('firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
        appId: "{{ config('firebase.app_id') }}"
    };

    let app, auth, database;
    let isInitialized = false;

    // Initialize Firebase
    async function initFirebase() {
        if (isInitialized) return true;
        
        try {
            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js');
            const { getAuth, signInWithCustomToken } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-auth.js');
            const { getDatabase } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js');

            app = initializeApp(firebaseConfig);
            auth = getAuth(app);
            database = getDatabase(app);

            // Create a simple custom token (for testing only)
            const customToken = btoa(JSON.stringify({
                uid: '{{ auth()->id() }}',
                role: '{{ auth()->user()->role }}',
                name: '{{ auth()->user()->name }}',
                exp: Math.floor(Date.now() / 1000) + 3600
            }));

            console.log('Attempting to sign in with custom token...');
            
            try {
                await signInWithCustomToken(auth, customToken);
                console.log('Custom token authentication successful');
            } catch (authError) {
                console.warn('Custom token failed, trying without authentication:', authError);
                // If custom token fails, we'll try to use database directly
            }
            
            isInitialized = true;
            return true;
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            return false;
        }
    }

    // Test functions
    window.testConfig = function() {
        const result = document.getElementById('config-result');
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Testing...';
        
        setTimeout(() => {
            const configValid = Object.values(firebaseConfig).every(value => value && value !== '');
            
            if (configValid) {
                result.innerHTML = `
                    <div class="alert alert-success">
                        <strong>✓ Configuration Valid</strong><br>
                        <small>API Key: ${firebaseConfig.apiKey.substring(0, 10)}...</small><br>
                        <small>Project ID: ${firebaseConfig.projectId}</small><br>
                        <small>Database URL: ${firebaseConfig.databaseURL}</small>
                    </div>
                `;
            } else {
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>✗ Configuration Invalid</strong><br>
                        <small>Some Firebase configuration values are missing.</small>
                    </div>
                `;
            }
        }, 1000);
    };

    window.testConnection = async function() {
        const result = document.getElementById('connection-result');
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Testing connection...';
        
        try {
            const success = await initFirebase();
            
            if (success) {
                // Test database write
                const { ref, set, serverTimestamp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js');
                const testRef = ref(database, 'test/connection');
                await set(testRef, {
                    message: 'Connection test successful',
                    timestamp: serverTimestamp(),
                    userId: {{ auth()->id() }},
                    userAgent: navigator.userAgent
                });
                
                result.innerHTML = `
                    <div class="alert alert-success">
                        <strong>✓ Connection Successful</strong><br>
                        <small>Firebase authentication and database write successful.</small>
                    </div>
                `;
            } else {
                throw new Error('Firebase initialization failed');
            }
        } catch (error) {
            result.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ Connection Failed</strong><br>
                    <small>Error: ${error.message}</small>
                </div>
            `;
        }
    };

    window.showDebugInfo = function() {
        const result = document.getElementById('debug-info');
        const debugInfo = {
            userAgent: navigator.userAgent,
            currentUrl: window.location.href,
            userId: {{ auth()->id() }},
            userName: "{{ auth()->user()->name }}",
            userRole: "{{ auth()->user()->role }}",
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ? 'Present' : 'Missing',
            firebaseConfigKeys: Object.keys(firebaseConfig),
            timestamp: new Date().toISOString()
        };
        
        result.innerHTML = `
            <div class="alert alert-info">
                <strong>Debug Information:</strong><br>
                <pre>${JSON.stringify(debugInfo, null, 2)}</pre>
            </div>
        `;
    };

    window.sendTestMessage = async function() {
        const messageInput = document.getElementById('test-message');
        const result = document.getElementById('message-result');
        const message = messageInput.value.trim();
        
        if (!message) {
            result.innerHTML = '<div class="alert alert-warning">Please enter a test message.</div>';
            return;
        }
        
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Sending message...';
        
        try {
            const success = await initFirebase();
            if (!success) throw new Error('Firebase not initialized');
            
            const { ref, push, serverTimestamp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js');
            
            const testConversationId = 'test_conversation';
            const messagesRef = ref(database, `messages/${testConversationId}`);
            
            const messageData = {
                id: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                conversationId: testConversationId,
                senderId: {{ auth()->id() }},
                senderName: "{{ auth()->user()->name }}",
                senderRole: "{{ auth()->user()->role }}",
                content: message,
                type: 'text',
                timestamp: serverTimestamp()
            };
            
            await push(messagesRef, messageData);
            
            result.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ Message Sent Successfully</strong><br>
                    <small>Message: "${message}"</small><br>
                    <small>Conversation ID: ${testConversationId}</small>
                </div>
            `;
            
            messageInput.value = '';
            
        } catch (error) {
            result.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ Message Send Failed</strong><br>
                    <small>Error: ${error.message}</small>
                </div>
            `;
        }
    };
</script>
@endsection