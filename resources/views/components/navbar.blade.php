<style>
    .nav {
        max-width: 1200px;
        margin: 0 auto;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .brand {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-links {
        display: flex;
        gap: 24px;
        align-items: center;
    }

    .nav-link, .nav-links a {
        text-decoration: none;
        color: var(--text-muted, #64748b) !important;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        cursor: pointer;
        font-family: inherit;
        padding: 0;
    }

    .nav-link:hover, .nav-links a:hover {
        color: var(--primary) !important;
    }

    .nav-link-active {
        color: var(--primary) !important;
        font-weight: 600 !important;
    }
</style>

<div class="nav">
    <div class="brand">
        <a href="{{ route('home') }}" style="text-decoration: none; color: inherit;">
            <i class="fas fa-book"></i> Public Library of Veria
        </a>
    </div>
    <div class="nav-links">
        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">
                    <i class="fas fa-user-shield"></i> Manage
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Account
            </a>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @else
            <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'nav-link-active' : '' }}">
                <i class="fas fa-user-plus"></i> Registration
            </a>
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'nav-link-active' : '' }}">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        @endauth
    </div>
</div>
