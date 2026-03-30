<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #308bd6;
            --primary-dark: #1567aa;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-dark: #000000;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #308bd6;
            --warning: #f59e0b;
            --accent: #308bd6;
            --accent-deep: #22304a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-dark);
            line-height: 1.5;
        }

        .admin-nav {
            background: #000000;
            padding: 12px 0;
            margin-bottom: 40px;
        }

        .admin-nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            gap: 24px;
        }

        .admin-nav-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .admin-nav-link:hover, .admin-nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 24px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .stat-value { font-size: 28px; font-weight: 700; color: var(--primary); margin-bottom: 4px; }
        .stat-label { font-size: 14px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text-dark); }
        .btn-outline:hover { background: #f1f5f9; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 2px solid var(--border);
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .badge {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fee2e2; color: #991b1b; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); }
        input, select, textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <header>
        @include('components.navbar')
    </header>

    <div class="admin-nav">
        <div class="admin-nav-container">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Overview
            </a>
            <a href="{{ route('admin.activities.index') }}" class="admin-nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="{{ route('admin.activities.archived') }}" class="admin-nav-link {{ request()->routeIs('admin.activities.archived') ? 'active' : '' }}">
                <i class="fas fa-box-archive"></i> Archive
            </a>
            <a href="{{ route('admin.analytics') }}" class="admin-nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <i class="fas fa-chart-column"></i> Insights
            </a>
            <a href="{{ route('admin.search') }}" class="admin-nav-link {{ request()->routeIs('admin.search') ? 'active' : '' }}">
                <i class="fas fa-search"></i> Search
            </a>
            <a href="{{ route('admin.checkin.tablet') }}" class="admin-nav-link {{ request()->routeIs('admin.checkin.tablet*') ? 'active' : '' }}">
                <i class="fas fa-tablet-alt"></i> Tablet Check-in
            </a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.blacklist.index') }}" class="admin-nav-link {{ request()->routeIs('admin.blacklist.*') ? 'active' : '' }}">
                <i class="fas fa-user-slash"></i> Blacklist
            </a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Users
            </a>
            @endif
        </div>
    </div>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
    <script src="https://cdn.userway.org/widget.js" data-account="P05mbmczA2" data-position="3"></script>
</body>
</html>
