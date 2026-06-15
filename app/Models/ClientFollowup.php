<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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