@extends('layouts.admin')

@section('title', 'إضافة عضو')
@section('page_title', 'إضافة عضو')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">بيانات العضو</h5>
    </div>

    <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card-body">
            @include('admin.members.partials.form', [
                'member' => null,
                'users' => $users,
                'teams' => $teams,
                'managers' => $managers,
            ])
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('admin.members.index') }}" class="btn btn-light">رجوع</a>

            <button class="btn btn-primary">
                <i class="bi bi-save"></i>
                حفظ
            </button>
        </div>
    </form>
</div>

@endsection