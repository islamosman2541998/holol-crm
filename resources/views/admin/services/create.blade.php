@extends('layouts.admin')

@section('title', 'إضافة خدمة')
@section('page_title', 'إضافة خدمة')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات الخدمة</h5>
    </div>

    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.services.partials.form', [
                'service' => null,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.services.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection