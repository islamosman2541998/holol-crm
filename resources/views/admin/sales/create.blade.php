@extends('layouts.admin')

@section('title', 'إضافة عملية بيع')
@section('page_title', 'إضافة عملية بيع')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات عملية البيع</h5>
    </div>

    <form method="POST" action="{{ route('admin.sales.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.sales.partials.form', [
                'sale' => null,
                'clients' => $clients,
                'services' => $services,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.sales.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection