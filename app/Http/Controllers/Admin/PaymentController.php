<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\PaymentService;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use AuthorizesOwnedRecords;

    public function index()
    {
        return view('admin.payments.index');
    }

    public function store(Request $request, Sale $sale, PaymentService $paymentService)
    {
        abort_unless(auth()->user()->can('payments.create'), 403);

        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

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

        $paymentService->create($sale, $data, auth()->id());

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function destroy(Request $request, Payment $payment, PaymentService $paymentService)
    {
        abort_unless(auth()->user()->can('payments.delete'), 403);

        $sale = $payment->sale;

        abort_unless($sale, 404);

        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        $data = $request->validate([
            'reversal_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reversal_reason.required' => 'سبب عكس الدفعة مطلوب.',
            'reversal_reason.min' => 'سبب عكس الدفعة يجب أن يكون واضحًا (5 أحرف على الأقل).',
        ]);

        $paymentService->reverse($payment, $data['reversal_reason'], auth()->id());

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'تم عكس الدفعة محاسبيًا وتحديث حالة البيع بنجاح');
    }
}
