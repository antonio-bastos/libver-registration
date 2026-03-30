@extends('layouts.auth')

@section('title', 'Reset Password - Public Library of Veria')
@section('width', '420px')

@section('content')
    <div style="margin-bottom: 24px; text-align: center;">
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">Forgot Password?</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Enter the email address associated with your account and we'll send you a link to reset your password.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #a7f3d0;">
            <i class="fas fa-check-circle" style="margin-right: 6px;"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="resetForm">
        @csrf

        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> Email Address
            </label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                required 
                placeholder="you@example.com"
                class="@error('email') error @enderror"
                value="{{ old('email') }}"
                autofocus
            >
             @error('email')
                <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </div>

        <div class="form-footer">
            Remember your password?
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        if(document.getElementById('email').value) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Sending...';
        }
    });

    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        if (this.value && !this.value.includes('@')) {
            this.classList.add('error');
        } else {
            this.classList.remove('error');
        }
    });
</script>
@endpush
