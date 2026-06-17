<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'subtotal',
        'vat',
        'total',
        'payment_method',
        'status',
        'sold_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat' => 'decimal:2',
            'total' => 'decimal:2',
            'sold_at' => 'date',
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

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'partial' => 'مدفوع جزئيًا',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'partial' => 'bg-info',
            'paid' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-dark',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'كاش',
            'bank_transfer' => 'تحويل بنكي',
            'instapay' => 'InstaPay',
            'vodafone_cash' => 'Vodafone Cash',
            'other' => 'أخرى',
            default => 'غير معروف',
        };
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max((float) $this->total - $this->paid_amount, 0);
    }
}
