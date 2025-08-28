<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Firebase App')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .auth-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-test {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            color: white;
            border: none;
            border-radius: 8px;
        }
        
        .btn-test:hover {
            background: linear-gradient(45deg, #f5576c, #f093fb);
            color: white;
            transform: translateY(-1px);
        }
        
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        pre code {
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.0.0/firebase-app.js';
        import { 
            getAuth, 
            signInWithEmailAndPassword, 
            createUserWithEmailAndPassword, 
            signOut, 
            onAuthStateChanged,
            updateProfile,
            sendEmailVerification,
            sendPasswordResetEmail
        } from 'https://www.gstatic.com/firebasejs/10.0.0/firebase-auth.js';

        // Firebase configuration from environment variables
        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_API_KEY', 'demo-api-key') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', 'demo.firebaseapp.com') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID', 'demo-project') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', 'demo.appspot.com') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '123456789') }}",
            appId: "{{ env('FIREBASE_APP_ID', 'demo-app-id') }}"
        };

        console.log('Firebase Config:', firebaseConfig);

        // Initialize Firebase
        try {
            const app = initializeApp(firebaseConfig);
            const auth = getAuth(app);

            // Make Firebase Auth available globally
            window.firebaseAuth = auth;
            window.signInWithEmailAndPassword = signInWithEmailAndPassword;
            window.createUserWithEmailAndPassword = createUserWithEmailAndPassword;
            window.updateProfile = updateProfile;
            window.sendEmailVerification = sendEmailVerification;
            window.sendPasswordResetEmail = sendPasswordResetEmail;
            window.signOut = signOut;
            window.onAuthStateChanged = onAuthStateChanged;
            
            console.log('✅ Firebase initialized successfully');
        } catch (error) {
            console.error('❌ Firebase initialization failed:', error);
            window.firebaseAuth = null;
        }
    </script>
    
    @stack('scripts')
</body>
</html>