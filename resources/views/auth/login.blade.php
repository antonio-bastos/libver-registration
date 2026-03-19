@extends('layouts.auth')

@section('title', 'Login - Veria Central Public Library')
@section('width', '420px')

@section('content')
    <form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate>
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
            >
             @error('email')
                <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> Password
            </label>
            <div class="password-container">
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    required 
                    placeholder="Enter your password"
                    class="@error('password') error @enderror"
                >
                <i class="fas fa-eye password-toggle" id="togglePasswordBtn" onclick="togglePassword('password', 'togglePasswordBtn')"></i>
            </div>
             @error('password')
                <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-options">
            <div class="checkbox-wrapper">
                <input id="remember" type="checkbox" name="remember" value="1">
                <label for="remember">Stay Connected</label>
            </div>
            
            <a class="forgot-link" href="{{ route('password.request') }}">
                <i class="fas fa-question-circle"></i> Forgot Password?
            </a>
        </div>

        <div class="form-actions">
            <button type="submit" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </div>

        <div class="form-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Create one here</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        // Simple client-side check if form is valid (email and password exist)
        if(document.getElementById('email').value && document.getElementById('password').value) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Signing In...';
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

    const passwordInput = document.getElementById('password');
    passwordInput.addEventListener('input', function() {
        if (this.value) {
            this.classList.remove('error');
        }
    });
</script>
@endpush
