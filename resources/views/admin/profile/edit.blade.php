@extends('layouts.admin')

@section('title', 'الملف الشخصي')
@section('page_title', 'الملف الشخصي')

@section('content')

    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">بياناتي الشخصية</h5>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $user->name) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $user->email) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">رقم الموبايل</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الدور</label>
                        <input type="text" class="form-control" value="{{ $user->getRoleNames()->first() ?? '-' }}" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">تغيير كلمة المرور</h5>
                <div class="text-muted small mt-1">
                    اسيبها فاضية لو مش عايز تغيّر كلمة المرور.
                </div>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    حفظ التغييرات
                </button>
            </div>
        </div>
    </form>

@endsection
