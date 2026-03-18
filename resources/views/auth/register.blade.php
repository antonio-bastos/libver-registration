@extends('layouts.auth')

@section('title', 'Register - Public Library of Veria')
@section('header', 'Create Your Account')
@section('sub-header', 'Join our library community today')
@section('width', '600px')

@push('styles')
<style>
    .password-strength-meter {
        height: 5px;
        background-color: #e2e8f0;
        border-radius: 3px;
        margin-top: 10px;
        overflow: hidden;
        display: none; /* Hidden by default until typing starts */
    }
    
    .meter-bar {
        height: 100%;
        width: 0;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    .password-strength-text {
        font-size: 11px;
        margin-top: 5px;
        color: #64748b;
        font-weight: 500;
        text-align: right;
        min-height: 17px;
    }

    .strength-weak { background-color: #ef4444; }   /* Red */
    .strength-fair { background-color: #f59e0b; }   /* Orange */
    .strength-good { background-color: #3b82f6; }   /* Blue */
    .strength-strong { background-color: #22c55e; } /* Green */
</style>
@endpush

@section('content')
    <form method="POST" action="{{ route('register.submit') }}" id="registerForm" novalidate>
        @csrf

        <!-- Account Details Section -->
        <div class="section-title">Account Details</div>
        
        <div class="form-row full">
            <div class="form-field">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address <span class="required">*</span>
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
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="password">
                    <i class="fas fa-lock"></i> Password <span class="required">*</span>
                </label>
                <div class="password-container">
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        placeholder="8+ chars, mixed case, num & symbol"
                        class="@error('password') error @enderror"
                    >
                    <i class="fas fa-eye password-toggle" id="toggleRegPassword" onclick="togglePassword('password', 'toggleRegPassword')"></i>
                </div>
                
                <!-- Password Strength Meter -->
                <div class="password-strength-meter" id="strengthMeter">
                    <div class="meter-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text" id="strengthText"></div>

                @error('password')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">
                    <i class="fas fa-lock"></i> Confirm Password <span class="required">*</span>
                </label>
                 <div class="password-container">
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        required 
                        placeholder="Re-enter your password"
                        class="@error('password_confirmation') error @enderror"
                    >
                     <i class="fas fa-eye password-toggle" id="toggleRegConfirm" onclick="togglePassword('password_confirmation', 'toggleRegConfirm')"></i>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section-title">Personal Information</div>

        <div class="form-row">
            <div class="form-field">
                <label for="name">
                    <i class="fas fa-user"></i> First Name <span class="required">*</span>
                </label>
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    required 
                    placeholder="John"
                    class="@error('name') error @enderror"
                    value="{{ old('name') }}"
                >
                @error('name')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="surname">
                    <i class="fas fa-user"></i> Last Name <span class="required">*</span>
                </label>
                <input 
                    id="surname" 
                    name="surname" 
                    type="text" 
                    required 
                    placeholder="Doe"
                    class="@error('surname') error @enderror"
                    value="{{ old('surname') }}"
                >
                @error('surname')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="phone">
                    <i class="fas fa-phone"></i> Mobile Phone <span class="required">*</span>
                </label>
                <input 
                    id="phone" 
                    name="phone" 
                    type="tel" 
                    required 
                    placeholder="6912345678"
                    class="@error('phone') error @enderror"
                    value="{{ old('phone') }}"
                >
                @error('phone')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="card_number">
                    <i class="fas fa-id-card"></i> ID Card Number <span class="required">*</span>
                </label>
                <input 
                    id="card_number" 
                    name="card_number" 
                    type="text" 
                    required 
                    placeholder="Π1234567"
                    class="@error('card_number') error @enderror"
                    value="{{ old('card_number') }}"
                >
                @error('card_number')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row full">
            <div class="form-field">
                <label for="dob">
                    <i class="fas fa-calendar"></i> Date of Birth <span class="required">*</span>
                </label>
                <input 
                    id="dob" 
                    name="dob" 
                    type="date" 
                    required 
                    class="@error('dob') error @enderror"
                    value="{{ old('dob') }}"
                >
                @error('dob')
                    <div class="field-error"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" id="submitBtn">
                <i class="fas fa-user-check"></i> Create Account
            </button>
        </div>

        <div class="form-footer">
            Already have an account? 
            <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        // Let the browser validation fallback or backend validation handle empty fields if novalidate logic isn't fully client-side
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Creating Account...';
    });

    // Add real-time validation feedback
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        if (this.value && !this.value.includes('@')) {
            this.classList.add('error');
        } else {
            this.classList.remove('error');
        }
    });

    const passwordInputs = document.querySelectorAll('#password, #password_confirmation');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {

    // Password Strength Logic
    const passwordField = document.getElementById('password');
    const strengthMeter = document.getElementById('strengthMeter');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    passwordField.addEventListener('input', function() {
        const val = this.value;
        
        // Show meter if user has started typing
        if (val.length > 0) {
            strengthMeter.style.display = 'block';
        } else {
            strengthMeter.style.display = 'none';
            strengthText.textContent = '';
            return;
        }

        let score = 0;
        
        // Criteria
        if (val.length >= 8) score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        // Identify strength levels (0-5 score)
        // Adjust width and color
        let width = 0;
        let colorClass = '';
        let label = '';

        if (score < 2) {
            width = 25;
            colorClass = 'strength-weak';
            label = 'Weak';
        } else if (score < 4) {
            width = 50;
            colorClass = 'strength-fair';
            label = 'Fair';
        } else if (score < 5) {
            width = 75;
            colorClass = 'strength-good';
            label = 'Good';
        } else {
            width = 100;
            colorClass = 'strength-strong';
            label = 'Strong';
        }

        // Apply styles
        strengthBar.style.width = width + '%';
        strengthBar.className = 'meter-bar ' + colorClass;
        strengthText.textContent = label;
        
        // Color text to match bar
        if (score < 2) strengthText.style.color = '#ef4444';
        else if (score < 4) strengthText.style.color = '#f59e0b';
        else if (score < 5) strengthText.style.color = '#3b82f6';
        else strengthText.style.color = '#22c55e';
    });
            if (this.value && this.value.length < 8) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
    });
</script>
@endpush
