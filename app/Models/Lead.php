<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assigned_to',
        'converted_client_id',
        'name',
        'company',
        'email',
        'mobile',
        'phone',
        'city',
        'source',
        'status',
        'notes',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
        ];
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديد',
            'contacted' => 'تم التواصل',
            'qualified' => 'مؤهل',
            'unqualified' => 'غير مؤهل',
            'converted' => 'تم تحويله',
            'lost' => 'مفقود',
            default => 'غير معروف',
        };
    }
public function followups()
{
    return $this->hasMany(LeadFollowup::class);
}
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-info',
            'contacted' => 'bg-primary',
            'qualified' => 'bg-success',
            'unqualified' => 'bg-secondary',
            'converted' => 'bg-success',
            'lost' => 'bg-danger',
            default => 'bg-dark',
        };
    }
}