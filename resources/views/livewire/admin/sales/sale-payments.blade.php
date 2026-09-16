<div>
    @can('payments.create')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">إضافة دفعة</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">قيمة الدفعة</label>
                        <input type="number"
                               step="0.01"
                               min="1"
                               class="form-control @error('amount') is-invalid @enderror"
                               wire:model.defer="amount">

                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select class="form-select" wire:model.defer="payment_method">
                            <option value="cash">كاش</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="instapay">InstaPay</option>
                            <option value="vodafone_cash">Vodafone Cash</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">تاريخ الدفع</label>
                        <input type="date"
                               class="form-control @error('paid_at') is-invalid @enderror"
                               wire:model.defer="paid_at">

                        @error('paid_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button"
                                class="btn btn-primary w-100"
                                wire:click="save"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-save"></i>
                                حفظ الدفعة
                            </span>

                            <span wire:loading>
                                جاري الحفظ...
                            </span>
                        </button>
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea rows="2"
                                  class="form-control"
                                  wire:model.defer="notes"></textarea>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">المدفوعات</h5>

            <div class="small text-muted">
                المدفوع:
                <strong>{{ number_format($sale->paid_amount, 2) }}</strong>
                /
                الإجمالي:
                <strong>{{ number_format($sale->total, 2) }}</strong>
            </div>
        </div>

        <div class="card-body">
            @if ($payments->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>القيمة</th>
                                <th>طريقة الدفع</th>
                                <th>تاريخ الدفع</th>
                                <th>الموظف</th>
                                <th>ملاحظات</th>
                                <th class="text-end">الإجراءات</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td class="fw-bold">{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_method_label }}</td>
                                    <td>{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ $payment->user?->name ?? '-' }}</td>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                    <td class="text-end">
                                        @can('payments.delete')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="عكس الدفعة محاسبيًا"
                                                    onclick="confirmReversePayment({{ $payment->id }})">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    لا توجد مدفوعات حتى الآن
                </div>
            @endif
        </div>
    </div>

    @if ($reversedPayments->isNotEmpty())
        <div class="card border-danger-subtle shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-danger">سجل الدفعات المعكوسة</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>القيمة</th>
                            <th>تاريخ الدفع</th>
                            <th>عكسها</th>
                            <th>تاريخ العكس</th>
                            <th>السبب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reversedPayments as $payment)
                            <tr>
                                <td>{{ number_format($payment->reversal?->amount ?? $payment->amount, 2) }}</td>
                                <td>{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $payment->reversal?->user?->name ?? '-' }}</td>
                                <td>{{ $payment->reversal?->reversed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-wrap">{{ $payment->reversal?->reason ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <script>
        function confirmReversePayment(id) {
            Swal.fire({
                title: 'عكس الدفعة محاسبيًا',
                text: 'ستُستبعد الدفعة من التحصيل مع الاحتفاظ بسجل كامل للعملية.',
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'سبب عكس الدفعة',
                inputPlaceholder: 'اكتب سببًا واضحًا...',
                inputAttributes: {
                    maxlength: 1000,
                },
                showCancelButton: true,
                confirmButtonText: 'تأكيد العكس',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return 'اكتب سببًا واضحًا لا يقل عن 5 أحرف';
                    }
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('reverse', id, result.value.trim());
                }
            });
        }
    </script>
</div>
