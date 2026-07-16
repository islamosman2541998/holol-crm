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
                <span>Leads </span>
            </a>
        @endcan


        @can('quotations.view')
            <a href="{{ route('admin.quotations.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i>
                <span>عروض الأسعار</span>
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
        @can('teams.view')
            <a href="{{ route('admin.teams.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>الفرق</span>
            </a>
        @endcan
        @can('members.view')
            <a href="{{ route('admin.members.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>الأعضاء</span>
            </a>
        @endcan

        @can('projects.view')
            <a href="{{ route('admin.projects.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                <i class="bi bi-kanban"></i>
                <span>المشاريع</span>
            </a>
        @endcan

        @can('tasks.view')
            <a href="{{ route('admin.tasks.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square"></i>
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
        @canany(['reports.view', 'followups.view', 'leads.view'])
            @php
                $reportsOpen =
                    request()->routeIs('admin.reports.*') ||
                    request()->routeIs('admin.followups.*') ||
                    request()->routeIs('admin.lead-followups.*');
            @endphp

            <div class="sidebar-dropdown {{ $reportsOpen ? 'is-open' : '' }}">
                <button type="button" class="sidebar-link sidebar-dropdown-toggle {{ $reportsOpen ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>التقارير</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </button>

                <div class="sidebar-submenu">
                    <div class="sidebar-submenu-inner">
                        @can('reports.view')
                            <a href="{{ route('admin.reports.clients') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.reports.clients') ? 'active' : '' }}">
                                <i class="bi bi-person-vcard"></i>
                                <span>تقرير العملاء</span>
                            </a>
                        @endcan
                        @can('reports.view')
                            <a href="{{ route('admin.reports.leads') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.reports.leads') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>تقرير Leads</span>
                            </a>
                        @endcan
                        @can('followups.view')
                            <a href="{{ route('admin.followups.index') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.followups.*') ? 'active' : '' }}">
                                <i class="bi bi-chat-dots"></i>
                                <span>متابعات العملاء</span>
                            </a>
                        @endcan

                        @can('leads.view')
                            <a href="{{ route('admin.lead-followups.index') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.lead-followups.*') ? 'active' : '' }}">
                                <i class="bi bi-chat-left-dots"></i>
                                <span>متابعات Leads</span>
                            </a>
                        @endcan
                        @can('reports.view')
                            <a href="{{ route('admin.reports.sales-payments') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.reports.sales-payments') ? 'active' : '' }}">
                                <i class="bi bi-cash-stack"></i>
                                <span>تقرير المبيعات والمدفوعات</span>
                            </a>
                        @endcan
                        @can('reports.view')
                            <a href="{{ route('admin.reports.quotations') }}"
                                class="sidebar-sublink {{ request()->routeIs('admin.reports.quotations') ? 'active' : '' }}">
                                <i class="bi bi-receipt-cutoff"></i>
                                <span>تقرير عروض الأسعار</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany
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
