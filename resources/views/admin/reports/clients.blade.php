@extends('layouts.admin')

@section('title', 'تقرير العملاء')
@section('page_title', 'تقرير العملاء')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">تقرير العملاء</h4>
        <p class="text-muted mb-0">
            تقرير شامل للعملاء مع الفلاتر والمتابعات والعروض والمبيعات.
        </p>
    </div>
</div>

<livewire:admin.reports.client-report />

@endsection