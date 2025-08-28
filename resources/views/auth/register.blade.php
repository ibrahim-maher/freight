@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h2 class="mb-0">
            <i class="fas fa-user-plus me-2"></i>
            Create Account
        </h2>
        <p class="mb-0 mt-2 opacity-75">Join us today</p>
    </div>
    
    <div class="card-body p-4">
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <form id="registerForm">
            @csrf
            
            <!-- Name Input -->
            <div class="form-floating mb-3">
                <input type="text" 
                       class="form-control" 
                       id="name" 
                       name="name" 
                       placeholder="Full Name" 
                       value="Test User"
                       required
                       minlength="2">
                <label for="name">
                    <i class="fas fa-user me-2"></i>Full Name
                </label>
            </div>
            
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
                       required
                       minlength="6">
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
            </div>
            
            <!-- Confirm Password Input -->
            <div class="form-floating mb-3">
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Confirm Password"
                       value="password123" 
                       required
                       minlength="6">
                <label for="password_confirmation">
                    <i class="fas fa-lock me-2"></i>Confirm Password
                </label>
            </div>
            
            <!-- Register Button -->
            <button type="submit" class="btn btn-primary w-100 mb-3" id="registerBtn">
                <span class="btn-text">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Creating Account...
                </span>
            </button>
            
            <!-- Test Button -->
            <button type="button" class="btn btn-info w-100 mb-3" id="testRegisterBtn">
                <i class="fas fa-vial me-2"></i>Test Registration Process
            </button>
            
            <!-- Login Link -->
            <div class="text-center">
                <span class="text-muted">Already have an account?</span>
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">
                    Sign In
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    
    // Show/Hide loading state
    function setLoading(loading) {
        const btnText = registerBtn.querySelector('.btn-text');
        const btnSpinner = registerBtn.querySelector('.btn-spinner');
        
        if (loading) {
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            registerBtn.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            registerBtn.disabled = false;
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
    }
    
    // Validate passwords match
    function validatePasswords() {
        if (passwordInput.value !== passwordConfirmationInput.value) {
            passwordConfirmationInput.setCustomValidity('Passwords do not match');
            return false;
        } else {
            passwordConfirmationInput.setCustomValidity('');
            return true;
        }
    }
    
    passwordConfirmationInput.addEventListener('input', validatePasswords);
    passwordInput.addEventListener('input', validatePasswords);
    
    // Test registration process
    document.getElementById('testRegisterBtn').addEventListener('click', async function() {
        try {
            showAlert('Testing registration process...', 'info');
            
            // Test Laravel backend first
            const testResponse = await fetch('/test-auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ test: 'registration' })
            });
            
            if (testResponse.ok) {
                showAlert('Laravel backend is working! Firebase configuration: ' + 
                         (window.firebaseAuth ? 'Ready' : 'Not configured'), 'success');
            }
        } catch (error) {
            showAlert('Test failed: ' + error.message, 'danger');
        }
    });
    
    // Handle form submission
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validatePasswords()) {
            showAlert('Passwords do not match');
            return;
        }
        
        setLoading(true);
        
        try {
            if (!window.firebaseAuth) {
                throw new Error('Firebase not configured');
            }
            
            console.log('Creating user with Firebase...');
            
            // Create user with Firebase
            const userCredential = await window.createUserWithEmailAndPassword(
                window.firebaseAuth, 
                emailInput.value, 
                passwordInput.value
            );
            
            console.log('Firebase user created:', userCredential.user.uid);
            
            // Update user profile
            await userCredential.user.updateProfile({
                displayName: nameInput.value
            });
            
            // Register with Laravel backend
            console.log('Registering with Laravel backend...');
            
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    name: nameInput.value,
                    email: emailInput.value,
                    password: passwordInput.value,
                    uid: userCredential.user.uid
                })
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                showAlert('Account created successfully! Redirecting to login...', 'success');
                
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                showAlert('Registration failed: ' + result.message);
            }
            
        } catch (error) {
            console.error('Registration error:', error);
            
            let errorMessage = 'Registration failed: ' + error.message;
            
            if (error.code) {
                switch(error.code) {
                    case 'auth/email-already-in-use':
                        errorMessage = 'This email is already registered. Try signing in instead.';
                        break;
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address.';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMessage = 'Email/password accounts are not enabled.';
                        break;
                    case 'auth/weak-password':
                        errorMessage = 'Password is too weak. Please choose a stronger password.';
                        break;
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