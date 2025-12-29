<aside class="sidebar" id="sidebar">
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="logo" onclick="handleLogoClick()">
            <!-- Horizontal Logos (Expanded) -->
            <img src="{{ asset('image/logo/pl-sava-logo-horizontal-dark.png') }}" alt="PLFIS" class="logo-img logo-horizontal logo-dark">
            <img src="{{ asset('image/logo/pl-sava-logo-horizontal-light.png') }}" alt="PLFIS" class="logo-img logo-horizontal logo-light">
            
            <!-- Icon Logos (Collapsed) -->
            <img src="{{ asset('image/logo/pl-sava-icon-dark.png') }}" alt="PLFIS" class="logo-img logo-icon logo-dark">
            <img src="{{ asset('image/logo/pl-sava-icon-light.png') }}" alt="PLFIS" class="logo-img logo-icon logo-light">
        </div>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="{{ route('dashboard') }}" class="menu-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('finance.index') }}" class="menu-link {{ Request::routeIs('finance.*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span class="menu-text">Keuangan</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('categories.index') }}" class="menu-link {{ Request::routeIs('categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Kategori</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('emergency-fund.index') }}" class="menu-link {{ Request::routeIs('emergency-fund.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span class="menu-text">Dana Darurat</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('ai.index') }}" class="menu-link {{ Request::routeIs('ai.*') ? 'active' : '' }}">
                <i class="fas fa-robot"></i>
                <span class="menu-text">AI Assistant</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('digital-accounts.index') }}" class="menu-link {{ Request::routeIs('digital-accounts.*') ? 'active' : '' }}">
                <i class="fas fa-globe-americas"></i>
                <span class="menu-text">Pendapatan Digital</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('recurring.index') }}" class="menu-link {{ Request::routeIs('recurring.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span class="menu-text">Kebutuhan Pokok</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('vault.index') }}" class="menu-link {{ Request::routeIs('vault.*') ? 'active' : '' }}">
                <i class="fas fa-lock"></i>
                <span class="menu-text">Vault Akun</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('notes.index') }}" class="menu-link {{ Request::routeIs('notes.*') ? 'active' : '' }}">
                <i class="fas fa-sticky-note"></i>
                <span class="menu-text">Catatan</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('tasks.index') }}" class="menu-link {{ Request::routeIs('tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span class="menu-text">Agenda</span>
            </a>
        </li>

        <li class="menu-item menu-separator">
            <a href="{{ route('settings.index') }}" class="menu-link {{ Request::routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Pengaturan</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('logout') }}" class="menu-link" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</aside>

<script>
    function handleLogoClick() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('collapsed')) {
            toggleSidebar();
        }
    }
</script>
