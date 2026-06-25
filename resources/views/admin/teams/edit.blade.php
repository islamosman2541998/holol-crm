@extends('layouts.admin')

@section('title', 'تعديل فريق')
@section('page_title', 'تعديل فريق')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل الفريق</h5>
    </div>

    <form method="POST" action="{{ route('admin.teams.update', $team) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.teams.partials.form', [
                'team' => $team,
                'managers' => $managers,
                'permissions' => $permissions,
                'teamPermissions' => $teamPermissions,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.teams.index') }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection