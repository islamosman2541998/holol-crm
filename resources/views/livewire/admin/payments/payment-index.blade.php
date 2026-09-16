<div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">إجمالي المدفوع حسب الفلتر</div>
                    <div class="stat-value">{{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="اسم العميل / الشركة / رقم البيع"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">طريقة الدفع</label>
                    <select class="form-select" wire:model.live="paymentMethod">
                        <option value="">كل الطرق</option>
                        <option value="cash">كاش</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="instapay">InstaPay</option>
                        <option value="vodafone_cash">Vodafone Cash</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الموظف</label>
                    <select class="form-select" wire:model.live="userId">
                        <option value="">كل الموظفين</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date"
                           class="form-control"
                           wire:model.live="dateFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date"
                           class="form-control"
                           wire:model.live="dateTo">
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة المدفوعات</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>رقم البيع</th>
                            <th>العميل</th>
                            <th>القيمة</th>
                            <th>طريقة الدفع</th>
                            <th>تاريخ الدفع</th>
                            <th>الموظف</th>
                            <th>ملاحظات</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>
                                    @if ($payment->sale)
                                        <a href="{{ route('admin.sales.show', $payment->sale) }}"
                                           class="fw-semibold text-decoration-none">
                                            #{{ $payment->sale->id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $payment->sale?->client?->name ?? '-' }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $payment->sale?->client?->company ?? '-' }}
                                    </div>
                                </td>

                                <td class="fw-bold">
                                    {{ number_format($payment->amount, 2) }}
                                </td>

                                <td>{{ $payment->payment_method_label }}</td>

                                <td>{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</td>

                                <td>{{ $payment->user?->name ?? '-' }}</td>

                                <td>{{ $payment->notes ?? '-' }}</td>

                                <td class="text-end">
                                    @can('payments.delete')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="عكس الدفعة محاسبيًا"
                                                onclick="confirmReversePaymentFromIndex({{ $payment->id }})">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد مدفوعات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmReversePaymentFromIndex(id) {
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
