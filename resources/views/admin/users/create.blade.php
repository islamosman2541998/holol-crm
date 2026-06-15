@extends('layouts.admin')

@section('title', 'إضافة مستخدم')
@section('page_title', 'إضافة مستخدم')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات المستخدم</h5>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.users.partials.form', [
                'user' => null,
                'roles' => $roles,
                'selectedRole' => null,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection