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
