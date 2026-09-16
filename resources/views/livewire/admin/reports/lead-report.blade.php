<div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">عدد Leads</div>
                    <h4 class="mb-0">{{ number_format($stats['leads_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Leads جديدة</div>
                    <h4 class="mb-0">{{ number_format($stats['new_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Leads مؤهلة</div>
                    <h4 class="mb-0">{{ number_format($stats['qualified_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">تم تحويلها</div>
                    <h4 class="mb-0">{{ number_format($stats['converted_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">بدون متابعة</div>
                    <h5 class="mb-0">{{ number_format($stats['without_followups']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">متابعات متأخرة</div>
                    <h5 class="mb-0">{{ number_format($stats['overdue_followups']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Conversion Rate</div>
                    <h5 class="mb-0">{{ $stats['conversion_rate'] }}%</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">فلاتر التقرير</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="اسم / شركة / موبايل / إيميل"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="new">جديد</option>
                        <option value="contacted">تم التواصل</option>
                        <option value="qualified">مؤهل</option>
                        <option value="unqualified">غير مؤهل</option>
                        <option value="converted">تم تحويله</option>
                        <option value="lost">مفقود</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المصدر</label>
                    <select class="form-select" wire:model.live="source">
                        <option value="">كل المصادر</option>
                        @foreach ($sources as $sourceItem)
                            <option value="{{ $sourceItem }}">{{ $sourceItem }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المدينة</label>
                    <select class="form-select" wire:model.live="city">
                        <option value="">كل المدن</option>
                        @foreach ($cities as $cityItem)
                            <option value="{{ $cityItem }}">{{ $cityItem }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الموظف المسؤول</label>
                    <select class="form-select" wire:model.live="assignedTo">
                        <option value="">الكل</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->user_id }}">
                                {{ $member->name }}
                                @if ($member->team)
                                    - {{ $member->team->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>

                <div class="col-md-3">
                    <label class="form-label">المتابعات</label>
                    <select class="form-select" wire:model.live="followupState">
                        <option value="">كل Leads</option>
                        <option value="with">لديهم متابعات</option>
                        <option value="without">بدون متابعات</option>
                        <option value="today">متابعات اليوم</option>
                        <option value="overdue">متابعات متأخرة</option>
                        <option value="upcoming">متابعات قادمة</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">التحويل</label>
                    <select class="form-select" wire:model.live="conversionState">
                        <option value="">الكل</option>
                        <option value="converted">تم تحويلها لعملاء</option>
                        <option value="not_converted">لم تتحول بعد</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المهام</label>
                    <select class="form-select" wire:model.live="taskState">
                        <option value="">الكل</option>
                        <option value="with">لديها مهام</option>
                        <option value="without">بدون مهام</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المحذوف / الأرشيف</label>
                    <select class="form-select" wire:model.live="trashedState">
                        <option value="without">بدون المحذوف</option>
                        <option value="with">مع المحذوف</option>
                        <option value="only">المحذوف فقط</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">عرض</label>
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="col-md-12 d-flex justify-content-end gap-2">
                    <button type="button"
                            class="btn btn-light"
                            wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        تفريغ الفلاتر
                    </button>

                    <button type="button"
                            class="btn btn-success"
                            wire:click="exportExcel"
                            wire:loading.attr="disabled">
                        <i class="bi bi-file-earmark-excel"></i>
                        تصدير Excel حسب الفلتر
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">نتائج التقرير</h5>

            <div class="small text-muted">
                عدد النتائج: {{ $leads->total() }}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>التواصل</th>
                            <th>الحالة</th>
                            <th>المسؤول</th>
                            <th>المصدر / المدينة</th>
                            <th>آخر متابعة</th>
                            <th>المهام</th>
                            <th>التحويل</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">إجراء</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $lead->name }}</div>
                                    <div class="small text-muted">{{ $lead->company ?? '-' }}</div>
                                    <div class="small text-muted">{{ $lead->email ?? '-' }}</div>

                                    @if ($lead->trashed())
                                        <span class="badge bg-secondary mt-1">محذوف</span>
                                    @endif
                                </td>

                                <td>
                                    <div>موبايل: {{ $this->formatPhone($lead->mobile) }}</div>
                                    <div class="small text-muted">هاتف: {{ $this->formatPhone($lead->phone) }}</div>
                                </td>

                                <td>
                                    <span class="badge {{ $lead->status_badge_class }}">
                                        {{ $lead->status_label }}
                                    </span>
                                </td>

                                <td>
                                    {{ $lead->assignedMember?->name ?? $lead->assignedUser?->name ?? '-' }}

                                    @if ($lead->assignedMember?->team)
                                        <div class="small text-muted">
                                            {{ $lead->assignedMember->team->name }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div>{{ $lead->source ?? '-' }}</div>
                                    <div class="small text-muted">{{ $lead->city ?? '-' }}</div>
                                </td>

                                <td>
                                    @if ($lead->latestFollowup)
                                        <div class="small">
                                            {{ $lead->latestFollowup->next_followup_at?->format('Y-m-d') ?? '-' }}
                                        </div>

                                        <div class="small text-muted">
                                            {{ $lead->latestFollowup->status ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-muted">لا يوجد</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $lead->tasks_count }}
                                </td>

                                <td>
                                    @if ($lead->convertedClient)
                                        <a href="{{ route('admin.clients.show', $lead->convertedClient) }}">
                                            {{ $lead->convertedClient->name }}
                                        </a>

                                        <div class="small text-muted">
                                            {{ $lead->converted_at?->format('Y-m-d') ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-muted">لم يتحول</span>
                                    @endif
                                </td>

                                <td>{{ $lead->created_at?->format('Y-m-d') }}</td>

                                <td class="text-end">
                                    @if (! $lead->trashed())
                                        <a href="{{ route('admin.leads.show', $lead) }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">محذوف</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    لا توجد نتائج مطابقة للفلاتر الحالية
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>
