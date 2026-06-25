@extends('layouts.admin')

@section('title', 'تعديل مهمة')
@section('page_title', 'تعديل مهمة')

@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">تعديل المهمة</h5>
        </div>

        <form method="POST" action="{{ route('admin.tasks.update', $task) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.tasks.partials.form', [
                    'task' => $task,
                    'members' => $members,
                    'clients' => $clients,
                    'leads' => $leads,
                    'selectedClientId' => null,
                    'selectedLeadId' => null,
                    'selectedMemberId' => null,
                    'projects' => $projects,
                    'selectedProjectId' => null,
                ])
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">رجوع</a>

                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    تحديث
                </button>
            </div>
        </form>
    </div>

@endsection
