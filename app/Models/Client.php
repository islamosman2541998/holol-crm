<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assigned_to',
        'name',
        'company',
        'email',
        'phone',
        'mobile',
        'city',
        'source',
        'notes',
        'status',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديد',
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'lost' => 'مفقود',
            default => 'غير معروف',
        };
    }
    public function followups()
{
    return $this->hasMany(ClientFollowup::class);
}

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-info',
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'lost' => 'bg-danger',
            default => 'bg-dark',
        };
    }
}