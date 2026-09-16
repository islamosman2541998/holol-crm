<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReversal extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'reversed_by',
        'amount',
        'reason',
        'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reversed_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
