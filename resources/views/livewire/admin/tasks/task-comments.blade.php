<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">تعليقات المهمة</h5>
        </div>

        <div class="card-body">
            @can('tasks.view')
                <form wire:submit.prevent="save" class="mb-4">
                    <label class="form-label">إضافة تعليق</label>

                    <textarea wire:model.defer="comment"
                              rows="3"
                              class="form-control @error('comment') is-invalid @enderror"
                              placeholder="اكتب تحديث أو تعليق على المهمة"></textarea>

                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-chat-dots"></i>
                            إضافة تعليق
                        </button>
                    </div>
                </form>
            @endcan

            @forelse ($comments as $comment)
                <div class="task-comment-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">
                                {{ $comment->member?->name ?? $comment->user?->name ?? 'System' }}
                            </div>

                            <div class="text-muted small">
                                {{ $comment->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        @can('tasks.edit')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    wire:click="delete({{ $comment->id }})"
                                    wire:confirm="هل أنت متأكد من حذف التعليق؟">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcan
                    </div>

                    <div class="mt-2">
                        {{ $comment->comment }}
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد تعليقات حتى الآن
                </div>
            @endforelse
        </div>
    </div>
</div>