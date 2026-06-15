@extends('layouts.admin')

@section('title', 'إضافة دور')
@section('page_title', 'إضافة دور')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات الدور</h5>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.roles.partials.form', [
                'role' => null,
                'permissions' => $permissions,
                'rolePermissions' => [],
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection