@extends('layouts.admin')

@section('title', 'عروض الأسعار')
@section('page_title', 'عروض الأسعار')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">عروض الأسعار</h4>
        <p class="text-muted mb-0">
            إدارة عروض الأسعار وربطها بالمبيعات.
        </p>
    </div>

    @can('quotations.create')
        <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            إضافة عرض سعر
        </a>
    @endcan
</div>

<livewire:admin.quotations.quotation-index />

@endsection