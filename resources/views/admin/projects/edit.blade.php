@extends('layouts.admin')

@section('title', 'تعديل مشروع')
@section('page_title', 'تعديل مشروع')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">تعديل المشروع</h5>
    </div>

    <form method="POST" action="{{ route('admin.projects.update', $project) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('admin.projects.partials.form', [
                'project' => $project,
                'clients' => $clients,
                'services' => $services,
                'teams' => $teams,
                'members' => $members,
                'selectedClientId' => null,
                'selectedTeamId' => null,
                'selectedManagerId' => null,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                تحديث
            </button>
        </div>
    </form>
</div>

@endsection