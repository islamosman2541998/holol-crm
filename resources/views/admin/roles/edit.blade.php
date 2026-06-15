@extends('layouts.admin')

@section('title', 'تعديل دور')
@section('page_title', 'تعديل دور')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل الدور</h5>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.roles.partials.form', [
                'role' => $role,
                'permissions' => $permissions,
                'rolePermissions' => $rolePermissions,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection