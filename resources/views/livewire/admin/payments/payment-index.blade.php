<div>
    @php
        $hasFilters = $search !== '' || $paymentMethod !== '' || $userId !== '' || $dateFrom !== '' || $dateTo !== '';
    @endphp

    <section class="stat-section stat-tone-finance">
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                        <div class="stat-title">إجمالي المدفوع {{ $hasFilters ? 'حسب التصفية' : '' }}</div>
                        <div class="stat-value">{{ number_format($totalAmount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المدفوعات</h2>
                <p class="list-card-sub">{{ number_format($payments->total()) }} دفعة{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
            </div>

            @if ($hasFilters)
                <div class="list-card-actions">
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                </div>
            @endif
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="paymentSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="paymentSearch" class="form-control" placeholder="اسم العميل / الشركة / رقم البيع"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="paymentMethod">طريقة الدفع</label>
                <select id="paymentMethod" class="form-select" wire:model.live="paymentMethod">
                    <option value="">كل الطرق</option>
                    <option value="cash">كاش</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="instapay">InstaPay</option>
                    <option value="vodafone_cash">Vodafone Cash</option>
                    <option value="other">أخرى</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="paymentUser">الموظف</label>
                <select id="paymentUser" class="form-select" wire:model.live="userId">
                    <option value="">كل الموظفين</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="paymentFrom">من تاريخ</label>
                <input type="date" id="paymentFrom" class="form-control" wire:model.live="dateFrom">
            </div>

            <div class="list-filter">
                <label class="form-label" for="paymentTo">إلى تاريخ</label>
                <input type="date" id="paymentTo" class="form-control" wire:model.live="dateTo">
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>رقم البيع</th>
                        <th>العميل</th>
                        <th>القيمة</th>
                        <th>طريقة الدفع</th>
                        <th>تاريخ الدفع</th>
                        <th>الموظف</th>
                        <th>ملاحظات</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td class="text-nowrap">
                                @if ($payment->sale)
                                    <a href="{{ route('admin.sales.show', $payment->sale) }}" class="cell-title">
                                        #{{ $payment->sale->id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="cell-person-name">{{ $payment->sale?->client?->name ?? '-' }}</span>
                                <span class="cell-sub">{{ $payment->sale?->client?->company ?? '-' }}</span>
                            </td>

                            <td class="cell-amount">{{ number_format($payment->amount, 2) }}</td>

                            <td class="text-nowrap">{{ $payment->payment_method_label }}</td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</span>
                            </td>

                            <td class="text-nowrap">{{ $payment->user?->name ?? '-' }}</td>

                            <td style="min-width: 180px;">{{ $payment->notes ?? '-' }}</td>

                            <td>
                                <div class="row-actions">
                                    @can('payments.delete')
                                        <button type="button" class="row-action is-danger"
                                            title="عكس الدفعة محاسبيًا" aria-label="عكس الدفعة محاسبيًا"
                                            onclick="confirmReversePaymentFromIndex({{ $payment->id }})">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد مدفوعات مطابقة للتصفية' : 'لا توجد مدفوعات حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="list-card-foot">
                {{ $payments->links() }}
            </div>
        @endif
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
