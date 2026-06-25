@extends('layouts.admin')

@section('title', 'إضافة Lead')
@section('page_title', 'إضافة Lead')

@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">بيانات العميل المحتمل</h5>
        </div>

        <form method="POST" action="{{ route('admin.leads.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.leads.partials.form', [
                    'lead' => null,
                    'members' => $members,
                ])
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.leads.index') }}" class="btn btn-light">رجوع</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    حفظ
                </button>
            </div>
        </form>
    </div>

@endsection
