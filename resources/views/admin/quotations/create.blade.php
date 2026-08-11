@extends('layouts.admin')

@section('title', 'إضافة عرض سعر')
@section('page_title', 'إضافة عرض سعر')

@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">بيانات عرض السعر</h5>
        </div>

        <form method="POST" action="{{ route('admin.quotations.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.quotations.partials.form', [
                    'quotation' => null,
                    'clients' => $clients,
                    'leads' => $leads,
                    'services' => $services,
                    'selectedClientId' => $selectedClientId ?? null,
                    'selectedLeadId' => $selectedLeadId ?? null,
                ])
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-light">رجوع</a>

                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    حفظ
                </button>
            </div>
        </form>
    </div>

@endsection
