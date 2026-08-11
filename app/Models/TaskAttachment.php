<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'member_id',
        'type',
        'link_url',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'notes',
    ];

    public function getIsLinkAttribute(): bool
    {
        return $this->type === 'link';
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getFileSizeLabelAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        }

        if ($this->file_size < 1024 * 1024) {
            return round($this->file_size / 1024, 2) . ' KB';
        }

        return round($this->file_size / 1024 / 1024, 2) . ' MB';
    }
}