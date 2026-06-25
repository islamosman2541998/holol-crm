<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'status' => 'boolean',
        ];
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? 'نشط' : 'غير نشط';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status ? 'bg-success' : 'bg-secondary';
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
