@extends('layouts.admin')

@section('title', 'تعديل خدمة')
@section('page_title', 'تعديل خدمة')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل الخدمة</h5>
    </div>

    <form method="POST" action="{{ route('admin.services.update', $service) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.services.partials.form', [
                'service' => $service,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.services.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection