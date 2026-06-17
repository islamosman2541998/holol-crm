<div>
    @can('leads.edit')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">إضافة متابعة للـ Lead</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">نوع المتابعة</label>
                        <select class="form-select" wire:model.defer="type">
                            <option value="note">ملاحظة</option>
                            <option value="call">مكالمة</option>
                            <option value="whatsapp">واتساب</option>
                            <option value="meeting">اجتماع</option>
                            <option value="email">إيميل</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ميعاد المتابعة القادمة</label>
                        <input type="datetime-local"
                               class="form-control @error('next_followup_at') is-invalid @enderror"
                               wire:model.defer="next_followup_at">

                        @error('next_followup_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" wire:model.defer="status">
                            <option value="pending">قيد المتابعة</option>
                            <option value="done">تمت</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button"
                                class="btn btn-primary w-100"
                                wire:click="save"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-save"></i>
                                حفظ المتابعة
                            </span>

                            <span wire:loading>
                                جاري الحفظ...
                            </span>
                        </button>
                    </div>

                    <div class="col-12">
                        <label class="form-label">الملاحظة</label>
                        <textarea rows="3"
                                  class="form-control @error('note') is-invalid @enderror"
                                  wire:model.defer="note"
                                  placeholder="اكتب تفاصيل التواصل أو المتابعة هنا"></textarea>

                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">سجل متابعات الـ Lead</h5>
        </div>

        <div class="card-body">
            @forelse ($followups as $followup)
                <div class="lead-followup-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="mb-2 d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark">
                                    <i class="bi {{ $followup->type_icon }}"></i>
                                    {{ $followup->type_label }}
                                </span>

                                <span class="badge {{ $followup->status_badge_class }}">
                                    {{ $followup->status_label }}
                                </span>

                                @if ($followup->next_followup_at)
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $followup->next_followup_at->format('Y-m-d H:i') }}
                                    </span>
                                @endif
                            </div>

                            <p class="mb-2">{{ $followup->note }}</p>

                            <small class="text-muted">
                                بواسطة: {{ $followup->user?->name ?? '-' }}
                                -
                                {{ $followup->created_at->format('Y-m-d H:i') }}
                            </small>
                        </div>

                        <div class="d-flex gap-2 align-items-start">
                            @can('leads.edit')
                                @if ($followup->status !== 'done')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success"
                                            wire:click="markAsDone({{ $followup->id }})">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                @endif

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDeleteLeadFollowup({{ $followup->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد متابعات لهذا الـ Lead
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function confirmDeleteLeadFollowup(id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'سيتم حذف المتابعة',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', id);
                }
            });
        }
    </script>
</div>