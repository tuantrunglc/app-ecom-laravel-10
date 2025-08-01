@extends('backend.layouts.master')

@section('title', 'Firebase Simple Test')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Firebase Simple Connection Test</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Note:</strong> This test bypasses authentication and tests direct database access.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Configuration Check</h5>
                            <button class="btn btn-primary" onclick="checkConfig()">Check Config</button>
                            <div id="config-result" class="mt-3"></div>
                        </div>
                        <div class="col-md-6">
                            <h5>Database Test</h5>
                            <button class="btn btn-success" onclick="testDatabase()">Test Database</button>
                            <div id="database-result" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Firebase Rules Check</h5>
                            <div class="alert alert-warning">
                                <strong>Important:</strong> Make sure your Firebase Realtime Database rules allow read/write access:
                                <pre>{
  "rules": {
    ".read": true,
    ".write": true
  }
}</pre>
                            </div>
                            <button class="btn btn-warning" onclick="testRules()">Test Rules</button>
                            <div id="rules-result" class="mt-3"></div>
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

    console.log('Firebase Config:', firebaseConfig);

    let app, database;

    window.checkConfig = function() {
        const result = document.getElementById('config-result');
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Checking...';
        
        setTimeout(() => {
            const issues = [];
            
            if (!firebaseConfig.apiKey) issues.push('API Key missing');
            if (!firebaseConfig.databaseURL) issues.push('Database URL missing');
            if (!firebaseConfig.projectId) issues.push('Project ID missing');
            
            if (issues.length === 0) {
                result.innerHTML = `
                    <div class="alert alert-success">
                        <strong>✓ Configuration OK</strong><br>
                        <small>Project: ${firebaseConfig.projectId}</small><br>
                        <small>Database: ${firebaseConfig.databaseURL}</small>
                    </div>
                `;
            } else {
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>✗ Configuration Issues:</strong><br>
                        ${issues.map(issue => `<small>• ${issue}</small>`).join('<br>')}
                    </div>
                `;
            }
        }, 500);
    };

    window.testDatabase = async function() {
        const result = document.getElementById('database-result');
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Testing database...';
        
        try {
            // Initialize Firebase
            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js');
            const { getDatabase, ref, set, get, serverTimestamp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js');

            if (!app) {
                app = initializeApp(firebaseConfig);
                database = getDatabase(app);
            }

            console.log('Firebase app initialized');

            // Test write
            const testRef = ref(database, 'test/simple');
            const testData = {
                message: 'Simple test successful',
                timestamp: Date.now(),
                userId: {{ auth()->id() }},
                userName: "{{ auth()->user()->name }}"
            };

            console.log('Attempting to write data:', testData);
            await set(testRef, testData);
            console.log('Write successful');

            // Test read
            const snapshot = await get(testRef);
            const readData = snapshot.val();
            console.log('Read data:', readData);

            result.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ Database Test Successful</strong><br>
                    <small>Write: OK</small><br>
                    <small>Read: OK</small><br>
                    <small>Data: ${JSON.stringify(readData, null, 2)}</small>
                </div>
            `;

        } catch (error) {
            console.error('Database test failed:', error);
            result.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ Database Test Failed</strong><br>
                    <small>Error: ${error.message}</small><br>
                    <small>Code: ${error.code || 'Unknown'}</small>
                </div>
            `;
        }
    };

    window.testRules = async function() {
        const result = document.getElementById('rules-result');
        result.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Testing rules...';
        
        try {
            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js');
            const { getDatabase, ref, set, get } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js');

            if (!app) {
                app = initializeApp(firebaseConfig);
                database = getDatabase(app);
            }

            // Test different paths
            const tests = [
                { path: 'test/rules', name: 'Test Path' },
                { path: 'messages/test_conv', name: 'Messages Path' },
                { path: 'conversations/test_conv', name: 'Conversations Path' },
                { path: 'userPresence/{{ auth()->id() }}', name: 'User Presence Path' }
            ];

            const results = [];

            for (const test of tests) {
                try {
                    const testRef = ref(database, test.path);
                    await set(testRef, { test: true, timestamp: Date.now() });
                    const snapshot = await get(testRef);
                    
                    if (snapshot.exists()) {
                        results.push(`✓ ${test.name}: OK`);
                    } else {
                        results.push(`⚠ ${test.name}: Write OK, Read failed`);
                    }
                } catch (error) {
                    results.push(`✗ ${test.name}: ${error.message}`);
                }
            }

            result.innerHTML = `
                <div class="alert alert-info">
                    <strong>Rules Test Results:</strong><br>
                    ${results.map(r => `<small>${r}</small>`).join('<br>')}
                </div>
            `;

        } catch (error) {
            result.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ Rules Test Failed</strong><br>
                    <small>Error: ${error.message}</small>
                </div>
            `;
        }
    };
</script>
@endsection