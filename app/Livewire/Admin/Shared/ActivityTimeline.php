<?php

namespace App\Livewire\Admin\Shared;

use App\Models\ActivityLog;
use Livewire\Attributes\On;
use Livewire\Component;

class ActivityTimeline extends Component
{
    public string $subjectType;
    public int $subjectId;

    public function mount(string $subjectType, int $subjectId): void
    {
        $this->subjectType = $subjectType;
        $this->subjectId = $subjectId;
    }

    #[On('activity-log-updated')]
    public function refreshTimeline(): void
    {
        //
    }

    public function render()
    {
        $activities = ActivityLog::query()
            ->with('causer')
            ->where('subject_type', $this->subjectType)
            ->where('subject_id', $this->subjectId)
            ->latest()
            ->get();

        return view('livewire.admin.shared.activity-timeline', [
            'activities' => $activities,
        ]);
    }
}