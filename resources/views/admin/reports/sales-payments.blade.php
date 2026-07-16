@extends('layouts.admin')

@section('title', 'تقرير المبيعات والمدفوعات')
@section('page_title', 'تقرير المبيعات والمدفوعات')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">تقرير المبيعات والمدفوعات</h4>
        <p class="text-muted mb-0">
            تقرير شامل للمبيعات، المدفوعات، المتبقي، وطرق الدفع حسب الفلاتر.
        </p>
    </div>
</div>

<livewire:admin.reports.sales-payments-report />

@endsection