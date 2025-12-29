<header class="header">
    <div class="header-left">
        <!-- Mobile Menu Toggle -->
        <button class="header-btn mobile-toggle" onclick="toggleMobileSidebar()" style="display: none;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Center Search Bar -->
    <div class="header-center">
        <form action="{{ route('search.index') }}" method="GET" class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="q" placeholder="Cari transaksi, menu, atau bantuan..." class="search-input" value="{{ request('q') }}">
            <div class="search-shortcut">Ctrl + /</div>
        </form>
    </div>

    <div class="header-right">
        <!-- AI Assistant Quick Access -->
        <a href="{{ route('ai.index') }}" class="header-btn ai-btn" title="Tanya AI">
            <img src="{{ asset('image/logo/navis-icon-dark.png') }}" class="logo-dark" style="width: 20px; height: 20px;">
            <img src="{{ asset('image/logo/navis-icon-light.png') }}" class="logo-light" style="width: 20px; height: 20px; display: none;">
            <span class="btn-text">Tanya AI</span>
        </a>

        <!-- Theme Toggle -->
        <button class="header-btn theme-toggle" onclick="toggleTheme()" title="Ganti Tema">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown-container">
            <button class="header-btn notification-btn" onclick="toggleDropdown('notificationDropdown')">
                <i class="fas fa-bell"></i>
                @if(isset($unreadNotifications) && $unreadNotifications->count() > 0)
                    <span class="notification-badge">{{ $unreadNotifications->count() }}</span>
                @endif
            </button>

            <!-- Notification Dropdown -->
            <div id="notificationDropdown" class="dropdown-menu notification-menu">
                <div class="dropdown-header">
                    <h3>Notifikasi</h3>
                    <form action="{{ route('notifications.markAllRead') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="mark-read" style="background:none; border:none; cursor:pointer; padding:0;">Tandai semua dibaca</button>
                    </form>
                </div>
                <div class="dropdown-content">
                    @if(isset($unreadNotifications) && $unreadNotifications->count() > 0)
                        @foreach($unreadNotifications as $notification)
                        <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="notification-item unread">
                            <div class="notification-icon {{ $notification->data['type'] ?? 'info' }}">
                                <i class="fas fa-{{ $notification->data['icon'] ?? 'info-circle' }}"></i>
                            </div>
                            <div class="notification-text">
                                <p class="notif-title">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</p>
                                <p class="notif-desc">{{ $notification->data['message'] ?? '' }}</p>
                                <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                        @endforeach
                    @else
                        <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                            <i class="fas fa-bell-slash" style="font-size: 24px; margin-bottom: 10px; opacity: 0.5;"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>
                    @endif
                </div>
                <div class="dropdown-footer">
                    <a href="#">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="dropdown-container">
            <div class="user-profile" onclick="toggleDropdown('userDropdown')">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="img-cover-circle">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span>
                    <span class="user-role">Administrator</span>
                </div>
                <i class="fas fa-chevron-down chevron-icon"></i>
            </div>

            <!-- Profile Dropdown -->
            <div id="userDropdown" class="dropdown-menu user-menu">
                <div class="user-menu-header">
                    <div class="user-avatar-large">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="img-cover-circle">
                        @else
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <div class="user-details">
                        <div class="name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                </div>
                
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user"></i> Profil Saya
                </a>
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
                <div class="dropdown-divider"></div>
                
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </form>
            </div>
        </div>
    </div>
</header>
