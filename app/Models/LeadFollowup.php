<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'note',
        'next_followup_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'next_followup_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'call' => 'مكالمة',
            'whatsapp' => 'واتساب',
            'meeting' => 'اجتماع',
            'note' => 'ملاحظة',
            'email' => 'إيميل',
            default => 'غير معروف',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'call' => 'bi-telephone',
            'whatsapp' => 'bi-whatsapp',
            'meeting' => 'bi-calendar-check',
            'note' => 'bi-journal-text',
            'email' => 'bi-envelope',
            default => 'bi-chat-dots',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المتابعة',
            'done' => 'تمت',
            'cancelled' => 'ملغاة',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'done' => 'bg-success',
            'cancelled' => 'bg-secondary',
            default => 'bg-dark',
        };
    }
}