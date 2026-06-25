@extends('layouts.admin')

@section('title', 'إضافة مشروع')
@section('page_title', 'إضافة مشروع')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات المشروع</h5>
    </div>

    <form method="POST" action="{{ route('admin.projects.store') }}">
        @csrf

        <div class="card-body">
            @include('admin.projects.partials.form', [
                'project' => null,
                'clients' => $clients,
                'services' => $services,
                'teams' => $teams,
                'members' => $members,
                'selectedClientId' => $selectedClientId ?? null,
                'selectedTeamId' => $selectedTeamId ?? null,
                'selectedManagerId' => $selectedManagerId ?? null,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection