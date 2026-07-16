@extends('layouts.admin')

@section('title', 'تقرير عروض الأسعار')
@section('page_title', 'تقرير عروض الأسعار')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">تقرير عروض الأسعار</h4>
        <p class="text-muted mb-0">
            تقرير شامل لعروض الأسعار، حالاتها، صلاحيتها، وتحويلها لمبيعات.
        </p>
    </div>
</div>

<livewire:admin.reports.quotation-report />

@endsection