@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h2 class="mb-0">
            <i class="fas fa-sign-in-alt me-2"></i>
            Welcome Back
        </h2>
        <p class="mb-0 mt-2 opacity-75">Sign in to your account</p>
    </div>
    
    <div class="card-body p-4">
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <form id="loginForm">
            @csrf
            
            <!-- Email Input -->
            <div class="form-floating mb-3">
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com" 
                       value="test@example.com"
                       required>
                <label for="email">
                    <i class="fas fa-envelope me-2"></i>Email Address
                </label>
            </div>
            
            <!-- Password Input -->
            <div class="form-floating mb-3">
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Password"
                       value="password123"
                       required>
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
            </div>
            
            <!-- Login Button -->
            <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Signing In...
                </span>
            </button>
            
            <!-- Register Link -->
            <div class="text-center">
                <span class="text-muted">Don't have an account?</span>
                <a href="{{ route('register') }}" class="text-decoration-none fw-bold">
                    Create Account
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Show/Hide loading state
    function setLoading(loading) {
        const btnText = loginBtn.querySelector('.btn-text');
        const btnSpinner = loginBtn.querySelector('.btn-spinner');
        
        if (loading) {
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            loginBtn.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            loginBtn.disabled = false;
        }
    }
    
    // Show alert message
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Handle login form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        setLoading(true);
        
        try {
            // Check if Firebase is available
            if (!window.firebaseAuth) {
                showAlert('Firebase not configured. Please check your Firebase setup.', 'warning');
                return;
            }
            
            console.log('Attempting Firebase authentication...');
            
            // Sign in with Firebase
            const userCredential = await window.signInWithEmailAndPassword(
                window.firebaseAuth, 
                emailInput.value, 
                passwordInput.value
            );
            
            console.log('Firebase auth successful:', userCredential.user.uid);
            
            // Get Firebase ID token
            const idToken = await userCredential.user.getIdToken();
            console.log('Got Firebase token, verifying with backend...');
            
            // Try to verify token with Laravel backend
            try {
                const response = await fetch('/api/verify-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ id_token: idToken })
                });
                
                if (!response.ok) {
                    throw new Error(`Backend returned ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Store token and user data
                    sessionStorage.setItem('firebase_token', idToken);
                    sessionStorage.setItem('user_data', JSON.stringify(result.user));
                    
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    showAlert('Backend verification failed: ' + result.message);
                }
                
            } catch (backendError) {
                console.warn('Backend verification failed:', backendError);
                
                // Continue with Firebase-only auth
                showAlert('Login successful (Firebase only)! Backend unavailable.', 'warning');
                
                // Store Firebase data only
                sessionStorage.setItem('firebase_token', idToken);
                sessionStorage.setItem('user_data', JSON.stringify({
                    uid: userCredential.user.uid,
                    email: userCredential.user.email,
                    name: userCredential.user.displayName || userCredential.user.email
                }));
                
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1500);
            }
            
        } catch (error) {
            console.error('Login error:', error);
            
            let errorMessage = 'Login failed: ' + error.message;
            
            if (error.code) {
                switch(error.code) {
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address format.';
                        break;
                    case 'auth/user-disabled':
                        errorMessage = 'This account has been disabled.';
                        break;
                    case 'auth/user-not-found':
                        errorMessage = 'No account found with this email address.';
                        break;
                    case 'auth/wrong-password':
                    case 'auth/invalid-credential':
                    case 'auth/invalid-login-credentials':
                        errorMessage = 'Invalid email or password. Please check your credentials.';
                        break;
                    case 'auth/too-many-requests':
                        errorMessage = 'Too many failed attempts. Please try again later.';
                        break;
                    default:
                        errorMessage = 'Authentication failed. Please try again.';
                }
            }
            
            showAlert(errorMessage);
        } finally {
            setLoading(false);
        }
    });
});
</script>
@endpush
@endsection