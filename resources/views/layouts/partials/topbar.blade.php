<header class="admin-topbar">
    <div class="topbar-heading">
        <h5 class="mb-0">@yield('page_title', 'لوحة التحكم')</h5>
        <small class="text-muted">مرحبًا، {{ auth()->user()->name }}</small>
    </div>

    <div class="topbar-actions">
        <button type="button" id="themeToggle" class="theme-toggle-btn" aria-label="تبديل الوضع الداكن">
            <i class="bi bi-moon-stars"></i>
        </button>

        <livewire:admin.shared.notification-bell />

        <div class="dropdown user-dropdown">
            <button type="button" class="user-chip" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="user-chip-name">{{ auth()->user()->name }}</div>
                    <div class="user-role-badge">
                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    </div>
                </div>
                <i class="bi bi-chevron-down user-chip-caret"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                <div class="user-dropdown-header">
                    <div class="user-avatar user-avatar-lg">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                        <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item user-dropdown-logout">
                        <i class="bi bi-box-arrow-left"></i>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
