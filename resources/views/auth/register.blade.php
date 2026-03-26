@extends('layouts.auth')

@section('title', 'Register - Veria Central Public Library')
@section('width', '600px')

@push('styles')
<style>
    /* Professional Spacing & Layout Tweaks */
    .form-row {
        margin-bottom: 30px; /* Increased from default */
        gap: 24px;
        display: flex;
        flex-wrap: wrap;
    }
    
    .form-row.full {
        flex-direction: column;
    }

    .form-field {
        flex: 1;
        min-width: 200px;
        display: flex;
        flex-direction: column;
    }

    /* Enhanced Input Styling */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="tel"],
    input[type="date"] {
        padding: 14px 16px; /* Taller, more comfortable inputs */
        background-color: #f8fafc; /* Very subtle grey */
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.25s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* Subtle depth */
        width: 100%;
        box-sizing: border-box;
    }

    input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); /* Soft distinctive focus ring */
        transform: translateY(-1px);
        outline: none;
    }
    
    input.error {
        border-color: #ef4444;
        background-color: #fef2f2;
    }

    /* Label Styling */
    label {
        font-size: 0.9rem;
        color: #334155;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    label i {
        color: #64748b; /* Muted icon color */
        margin-right: 6px;
    }
    
    .required {
        color: #ef4444;
        margin-left: 2px;
    }

    /* Section Headers */
    .section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        margin-top: 40px;
        margin-bottom: 25px;
        letter-spacing: 1px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
    }
    
    .field-error {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-hint {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 8px;
    }

    .form-subtext {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .password-container {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #94a3b8;
        cursor: pointer;
    }

    .optional-tag {
        font-weight: normal;
        color: #94a3b8;
        font-size: 0.8em;
    }

    /* Password Meter Polish */
    .password-strength-meter {
        height: 4px;
        background-color: #f1f5f9;
        margin-top: 12px;
        border-radius: 2px;
        overflow: hidden;
    }
    
    .meter-bar {
        height: 100%;
        width: 0;
        border-radius: 2px;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    .password-strength-text {
        font-size: 12px;
        margin-top: 6px;
        min-height: 18px;
    }

    .strength-weak { background-color: #ef4444; }
    .strength-fair { background-color: #f59e0b; }
    .strength-good { background-color: #3b82f6; }
    .strength-strong { background-color: #308bd6; }
    
    button[type="submit"] {
        background-color: #308bd6;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    button[type="submit"]:hover {
        background-color: #1567aa;
    }
</style>
@endpush

@section('content')
    <form method="POST" action="{{ route('register.submit') }}" id="registerForm" novalidate autocomplete="off">
        @csrf

        <!-- Account Details Section -->
        <div class="section-title">Account Credentials</div>
        
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
                    placeholder="e.g. name@example.com"
                    class="@error('email') error @enderror"
                    value="{{ old('email') }}"
                    autocomplete="email"
                >
                @error('email')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @else
                    <div class="form-hint">We'll use this for booking confirmations.</div>
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
                        placeholder="Create a strong password"
                        class="@error('password') error @enderror"
                        autocomplete="new-password"
                    >
                    <i class="fas fa-eye password-toggle" 
                       id="toggleRegPassword" 
                       onclick="togglePassword('password', 'toggleRegPassword')">
                    </i>
                </div>
                
                <!-- Password Strength Meter -->
                <div class="password-strength-meter" id="strengthMeter">
                    <div class="meter-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text" id="strengthText"></div>

                @error('password')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">
                    <i class="fas fa-check-circle"></i> Confirm Password <span class="required">*</span>
                </label>
                 <div class="password-container">
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        required 
                        placeholder="Repeat password"
                        autocomplete="new-password"
                    >
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
                    placeholder="Your First Name"
                    class="@error('name') error @enderror"
                    value="{{ old('name') }}"
                    autocomplete="given-name"
                >
                @error('name')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="surname">
                    <i class="fas fa-user-tag"></i> Last Name <span class="required">*</span>
                </label>
                <input 
                    id="surname" 
                    name="surname" 
                    type="text" 
                    required 
                    placeholder="Your Surname"
                    class="@error('surname') error @enderror"
                    value="{{ old('surname') }}"
                    autocomplete="family-name"
                >
                @error('surname')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="phone">
                    <i class="fas fa-phone"></i> Phone Number <span class="required">*</span>
                </label>
                <input 
                    id="phone" 
                    name="phone" 
                    type="tel" 
                    required
                    placeholder="Mobile or Landline (e.g. 69... or 23...)"
                    class="@error('phone') error @enderror"
                    value="{{ old('phone') }}"
                    autocomplete="tel"
                >
                @error('phone')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="dob">
                    <i class="fas fa-calendar-alt"></i> Date of Birth <span class="required">*</span>
                </label>
                 <input 
                    id="dob" 
                    name="dob" 
                    type="date" 
                    required
                    class="@error('dob') error @enderror"
                    value="{{ old('dob') }}"
                    autocomplete="bday"
                >
                <div class="form-subtext">For age verification purposes</div>
            </div>
        </div>
        
        <div class="form-row full" style="margin-bottom: 10px;">
            <div class="form-field">
                <label for="card_number">
                    <i class="fas fa-id-card"></i> Library Card Number <span class="optional-tag">(Optional)</span>
                </label>
                <input 
                    id="card_number" 
                    name="card_number" 
                    type="text" 
                    placeholder="e.g. XXXXXXXXXXX"
                    class="@error('card_number') error @enderror"
                    value="{{ old('card_number') }}"
                >
                 @error('card_number')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-actions" style="margin-top: 40px;">
            <button type="submit" style="padding: 16px;">
                Create Account <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </div>

        <div class="auth-footer" style="text-align: center; margin-top: 25px; color: #64748b; font-size: 0.9em;">
            Already have an account? <a href="{{ route('login') }}" style="color: #308bd6; font-weight: 600; text-decoration: none;">Sign in here</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const btn = document.querySelector('button[type="submit"]');
        if (this.checkValidity()) {
             btn.disabled = true;
             btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        }
    });

    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if(passwordField) {
            passwordField.addEventListener('input', function() {
                const val = this.value;
                
                if (val.length > 0) {
                    strengthMeter.style.display = 'block';
                } else {
                    strengthMeter.style.display = 'none';
                    return;
                }

                let score = 0;
                
                if (val.length >= 8) score++;
                if (val.length >= 12) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

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

                strengthBar.style.width = width + '%';
                strengthBar.className = 'meter-bar ' + colorClass;
                strengthText.textContent = label;
                
                if (score < 2) strengthText.style.color = '#ef4444';
                else if (score < 4) strengthText.style.color = '#f59e0b';
                else if (score < 5) strengthText.style.color = '#3b82f6';
                else strengthText.style.color = '#308bd6';
            });
        }
    });
</script>
@endpush
