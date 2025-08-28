@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h2 class="mb-0">
            <i class="fas fa-key me-2"></i>
            Reset Password
        </h2>
        <p class="mb-0 mt-2 opacity-75">Enter your email to reset password</p>
    </div>
    
    <div class="card-body p-4">
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <form id="resetForm">
            @csrf
            
            <!-- Email Input -->
            <div class="form-floating mb-3">
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com" 
                       required>
                <label for="email">
                    <i class="fas fa-envelope me-2"></i>Email Address
                </label>
            </div>
            
            <!-- Reset Button -->
            <button type="submit" class="btn btn-primary w-100 mb-3" id="resetBtn">
                <span class="btn-text">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Sending...
                </span>
            </button>
            
            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script type="module">
import { getAuth, sendPasswordResetEmail } from 'https://www.gstatic.com/firebasejs/10.0.0/firebase-auth.js';

document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');
    const resetBtn = document.getElementById('resetBtn');
    const emailInput = document.getElementById('email');
    
    // Show/Hide loading state
    function setLoading(loading) {
        const btnText = resetBtn.querySelector('.btn-text');
        const btnSpinner = resetBtn.querySelector('.btn-spinner');
        
        if (loading) {
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            resetBtn.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            resetBtn.disabled = false;
        }
    }
    
    // Show alert message
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
    
    // Handle form submission
    resetForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        setLoading(true);
        
        try {
            const auth = getAuth();
            await sendPasswordResetEmail(auth, emailInput.value);
            
            showAlert('Password reset email sent! Check your inbox.', 'success');
            
            setTimeout(() => {
                window.location.href = '/auth/login';
            }, 3000);
            
        } catch (error) {
            console.error('Password reset error:', error);
            
            let errorMessage = 'Failed to send reset email. Please try again.';
            
            if (error.code === 'auth/user-not-found') {
                errorMessage = 'No account found with this email address.';
            } else if (error.code === 'auth/invalid-email') {
                errorMessage = 'Invalid email address.';
            } else if (error.code === 'auth/too-many-requests') {
                errorMessage = 'Too many requests. Please try again later.';
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