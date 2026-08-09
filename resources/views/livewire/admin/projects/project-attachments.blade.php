<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">مرفقات المشروع</h5>
        </div>

        <div class="card-body">
            @can('projects.edit')
                <form wire:submit.prevent="save" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الملف</label>
                            <input type="file"
                                   wire:model="file"
                                   class="form-control @error('file') is-invalid @enderror">

                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div wire:loading wire:target="file" class="small text-muted mt-1">
                                جاري رفع الملف...
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ملاحظات على الملف</label>
                            <input type="text"
                                   wire:model.defer="notes"
                                   class="form-control"
                                   placeholder="اختياري">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary" wire:loading.attr="disabled" wire:target="file,save">
                            <i class="bi bi-paperclip"></i>
                            رفع المرفق
                        </button>
                    </div>
                </form>
            @endcan

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>الملف</th>
                            <th>الحجم</th>
                            <th>بواسطة</th>
                            <th>التاريخ</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($attachments as $attachment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $attachment->file_name }}
                                    </div>

                                    @if ($attachment->notes)
                                        <div class="small text-muted">
                                            {{ $attachment->notes }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $attachment->file_size_label }}</td>

                                <td>{{ $attachment->member?->name ?? $attachment->user?->name ?? 'System' }}</td>

                                <td>{{ $attachment->created_at->format('Y-m-d H:i') }}</td>

                                <td class="text-end">
                                    <a href="{{ route('admin.projects.attachments.download', ['project' => $project->id, 'attachment' => $attachment->id]) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-download"></i>
                                    </a>

                                    @can('projects.edit')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                wire:click="delete({{ $attachment->id }})"
                                                wire:confirm="هل أنت متأكد من حذف المرفق؟">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    لا توجد مرفقات حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>