<div class="dropdown notification-bell" wire:poll.30s="$refresh">
    <button type="button" class="theme-toggle-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        @if ($unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end user-dropdown-menu notification-dropdown-menu">
        <div class="d-flex justify-content-between align-items-center px-2 pb-2">
            <strong class="small">الإشعارات</strong>

            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="btn btn-link btn-sm p-0 text-decoration-none">
                    تحديد الكل كمقروء
                </button>
            @endif
        </div>

        <div class="dropdown-divider"></div>

        <div class="notification-list">
            @forelse ($notifications as $notification)
                @php
                    $isAssigned = $notification->type === \App\Notifications\TaskAssignedNotification::class;

                    $notificationUrl = $isAssigned
                        ? (isset($notification->data['task_id'])
                            ? route('admin.tasks.show', $notification->data['task_id'])
                            : route('admin.tasks.index'))
                        : route('admin.tasks.index', ['dateFilter' => ($notification->data['overdue_count'] ?? 0) > 0 ? 'overdue' : 'today']);
                @endphp
                <a href="{{ $notificationUrl }}"
                   wire:click="markAsRead('{{ $notification->id }}')"
                   class="dropdown-item notification-item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="small fw-semibold">
                                @if ($isAssigned)
                                    تم اسناد مهمة لك: {{ $notification->data['title'] ?? '' }}
                                @elseif (($notification->data['overdue_count'] ?? 0) > 0)
                                    عندك {{ $notification->data['overdue_count'] }} مهمة متأخرة
                                    @if (($notification->data['today_count'] ?? 0) > 0)
                                        و{{ $notification->data['today_count'] }} اليوم
                                    @endif
                                @else
                                    عندك {{ $notification->data['today_count'] ?? 0 }} مهمة اليوم
                                @endif
                            </div>
                            <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>

                        @if (is_null($notification->read_at))
                            <span class="notification-dot"></span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-muted small text-center py-4">مفيش إشعارات</div>
            @endforelse
        </div>
    </div>
</div>
