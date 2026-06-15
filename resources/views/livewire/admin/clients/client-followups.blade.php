<div>
    @can('followups.create')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">إضافة متابعة</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">ملاحظة المتابعة</label>
                        <textarea
                            class="form-control @error('note') is-invalid @enderror"
                            rows="3"
                            wire:model.defer="note"
                            placeholder="اكتب تفاصيل المتابعة هنا"></textarea>

                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">ميعاد المتابعة القادمة</label>
                            <input
                                type="datetime-local"
                                class="form-control @error('next_followup_at') is-invalid @enderror"
                                wire:model.defer="next_followup_at">

                            @error('next_followup_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">الحالة</label>
                            <select class="form-select @error('status') is-invalid @enderror" wire:model.defer="status">
                                <option value="pending">قيد المتابعة</option>
                                <option value="done">تمت</option>
                                <option value="cancelled">ملغاة</option>
                            </select>

                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="bi bi-save"></i>
                        حفظ المتابعة
                    </span>

                    <span wire:loading>
                        جاري الحفظ...
                    </span>
                </button>
            </div>
        </div>
    @endcan

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">سجل المتابعات</h5>
        </div>

        <div class="card-body">
            @forelse ($followups as $followup)
                <div class="followup-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="mb-2">
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
                            @can('followups.edit')
                                @if ($followup->status !== 'done')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success"
                                            wire:click="markAsDone({{ $followup->id }})">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                @endif
                            @endcan

                            @can('followups.delete')
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="confirmLivewireDeleteFollowup({{ $followup->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد متابعات لهذا العميل
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function confirmLivewireDeleteFollowup(id) {
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