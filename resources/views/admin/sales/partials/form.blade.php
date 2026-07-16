@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $saleItems = old('service_id')
        ? collect(old('service_id'))->map(function ($serviceId, $index) {
            return [
                'service_id' => $serviceId,
                'quantity' => old('quantity')[$index] ?? 1,
                'unit_price' => old('unit_price')[$index] ?? 0,
                'item_notes' => old('item_notes')[$index] ?? null,
            ];
        })
        : ($sale?->items?->map(function ($item) {
            return [
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'item_notes' => $item->notes,
            ];
        }) ?? collect([
            [
                'service_id' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'item_notes' => '',
            ],
        ]));

    $quotationPayload = ($quotations ?? collect())->map(function ($quotation) {
        return [
            'id' => $quotation->id,
            'client_id' => $quotation->client_id,
            'quotation_number' => $quotation->quotation_number,
            'subtotal' => (float) $quotation->subtotal,
            'vat' => (float) $quotation->vat,
            'total' => (float) $quotation->total,
            'items' => $quotation->items->map(function ($item) {
                return [
                    'service_id' => $item->service_id,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'item_notes' => $item->notes,
                ];
            })->values(),
        ];
    })->values();
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label">العميل</label>
        <select name="client_id" class="form-select" id="saleClientSelect">
            <option value="">اختار العميل</option>

            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    @selected(old('client_id', $sale?->client_id) == $client->id)>
                    {{ $client->name }} - {{ $client->company ?? 'بدون شركة' }}
                </option>
            @endforeach
        </select>

        <div class="form-text">
            عند اختيار عرض سعر، سيتم تحديد العميل تلقائيًا حسب العرض.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">عرض سعر مرتبط</label>
        <select name="quotation_id" class="form-select" id="saleQuotationSelect">
            <option value="">بدون عرض سعر</option>

            @foreach (($quotations ?? collect()) as $quotation)
                <option value="{{ $quotation->id }}"
                        data-client-id="{{ $quotation->client_id }}"
                    @selected(old('quotation_id', $sale?->quotation_id ?? ($selectedQuotationId ?? null)) == $quotation->id)>
                    {{ $quotation->quotation_number }}
                    -
                    {{ $quotation->client?->name }}
                    -
                    {{ number_format($quotation->total, 2) }}
                </option>
            @endforeach
        </select>

        <div class="form-text">
            تظهر هنا عروض الأسعار المفتوحة فقط وغير المرتبطة بعملية بيع أخرى.
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">تاريخ البيع</label>
        <input type="date"
               name="sold_at"
               class="form-control"
               value="{{ old('sold_at', $sale?->sold_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3">
        @if ($sale)
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select">
                <option value="pending" @selected(old('status', $sale?->status ?? 'pending') === 'pending')>
                    قيد الانتظار
                </option>
                <option value="partial" @selected(old('status', $sale?->status) === 'partial')>
                    مدفوع جزئيًا
                </option>
                <option value="paid" @selected(old('status', $sale?->status) === 'paid')>
                    مدفوع
                </option>
                <option value="cancelled" @selected(old('status', $sale?->status) === 'cancelled')>
                    ملغي
                </option>
            </select>

            <div class="form-text">
                يتم تحديث الحالة تلقائيًا حسب المدفوعات، إلا في حالة الإلغاء.
            </div>
        @else
            <label class="form-label">حالة البيع</label>
            <div class="form-control bg-light">
                ستُحسب تلقائيًا من المبلغ المدفوع
            </div>
            <input type="hidden" name="status" value="pending">
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">طريقة الدفع</label>
        <select name="payment_method" class="form-select">
            <option value="cash" @selected(old('payment_method', $sale?->payment_method ?? 'cash') === 'cash')>
                كاش
            </option>
            <option value="bank_transfer" @selected(old('payment_method', $sale?->payment_method) === 'bank_transfer')>
                تحويل بنكي
            </option>
            <option value="instapay" @selected(old('payment_method', $sale?->payment_method) === 'instapay')>
                InstaPay
            </option>
            <option value="vodafone_cash" @selected(old('payment_method', $sale?->payment_method) === 'vodafone_cash')>
                Vodafone Cash
            </option>
            <option value="other" @selected(old('payment_method', $sale?->payment_method) === 'other')>
                أخرى
            </option>
        </select>
    </div>

    <div class="col-md-3">
        @if (! $sale)
            <label class="form-label">المبلغ المدفوع الآن</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="paid_amount"
                   id="paidAmount"
                   class="form-control"
                   value="{{ old('paid_amount', 0) }}">

            <div class="form-text">
                لو المبلغ يساوي الإجمالي، البيع هيبقى مدفوع وعرض السعر هيتقفل.
            </div>
        @else
            <label class="form-label">المدفوع / المتبقي</label>
            <div class="form-control bg-light">
                {{ number_format($sale->paid_amount, 2) }}
                /
                {{ number_format($sale->remaining_amount, 2) }}
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">VAT / ضريبة</label>
        <input type="number"
               step="0.01"
               min="0"
               name="vat"
               id="vat"
               class="form-control"
               value="{{ old('vat', $sale?->vat ?? 0) }}">
    </div>

    <div class="col-12">
        <hr>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">الخدمات</h5>
                <div class="small text-muted" id="quotationLockNote" style="display:none;">
                    الخدمات والأسعار مأخوذة من عرض السعر المختار.
                </div>
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    id="addSaleItemButton"
                    onclick="addSaleItem()">
                <i class="bi bi-plus-circle"></i>
                إضافة خدمة
            </button>
        </div>

        <div id="sale-items-wrapper">
            @foreach ($saleItems as $index => $item)
                <div class="sale-item-row border rounded-4 p-3 mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">الخدمة</label>
                            <select name="service_id[]" class="form-select service-select">
                                <option value="">اختار الخدمة</option>

                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}"
                                            data-price="{{ $service->default_price }}"
                                            @selected($item['service_id'] == $service->id)>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">الكمية</label>
                            <input type="number"
                                   min="1"
                                   name="quantity[]"
                                   class="form-control quantity-input"
                                   value="{{ $item['quantity'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">سعر الوحدة</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="unit_price[]"
                                   class="form-control price-input"
                                   value="{{ $item['unit_price'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">الإجمالي</label>
                            <input type="text"
                                   class="form-control item-total"
                                   value="0"
                                   readonly>
                        </div>

                        <div class="col-md-2">
                            <button type="button"
                                    class="btn btn-outline-danger w-100 remove-sale-item-button"
                                    onclick="removeSaleItem(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="col-12">
                            <label class="form-label">ملاحظات الخدمة</label>
                            <input type="text"
                                   name="item_notes[]"
                                   class="form-control item-notes-input"
                                   value="{{ $item['item_notes'] }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-md-3">
                        <div class="text-muted small">Subtotal</div>
                        <div class="fw-bold fs-4" id="subtotalText">0.00</div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">VAT</div>
                        <div class="fw-bold fs-4" id="vatText">0.00</div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-4" id="totalText">0.00</div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">Remaining</div>
                        <div class="fw-bold fs-4" id="remainingText">
                            {{ $sale ? number_format($sale->remaining_amount, 2) : '0.00' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">ملاحظات</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $sale?->notes) }}</textarea>
    </div>
</div>

<script>
    const quotations = @json($quotationPayload);

    function calculateSaleTotals() {
        let subtotal = 0;

        document.querySelectorAll('.sale-item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 0);
            const price = parseFloat(row.querySelector('.price-input')?.value || 0);
            const total = quantity * price;

            row.querySelector('.item-total').value = total.toFixed(2);

            subtotal += total;
        });

        const vat = parseFloat(document.getElementById('vat')?.value || 0);
        const total = subtotal + vat;
        const paidAmount = parseFloat(document.getElementById('paidAmount')?.value || 0);
        const remaining = Math.max(total - paidAmount, 0);

        document.getElementById('subtotalText').innerText = subtotal.toFixed(2);
        document.getElementById('vatText').innerText = vat.toFixed(2);
        document.getElementById('totalText').innerText = total.toFixed(2);

        const remainingText = document.getElementById('remainingText');
        if (remainingText) {
            remainingText.innerText = remaining.toFixed(2);
        }
    }

    function addSaleItem() {
        const wrapper = document.getElementById('sale-items-wrapper');
        const firstRow = wrapper.querySelector('.sale-item-row');
        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('quantity-input')) {
                input.value = 1;
            } else if (input.classList.contains('price-input')) {
                input.value = 0;
            } else if (input.classList.contains('item-total')) {
                input.value = 0;
            } else {
                input.value = '';
            }
        });

        clone.querySelectorAll('select').forEach(select => {
            select.value = '';
        });

        clone.querySelectorAll('input, select, button').forEach(element => {
            element.disabled = false;
        });

        wrapper.appendChild(clone);

        calculateSaleTotals();
    }

    function removeSaleItem(button) {
        const rows = document.querySelectorAll('.sale-item-row');

        if (rows.length === 1) {
            toastr.warning('يجب وجود خدمة واحدة على الأقل');
            return;
        }

        button.closest('.sale-item-row').remove();

        calculateSaleTotals();
    }

    function setQuotationLock(isLocked) {
        document.querySelectorAll('.sale-item-row').forEach(row => {
            row.querySelectorAll('.service-select, .quantity-input, .price-input, .item-notes-input').forEach(input => {
                input.disabled = isLocked;
            });

            const removeButton = row.querySelector('.remove-sale-item-button');
            if (removeButton) {
                removeButton.disabled = isLocked;
            }
        });

        const addButton = document.getElementById('addSaleItemButton');
        if (addButton) {
            addButton.disabled = isLocked;
        }

        const vatInput = document.getElementById('vat');
        if (vatInput) {
            vatInput.readOnly = isLocked;
        }

        const note = document.getElementById('quotationLockNote');
        if (note) {
            note.style.display = isLocked ? 'block' : 'none';
        }
    }

    function applyQuotation(quotation) {
        const clientSelect = document.getElementById('saleClientSelect');
        const wrapper = document.getElementById('sale-items-wrapper');

        if (clientSelect) {
            clientSelect.value = quotation.client_id;
        }

        while (wrapper.querySelectorAll('.sale-item-row').length < quotation.items.length) {
            addSaleItem();
        }

        while (wrapper.querySelectorAll('.sale-item-row').length > quotation.items.length && quotation.items.length > 0) {
            wrapper.querySelector('.sale-item-row:last-child').remove();
        }

        const rows = wrapper.querySelectorAll('.sale-item-row');

        quotation.items.forEach((item, index) => {
            const row = rows[index];

            if (!row) {
                return;
            }

            row.querySelector('.service-select').value = item.service_id;
            row.querySelector('.quantity-input').value = item.quantity;
            row.querySelector('.price-input').value = item.unit_price;
            row.querySelector('.item-notes-input').value = item.item_notes || '';
        });

        const vatInput = document.getElementById('vat');
        if (vatInput) {
            vatInput.value = quotation.vat || 0;
        }

        setQuotationLock(true);
        calculateSaleTotals();
    }

    function clearQuotationLock() {
        setQuotationLock(false);
        calculateSaleTotals();
    }

    function filterQuotationOptionsByClient() {
        const clientSelect = document.getElementById('saleClientSelect');
        const quotationSelect = document.getElementById('saleQuotationSelect');

        if (!clientSelect || !quotationSelect) {
            return;
        }

        const selectedClientId = clientSelect.value;

        Array.from(quotationSelect.options).forEach(option => {
            if (!option.value) {
                option.disabled = false;
                return;
            }

            option.disabled = selectedClientId && option.dataset.clientId !== selectedClientId;
        });

        const selectedOption = quotationSelect.options[quotationSelect.selectedIndex];

        if (
            selectedOption &&
            selectedOption.value &&
            selectedClientId &&
            selectedOption.dataset.clientId !== selectedClientId
        ) {
            quotationSelect.value = '';
            clearQuotationLock();
        }
    }

    document.addEventListener('input', function (event) {
        if (
            event.target.classList.contains('quantity-input') ||
            event.target.classList.contains('price-input') ||
            event.target.id === 'vat' ||
            event.target.id === 'paidAmount'
        ) {
            calculateSaleTotals();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('service-select')) {
            const selectedOption = event.target.options[event.target.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            const row = event.target.closest('.sale-item-row');

            row.querySelector('.price-input').value = price;

            calculateSaleTotals();
        }

        if (event.target.id === 'saleClientSelect') {
            filterQuotationOptionsByClient();
        }

        if (event.target.id === 'saleQuotationSelect') {
            const quotationId = parseInt(event.target.value || 0);
            const quotation = quotations.find(item => parseInt(item.id) === quotationId);

            if (quotation) {
                applyQuotation(quotation);
            } else {
                clearQuotationLock();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        filterQuotationOptionsByClient();

        const quotationSelect = document.getElementById('saleQuotationSelect');

        if (quotationSelect && quotationSelect.value) {
            const quotationId = parseInt(quotationSelect.value || 0);
            const quotation = quotations.find(item => parseInt(item.id) === quotationId);

            if (quotation) {
                applyQuotation(quotation);
            }
        } else {
            calculateSaleTotals();
        }
    });
</script>