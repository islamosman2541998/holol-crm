<header class="admin-topbar">
    <div>
        <h5 class="mb-0">@yield('page_title', 'لوحة التحكم')</h5>
        <small class="text-muted">مرحبًا {{ auth()->user()->name }}</small>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="user-chip">
            <div class="user-avatar">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="d-none d-md-block">
                <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                <div class="text-muted small">
                    {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left"></i>
                خروج
            </button>
        </form>
    </div>
</header>