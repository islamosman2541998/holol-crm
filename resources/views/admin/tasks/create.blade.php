@extends('layouts.admin')

@section('title', 'إضافة مهمة')
@section('page_title', 'إضافة مهمة')

@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">بيانات المهمة</h5>
        </div>

        <form method="POST" action="{{ route('admin.tasks.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.tasks.partials.form', [
                    'task' => null,
                    'members' => $members,
                    'clients' => $clients,
                    'leads' => $leads,
                    'selectedClientId' => $selectedClientId ?? null,
                    'selectedLeadId' => $selectedLeadId ?? null,
                    'selectedMemberId' => $selectedMemberId ?? null,
                    'projects' => $projects,
                    'selectedProjectId' => $selectedProjectId ?? null,
                ])
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">رجوع</a>

                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    حفظ
                </button>
            </div>
        </form>
    </div>

@endsection
