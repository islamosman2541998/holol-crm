@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label">اسم الخدمة</label>
        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $service?->name) }}"
               placeholder="مثال: Website Development">
    </div>

    <div class="col-md-6">
        <label class="form-label">كود الخدمة</label>
        <input type="text"
               name="code"
               class="form-control"
               value="{{ old('code', $service?->code) }}"
               placeholder="مثال: website">
    </div>

    <div class="col-md-6">
        <label class="form-label">السعر الافتراضي</label>
        <input type="number"
               step="0.01"
               min="0"
               name="default_price"
               class="form-control"
               value="{{ old('default_price', $service?->default_price ?? 0) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label d-block">الحالة</label>

        <input type="hidden" name="status" value="0">

        <div class="form-check form-switch">
            <input class="form-check-input"
                   type="checkbox"
                   name="status"
                   value="1"
                   id="status"
                   @checked(old('status', $service?->status ?? true))>

            <label class="form-check-label" for="status">
                الخدمة نشطة
            </label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">الوصف</label>
        <textarea name="description"
                  rows="4"
                  class="form-control"
                  placeholder="اكتب وصف مختصر للخدمة">{{ old('description', $service?->description) }}</textarea>
    </div>
</div>