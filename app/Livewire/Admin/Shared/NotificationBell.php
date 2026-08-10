<?php

namespace App\Livewire\Admin\Shared;

use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('livewire.admin.shared.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
