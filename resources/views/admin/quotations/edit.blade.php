@extends('layouts.admin')

@section('title', 'تعديل عرض سعر')
@section('page_title', 'تعديل عرض سعر')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل عرض السعر</h5>
    </div>

    <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.quotations.partials.form', [
                'quotation' => $quotation,
                'clients' => $clients,
                'leads' => $leads,
                'services' => $services,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection