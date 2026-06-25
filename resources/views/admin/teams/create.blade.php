@extends('layouts.admin')

@section('title', 'إضافة فريق')
@section('page_title', 'إضافة فريق')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات الفريق</h5>
    </div>

    <form method="POST" action="{{ route('admin.teams.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.teams.partials.form', [
                'team' => null,
                'managers' => $managers,
                'permissions' => $permissions,
                'teamPermissions' => [],
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.teams.index') }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection