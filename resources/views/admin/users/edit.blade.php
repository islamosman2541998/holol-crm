@extends('layouts.admin')

@section('title', 'تعديل مستخدم')
@section('page_title', 'تعديل مستخدم')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل بيانات المستخدم</h5>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.users.partials.form', [
                'user' => $user,
                'roles' => $roles,
                'selectedRole' => $user->roles->first()?->name,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">رجوع</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection