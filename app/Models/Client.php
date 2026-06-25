<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActivityLogs;
use App\Models\ActivityLog;

class Client extends Model
{
    use HasFactory, SoftDeletes , HasActivityLogs;

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
public function latestActivity()
{
    return $this->morphOne(ActivityLog::class, 'subject')->latestOfMany();
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
public function latestFollowup()
{
    return $this->hasOne(ClientFollowup::class)->latestOfMany();
}
public function tasks()
{
    return $this->hasMany(Task::class);
}

public function sales()
{
    return $this->hasMany(Sale::class);
}
public function projects()
{
    return $this->hasMany(Project::class);
}
public function assignedMember()
{
    return $this->hasOne(Member::class, 'user_id', 'assigned_to');
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