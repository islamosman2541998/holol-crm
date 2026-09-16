<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function create(Sale $sale, array $data, ?int $userId): Payment
    {
        return DB::transaction(function () use ($sale, $data, $userId) {
            $lockedSale = Sale::query()
                ->with('quotation')
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($lockedSale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'amount' => 'لا يمكن إضافة دفعة لعملية بيع ملغية.',
                ]);
            }

            $paidAmount = (float) $lockedSale->payments()->sum('amount');
            $remainingAmount = max((float) $lockedSale->total - $paidAmount, 0);
            $amount = (float) $data['amount'];

            if ($remainingAmount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'عملية البيع مدفوعة بالكامل بالفعل.',
                ]);
            }

            if ($amount > $remainingAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'مبلغ الدفعة أكبر من المبلغ المتبقي.',
                ]);
            }

            $payment = $lockedSale->payments()->create([
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $lockedSale->refreshPaymentStatus();

            return $payment;
        });
    }

    public function reverse(Payment $payment, string $reason, ?int $userId): PaymentReversal
    {
        $reason = trim($reason);

        if (mb_strlen($reason) < 5) {
            throw ValidationException::withMessages([
                'reversal_reason' => 'سبب عكس الدفعة يجب أن يكون واضحًا (5 أحرف على الأقل).',
            ]);
        }

        return DB::transaction(function () use ($payment, $reason, $userId) {
            $sale = Sale::query()
                ->with('quotation')
                ->lockForUpdate()
                ->findOrFail($payment->sale_id);

            $lockedPayment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $reversal = $lockedPayment->reversal()->create([
                'reversed_by' => $userId,
                'amount' => $lockedPayment->amount,
                'reason' => $reason,
                'reversed_at' => now(),
            ]);

            $lockedPayment->delete();
            $sale->refreshPaymentStatus();

            return $reversal;
        });
    }
}
