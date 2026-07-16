@extends('layouts.admin')

@section('title', 'تعديل عملية بيع')
@section('page_title', 'تعديل عملية بيع')

@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">تعديل عملية البيع</h5>
        </div>

        <form method="POST" action="{{ route('admin.sales.update', $sale) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.sales.partials.form', [
                    'sale' => $sale,
                    'clients' => $clients,
                    'services' => $services,
                    'quotations' => $quotations,
                ])
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.sales.index') }}" class="btn btn-light">رجوع</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    تحديث
                </button>
            </div>
        </form>
    </div>

@endsection
