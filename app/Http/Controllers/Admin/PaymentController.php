<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index');
    }

    public function store(Request $request, Sale $sale)
    {
        abort_unless(auth()->user()->can('payments.create'), 403);

        $sale->load(['payments', 'quotation']);

        if ($sale->status === 'cancelled') {
            throw ValidationException::withMessages([
                'amount' => 'لا يمكن إضافة دفعة لعملية بيع ملغية.',
            ]);
        }

        if ($sale->remaining_amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'عملية البيع مدفوعة بالكامل بالفعل.',
            ]);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank_transfer,instapay,vodafone_cash,other'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ], [
            'amount.required' => 'مبلغ الدفعة مطلوب',
            'amount.numeric' => 'مبلغ الدفعة يجب أن يكون رقمًا',
            'amount.min' => 'مبلغ الدفعة يجب أن يكون أكبر من صفر',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
        ]);

        if ((float) $data['amount'] > (float) $sale->remaining_amount) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ الدفعة أكبر من المبلغ المتبقي.',
            ]);
        }

        DB::transaction(function () use ($sale, $data) {
            $payment = $sale->payments()->create([
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $payment->sale->refreshPaymentStatus();
        });

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function destroy(Payment $payment)
    {
        abort_unless(auth()->user()->can('payments.delete'), 403);

        $sale = $payment->sale;

        DB::transaction(function () use ($payment, $sale) {
            $payment->delete();

            if ($sale) {
                $sale->refreshPaymentStatus();
            }
        });

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'تم حذف الدفعة وتحديث حالة البيع بنجاح');
    }
}