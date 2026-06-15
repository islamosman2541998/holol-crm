@php
    $cardBg = setting('login.card_bg', '#ffffff');
    $cardOpacity = (float) setting('login.card_opacity', '1');

    $hex = ltrim($cardBg, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $cardBackground = "rgba({$r}, {$g}, {$b}, {$cardOpacity})";
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | {{ setting('general.system_name', 'Holol CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: {{ setting('appearance.primary_color', '#0d6efd') }};
            --button-radius: {{ setting('appearance.button_radius', '8px') }};
            --card-radius: {{ setting('appearance.card_radius', '14px') }};
        }

        .auth-page {
            background:
                @if(setting('login.login_background'))
                    url('{{ asset('storage/' . setting('login.login_background')) }}')
                @else
                    linear-gradient(135deg, #0d6efd, #111827)
                @endif;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .auth-card {
            background-color: {{ $cardBackground }} !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card card border-0 shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="auth-logo mx-auto mb-3">
                    @if (setting('login.login_logo'))
                        <img src="{{ asset('storage/' . setting('login.login_logo')) }}" alt="Login Logo">
                    @else
                        <i class="bi bi-bar-chart-line"></i>
                    @endif
                </div>

                {{-- <h1 class="h4 mb-1 text-white">{{ setting('login.login_title', 'Holol CRM') }}</h1> --}}
                <p class=" mb-0 text-white">{{ setting('login.login_subtitle', 'تسجيل الدخول إلى لوحة التحكم') }}</p>
            </div>

            @if ($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        toastr.error(@json($errors->first()));
                    });
                </script>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label text-white">البريد الإلكتروني</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="example@email.com"
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">كلمة المرور</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-white" for="remember">
                            تذكرني
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    دخول
                </button>
            </form>

            <div class="text-center mt-4 small text-muted">
                © {{ date('Y') }} {{ setting('general.company_name', 'Holol') }}
            </div>
        </div>
    </div>
</div>

</body>
</html>