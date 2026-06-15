@extends('layouts.admin')

@section('title', 'الإعدادات')
@section('page_title', 'إعدادات السيستم')

@section('content')



    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <ul class="nav nav-pills gap-2" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#general" type="button">
                            <i class="bi bi-sliders"></i>
                            عام
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#branding" type="button">
                            <i class="bi bi-image"></i>
                            اعدادات الشعار
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#login" type="button">
                            <i class="bi bi-box-arrow-in-right"></i>
                            صفحة الدخول
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#appearance" type="button">
                            <i class="bi bi-palette"></i>
                            التصميم
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    <div class="tab-pane fade show active" id="general">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">اسم السيستم</label>
                                <input type="text" name="system_name" class="form-control"
                                    value="{{ old('system_name', setting('general.system_name', 'Holol CRM')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" name="company_name" class="form-control"
                                    value="{{ old('company_name', setting('general.company_name')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الإيميل</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', setting('general.email')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الموبايل</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', setting('general.phone')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="branding">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">لوجو السيستم</label>
                                <input type="file" name="system_logo" class="form-control" accept="image/*">

                                @if (setting('branding.system_logo'))
                                    <img src="{{ asset('storage/' . setting('branding.system_logo')) }}"
                                        class="settings-preview mt-3">
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon" class="form-control" accept="image/*">

                                @if (setting('branding.favicon'))
                                    <img src="{{ asset('storage/' . setting('branding.favicon')) }}"
                                        class="settings-preview mt-3">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="login">
                        <div class="row g-4">
                            {{-- <div class="col-md-6">
                                <label class="form-label">عنوان صفحة الدخول</label>
                                <input type="text" name="login_title" class="form-control"
                                    value="{{ old('login_title', setting('login.login_title')) }}">
                            </div> --}}

                            <div class="col-md-6">
                                <label class="form-label">وصف صفحة الدخول</label>
                                <input type="text" name="login_subtitle" class="form-control"
                                    value="{{ old('login_subtitle', setting('login.login_subtitle')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">لوجو صفحة الدخول</label>
                                <input type="file" name="login_logo" class="form-control" accept="image/*">

                                @if (setting('login.login_logo'))
                                    <img src="{{ asset('storage/' . setting('login.login_logo')) }}"
                                        class="settings-preview mt-3">
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">خلفية صفحة الدخول</label>
                                <input type="file" name="login_background" class="form-control" accept="image/*">

                                @if (setting('login.login_background'))
                                    <img src="{{ asset('storage/' . setting('login.login_background')) }}"
                                        class="settings-preview-wide mt-3">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">لون خلفية كارد الدخول</label>
                                <input type="color" name="card_bg" class="form-control form-control-color"
                                    value="{{ old('card_bg', setting('login.card_bg', '#ffffff')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">شفافية كارد الدخول</label>
                                <input type="number" step="0.05" min="0" max="1" name="card_opacity"
                                    class="form-control"
                                    value="{{ old('card_opacity', setting('login.card_opacity', '1')) }}">

                                <small class="text-muted">
                                    1 = بدون شفافية، 0.8 = شفاف بنسبة بسيطة
                                </small>
                            </div>
                            {{-- <div class="col-md-6">
                            <label class="form-label">لون الطبقة فوق الخلفية</label>
                            <input type="color" name="overlay_color" class="form-control form-control-color"
                                   value="{{ old('overlay_color', setting('login.overlay_color', '#111827')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">شفافية الطبقة</label>
                            <input type="number" step="0.05" min="0" max="1" name="overlay_opacity" class="form-control"
                                   value="{{ old('overlay_opacity', setting('login.overlay_opacity', '0.75')) }}">
                        </div> --}}
                        </div>
                    </div>

                    <div class="tab-pane fade" id="appearance">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Primary Color</label>
                                <input type="color" name="primary_color" class="form-control form-control-color"
                                    value="{{ old('primary_color', setting('appearance.primary_color', '#0d6efd')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Secondary Color</label>
                                <input type="color" name="secondary_color" class="form-control form-control-color"
                                    value="{{ old('secondary_color', setting('appearance.secondary_color', '#6c757d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Topbar Background</label>
                                <input type="color" name="topbar_bg" class="form-control form-control-color"
                                    value="{{ old('topbar_bg', setting('appearance.topbar_bg', '#ffffff')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sidebar Background</label>
                                <input type="color" name="sidebar_bg" class="form-control form-control-color"
                                    value="{{ old('sidebar_bg', setting('appearance.sidebar_bg', '#111827')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sidebar Text</label>
                                <input type="color" name="sidebar_text" class="form-control form-control-color"
                                    value="{{ old('sidebar_text', setting('appearance.sidebar_text', '#ffffff')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sidebar Active Background</label>
                                <input type="color" name="sidebar_active_bg" class="form-control form-control-color"
                                    value="{{ old('sidebar_active_bg', setting('appearance.sidebar_active_bg', '#0d6efd')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sidebar Active Text</label>
                                <input type="color" name="sidebar_active_text" class="form-control form-control-color"
                                    value="{{ old('sidebar_active_text', setting('appearance.sidebar_active_text', '#ffffff')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Button Radius</label>
                                <input type="text" name="button_radius" class="form-control"
                                    value="{{ old('button_radius', setting('appearance.button_radius', '8px')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Card Radius</label>
                                <input type="text" name="card_radius" class="form-control"
                                    value="{{ old('card_radius', setting('appearance.card_radius', '14px')) }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i>
                    حفظ الإعدادات
                </button>
            </div>
        </div>
    </form>

@endsection
