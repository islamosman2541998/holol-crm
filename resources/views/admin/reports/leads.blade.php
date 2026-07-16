@extends('layouts.admin')

@section('title', 'تقرير Leads')
@section('page_title', 'تقرير Leads')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">تقرير Leads</h4>
        <p class="text-muted mb-0">
            تقرير شامل للـ Leads مع المتابعات، التحويل، المهام، والفلاتر.
        </p>
    </div>
</div>

<livewire:admin.reports.lead-report />

@endsection