@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h2 class="mb-0">
            <i class="fas fa-envelope-circle-check me-2"></i>
            Verify Your Email
        </h2>
        <p class="mb-0 mt-2 opacity-75">Check your inbox for verification</p>
    </div>
    
    <div class="card-body p-4 text-center">
        <div class="mb-4">
            <i class="fas fa-envelope fa-4x text-primary mb-3"></i>
            <p class="text-muted">
                We've sent a verification link to your email address. 
                Please check your inbox and click the link to verify your account.
            </p>
        </div>
        
        <div class="d-grid gap-2">
            <button class="btn btn-primary" id="resendBtn">
                <i class="fas fa-paper-plane me-2"></i>
                Resend Verification Email
            </button>
            
            <button class="btn btn-outline-secondary" id="checkVerificationBtn">
                <i class="fas fa-check-circle me-2"></i>
                I've Verified My Email
            </button>
        </div>
        
        <hr class="my-4">
        
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resendBtn');
    const checkVerificationBtn = document.getElementById('checkVerificationBtn');
    
    // Show alert function
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
    }
    
    // Resend verification email
    resendBtn.addEventListener('click', async function() {
        try {
            const user = window.firebaseAuth.currentUser;
            if (user) {
                await user.sendEmailVerification();
                showAlert('Verification email sent successfully!', 'success');
            } else {
                showAlert('No user found. Please sign up again.', 'danger');
            }
        } catch (error) {
            console.error('Resend error:', error);
            showAlert('Failed to send verification email. Try again later.', 'danger');
        }
    });
    
    // Check verification status
    checkVerificationBtn.addEventListener('click', async function() {
        try {
            const user = window.firebaseAuth.currentUser;
            if (user) {
                await user.reload();
                if (user.emailVerified) {
                    showAlert('Email verified successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                } else {
                    showAlert('Email not verified yet. Please check your inbox.', 'warning');
                }
            }
        } catch (error) {
            console.error('Verification check error:', error);
            showAlert('Error checking verification status.', 'danger');
        }
    });
});
</script>
@endpush
@endsection