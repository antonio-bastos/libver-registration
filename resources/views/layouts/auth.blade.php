<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Public Library of Veria')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --success: #059669;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #475569;
            --text-light: #94a3b8;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        }

        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.10) 0%, rgba(224, 242, 254, 0.10) 100%), 
                        url('https://www.libver.gr/wp-content/uploads/2014/10/anakprotos.jpg') center/cover fixed;
            filter: blur(6px);
            z-index: -1;
        }

        header {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 24px;
        }

        .nav a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav a:hover {
            color: var(--primary);
        }

        .nav-link-active {
            color: var(--primary) !important;
            font-weight: 600 !important;
        }

        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }

        .form-wrapper {
            width: 100%;
            max-width: @yield('width', '420px');
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .container {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 40px;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            margin-bottom: 24px;
            padding: 14px 16px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .alert-danger {
            background: var(--danger-light);
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-danger i {
            font-size: 16px;
            margin-top: 2px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-light);
            margin-bottom: 20px;
            margin-top: 28px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .required {
            color: var(--danger);
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="tel"] {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--bg-white);
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        input::placeholder {
            color: var(--text-light);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: rgba(37, 99, 235, 0.02);
        }

        input.error {
            border-color: var(--danger);
            background: var(--danger-light);
        }

        input.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .field-error {
            font-size: 12px;
            color: var(--danger);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-error i {
            font-size: 11px;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: var(--bg-white);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        button[type="submit"]:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .form-footer {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .form-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Password Toggle */
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }

        @media (max-width: 640px) {
            .main {
                padding: 24px 16px;
            }

            .container {
                padding: 24px;
            }

            .form-header h1 {
                font-size: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
            }

            .nav {
                padding: 12px 16px;
            }

            .brand {
                font-size: 16px;
            }

            .nav-links {
                gap: 16px;
            }

            .nav a {
                font-size: 13px;
            }
        }

        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header>
        <div class="nav">
            <div class="brand">
                <i class="fas fa-book"></i> Public Library of Veria
            </div>
            <div class="nav-links">
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'nav-link-active' : '' }}">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'nav-link-active' : '' }}">
                    <i class="fas fa-user-plus"></i> Registration
                </a>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="form-wrapper">
            <div class="form-header">
                <h1>@yield('header')</h1>
                <p>@yield('sub-header')</p>
            </div>

            <div class="container">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            @if(count($errors) > 1)
                                <strong>Please correct the following errors:</strong>
                                <ul style="margin-top: 8px; margin-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <strong>Error:</strong>
                                <p style="margin-top: 4px;">{{ $errors->first() }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <script>
        // Password visibility toggle logic
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
    </script>
    @stack('scripts')
</body>
</html>
