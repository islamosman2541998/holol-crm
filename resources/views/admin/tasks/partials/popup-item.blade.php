@php
    $dueAt = $task->due_at?->locale('ar');
@endphp

<a href="{{ route('admin.tasks.show', $task) }}" class="tasks-popup-item tasks-popup-item--{{ $variant }}">
    <div class="tasks-popup-item-main">
        <div class="tasks-popup-item-title">{{ $task->title }}</div>

        <div class="tasks-popup-item-meta">
            @if ($variant === 'assigned')
                <span><i class="bi bi-person"></i> {{ $task->creator?->name ?? '-' }}</span>
            @endif

            @if ($dueAt)
                @if ($variant === 'overdue')
                    <span class="is-danger"><i class="bi bi-exclamation-circle"></i> متأخرة {{ $dueAt->diffForHumans() }}</span>
                    <span><i class="bi bi-calendar3"></i> {{ $dueAt->translatedFormat('j F، h:i A') }}</span>
                @elseif ($variant === 'today')
                    <span><i class="bi bi-clock"></i> اليوم {{ $dueAt->translatedFormat('h:i A') }}</span>
                @else
                    <span><i class="bi bi-calendar3"></i> {{ $dueAt->translatedFormat('j F، h:i A') }}</span>
                @endif
            @endif
        </div>
    </div>

    <span class="tasks-popup-priority tasks-popup-priority--{{ $task->priority }}">{{ $task->priority_label }}</span>
    <i class="bi bi-chevron-left tasks-popup-item-arrow"></i>
</a>
