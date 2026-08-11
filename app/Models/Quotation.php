<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'client_id',
        'lead_id',
        'user_id',
        'quotation_number',
        'subtotal',
        'vat',
        'total',
        'status',
        'quotation_date',
        'valid_until',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat' => 'decimal:2',
            'total' => 'decimal:2',
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'معلق',
            'open' => 'مفتوح',
            'closed' => 'مغلق',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'open' => 'bg-primary',
            'closed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-dark',
        };
    }

    public function getCanBeUsedInSaleAttribute(): bool
    {
        return $this->status === 'open' && ! $this->sale()->exists();
    }

    public function markAsOpen(): void
    {
        $this->update([
            'status' => 'open',
            'opened_at' => $this->opened_at ?? now(),
        ]);
    }

    public function markAsClosed(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $vat = round($subtotal * 0.14, 2);
        $total = $subtotal + $vat;

        $this->update([
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
        ]);
    }
}