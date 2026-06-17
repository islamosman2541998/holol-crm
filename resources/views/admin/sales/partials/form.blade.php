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
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label">العميل</label>
        <select name="client_id" class="form-select">
            <option value="">اختار العميل</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    @selected(old('client_id', $sale?->client_id) == $client->id)>
                    {{ $client->name }} - {{ $client->company ?? 'بدون شركة' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">تاريخ البيع</label>
        <input type="date"
               name="sold_at"
               class="form-control"
               value="{{ old('sold_at', $sale?->sold_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select">
            <option value="pending" @selected(old('status', $sale?->status ?? 'pending') === 'pending')>قيد الانتظار</option>
            <option value="partial" @selected(old('status', $sale?->status) === 'partial')>مدفوع جزئيًا</option>
            <option value="paid" @selected(old('status', $sale?->status) === 'paid')>مدفوع</option>
            <option value="cancelled" @selected(old('status', $sale?->status) === 'cancelled')>ملغي</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">طريقة الدفع</label>
        <select name="payment_method" class="form-select">
            <option value="cash" @selected(old('payment_method', $sale?->payment_method ?? 'cash') === 'cash')>كاش</option>
            <option value="bank_transfer" @selected(old('payment_method', $sale?->payment_method) === 'bank_transfer')>تحويل بنكي</option>
            <option value="instapay" @selected(old('payment_method', $sale?->payment_method) === 'instapay')>InstaPay</option>
            <option value="vodafone_cash" @selected(old('payment_method', $sale?->payment_method) === 'vodafone_cash')>Vodafone Cash</option>
            <option value="other" @selected(old('payment_method', $sale?->payment_method) === 'other')>أخرى</option>
        </select>
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
            <h5 class="mb-0">الخدمات</h5>

            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSaleItem()">
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
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeSaleItem(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="col-12">
                            <label class="form-label">ملاحظات الخدمة</label>
                            <input type="text"
                                   name="item_notes[]"
                                   class="form-control"
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
                    <div class="col-md-4">
                        <div class="text-muted small">Subtotal</div>
                        <div class="fw-bold fs-4" id="subtotalText">0.00</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">VAT</div>
                        <div class="fw-bold fs-4" id="vatText">0.00</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-4" id="totalText">0.00</div>
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

        document.getElementById('subtotalText').innerText = subtotal.toFixed(2);
        document.getElementById('vatText').innerText = vat.toFixed(2);
        document.getElementById('totalText').innerText = total.toFixed(2);
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

    document.addEventListener('input', function (event) {
        if (
            event.target.classList.contains('quantity-input') ||
            event.target.classList.contains('price-input') ||
            event.target.id === 'vat'
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
    });

    document.addEventListener('DOMContentLoaded', function () {
        calculateSaleTotals();
    });
</script>