<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">مراحل المشروع</h5>

            @if ($editingId)
                <button type="button" class="btn btn-sm btn-light" wire:click="resetForm">
                    إلغاء التعديل
                </button>
            @endif
        </div>

        <div class="card-body">
            @can('projects.edit')
                <form wire:submit.prevent="save" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">عنوان المرحلة</label>
                            <input type="text"
                                   wire:model.defer="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="مثال: تصميم الواجهة">

                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select wire:model.defer="status" class="form-select">
                                <option value="pending">لم تبدأ</option>
                                <option value="in_progress">قيد التنفيذ</option>
                                <option value="completed">مكتملة</option>
                                <option value="cancelled">ملغية</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">تاريخ التسليم</label>
                            <input type="date"
                                   wire:model.defer="due_date"
                                   class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">وصف المرحلة</label>
                            <textarea wire:model.defer="description"
                                      rows="2"
                                      class="form-control"
                                      placeholder="اختياري"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            {{ $editingId ? 'تحديث المرحلة' : 'إضافة مرحلة' }}
                        </button>
                    </div>
                </form>
            @endcan

            @forelse ($milestones as $milestone)
                <div class="project-milestone-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">
                                {{ $milestone->title }}
                            </div>

                            @if ($milestone->description)
                                <div class="text-muted small">
                                    {{ $milestone->description }}
                                </div>
                            @endif

                            <div class="small text-muted mt-1">
                                التسليم:
                                {{ $milestone->due_date?->format('Y-m-d') ?? '-' }}

                                @if ($milestone->is_overdue)
                                    <span class="text-danger ms-1">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        متأخرة
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="mb-2">
                                <span class="badge {{ $milestone->status_badge_class }}">
                                    {{ $milestone->status_label }}
                                </span>
                            </div>

                            @can('projects.edit')
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        wire:click="edit({{ $milestone->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="delete({{ $milestone->id }})"
                                        wire:confirm="هل أنت متأكد من حذف المرحلة؟">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد مراحل للمشروع حتى الآن
                </div>
            @endforelse
        </div>
    </div>
</div>