<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            @if (setting('branding.system_logo'))
                <img src="{{ asset('storage/' . setting('branding.system_logo')) }}" alt="Logo" class="brand-logo">
            @else
                <i class="bi bi-bar-chart-line"></i>
            @endif
        </div>

        {{-- <div>
            <div class="brand-title">{{ setting('general.system_name', 'Holol CRM') }}</div>
            <div class="brand-subtitle">لوحة التحكم</div>
        </div> --}}
    </div>

    <nav class="sidebar-menu">
        @can('dashboard.view')
            <a href="{{ route('admin.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>الرئيسية</span>
            </a>
        @endcan

        @can('clients.view')
            <a href="{{ route('admin.clients.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>العملاء</span>
            </a>
        @endcan

        @can('leads.view')
            <a href="{{ route('admin.leads.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>العملاء المحتملين</span>
            </a>
        @endcan
        @can('leads.view')
            <a href="{{ route('admin.lead-followups.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.lead-followups.*') ? 'active' : '' }}">
                <i class="bi bi-chat-left-dots"></i>
                <span>متابعات Leads</span>
            </a>
        @endcan

        @can('followups.view')
            <a href="{{ route('admin.followups.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.followups.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i>
                <span>المتابعات</span>
            </a>
        @endcan

        @can('sales.view')
            <a href="{{ route('admin.sales.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i>
                <span>المبيعات</span>
            </a>
        @endcan
        @can('services.view')
            <a href="{{ route('admin.services.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span>الخدمات</span>
            </a>
        @endcan

        @can('projects.view')
            <a href="#" class="sidebar-link">
                <i class="bi bi-kanban"></i>
                <span>المشاريع</span>
            </a>
        @endcan

        @can('tasks.view')
            <a href="#" class="sidebar-link">
                <i class="bi bi-list-task"></i>
                <span>المهام</span>
            </a>
        @endcan
        @can('payments.view')
            <a href="{{ route('admin.payments.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i>
                <span>المدفوعات</span>
            </a>
        @endcan
        @can('users.view')
            <a href="{{ route('admin.users.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>المستخدمين</span>
            </a>
        @endcan

        @can('roles.view')
            <a href="{{ route('admin.roles.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span>الأدوار والصلاحيات</span>
            </a>
        @endcan

        @can('settings.view')
            <a href="{{ route('admin.settings.edit') }}"
                class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>الإعدادات</span>
            </a>
        @endcan
    </nav>
</aside>
