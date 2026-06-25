<div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">سجل النشاط</h5>
        </div>

        <div class="card-body">
            @forelse ($activities as $activity)
                <div class="activity-log-item">
                    <div class="activity-icon">
                        <i class="bi {{ $activity->event_icon }}"></i>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">
                                    {{ $activity->title }}
                                </div>

                                @if ($activity->description)
                                    <div class="text-muted small">
                                        {{ $activity->description }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-muted small text-nowrap">
                                {{ $activity->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <div class="text-muted small mt-2">
                            بواسطة:
                            {{ $activity->causer?->name ?? 'System' }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا يوجد نشاط مسجل حتى الآن
                </div>
            @endforelse
        </div>
    </div>
</div>