@extends('layouts.auth')

@section('title', 'Reset Password - Veria Central Public Library')
@section('width', '420px')

@section('content')
    <div style="margin-bottom: 24px; text-align: center;">
         <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">Create New Password</h2>
         <p style="color: var(--text-muted); font-size: 0.95rem;">Enter your new password below to reset your account credentials.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> Email Address
            </label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                required 
                class="@error('email') error @enderror"
                value="{{ $email ?? old('email') }}"
                readonly
            >
             @error('email')
                <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> New Password
            </label>
            <div class="password-container">
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    required 
                    placeholder="Create a strong password"
                    class="@error('password') error @enderror"
                    autofocus
                >
                <i class="fas fa-eye password-toggle" id="togglePasswordBtn" onclick="togglePassword('password', 'togglePasswordBtn')"></i>
            </div>
             @error('password')
                <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                <i class="fas fa-check-circle"></i> Confirm Password
            </label>
             <div class="password-container">
                <input 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    required 
                    placeholder="Repeat password"
                >
            </div>
        </div>

        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit" id="submitBtn">
                <i class="fas fa-save"></i> Reset Password
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        if(document.getElementById('password').value && document.getElementById('password_confirmation').value) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Resetting...';
        }
    });
</script>
@endpush
