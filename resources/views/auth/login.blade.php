@php
    $overlayHex = ltrim(setting('login.overlay_color', '#0b1220'), '#');

    if (strlen($overlayHex) === 3) {
        $overlayHex = $overlayHex[0] . $overlayHex[0] . $overlayHex[1] . $overlayHex[1] . $overlayHex[2] . $overlayHex[2];
    }

    $overlayRgb = hexdec(substr($overlayHex, 0, 2)) . ', ' . hexdec(substr($overlayHex, 2, 2)) . ', ' . hexdec(substr($overlayHex, 4, 2));

    $fontOptions = config('system-fonts.options');
    $defaultFont = config('system-fonts.default', 'cairo');
    $selectedFont = setting('appearance.font_family', $defaultFont);
    $systemFont = $fontOptions[$selectedFont] ?? $fontOptions[$defaultFont];

    $systemName = setting('general.system_name', 'Hulul CRM');
    $companyName = setting('general.company_name', 'Hulul');
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | {{ $systemName }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $systemFont['stylesheet'] }}" rel="stylesheet">

    <style>
        :root {
            --system-font-family: {!! $systemFont['family'] !!};
            --primary-color: {{ setting('appearance.primary_color', '#0d6efd') }};
            --button-radius: {{ setting('appearance.button_radius', '8px') }};
            --auth-overlay-rgb: {{ $overlayRgb }};
        }

        .auth-visual {
            background-image:
                @if (setting('login.login_background'))
                    url('{{ asset('storage/' . setting('login.login_background')) }}')
                @else
                    linear-gradient(135deg, var(--primary-color), #111827)
                @endif;
        }
    </style>
</head>
<body>

<div class="auth-split">
    {{-- Form side --}}
    <main class="auth-form-side">
        <div class="auth-form-wrap">
            <div class="auth-form-header">
                <span class="auth-eyebrow">{{ $systemName }}</span>
                <h1 class="auth-heading">مرحباً بعودتك</h1>
                <p class="auth-subheading">{{ setting('login.login_subtitle', 'تسجيل الدخول إلى لوحة التحكم') }}</p>
            </div>

            @if ($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        toastr.error(@json($errors->first()));
                    });
                </script>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email" class="auth-label">البريد الإلكتروني</label>
                    <div class="auth-input-group">
                        <i class="bi bi-envelope auth-input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control auth-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="name@company.com"
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                    @error('email')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">كلمة المرور</label>
                    <div class="auth-input-group">
                        <i class="bi bi-lock auth-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control auth-input @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        >
                        <button type="button" class="auth-toggle-password" data-toggle-password="password" aria-label="إظهار كلمة المرور">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check auth-remember">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">تذكرني</label>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    تسجيل الدخول
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <div class="auth-footer">
                © {{ date('Y') }} {{ $companyName }}. جميع الحقوق محفوظة.
            </div>
        </div>
    </main>

    {{-- Visual side --}}
    <aside class="auth-visual">
        <div class="auth-visual-content">
            <div class="auth-brand">
                @if (setting('login.login_logo'))
                    <img src="{{ asset('storage/' . setting('login.login_logo')) }}" alt="{{ $systemName }}">
                @else
                    <span class="auth-brand-fallback"><i class="bi bi-bar-chart-line"></i> {{ $systemName }}</span>
                @endif
            </div>

            <div class="auth-visual-text">
                <h2>إدارة عملائك ومشاريعك<br><span class="auth-highlight">من مكان واحد</span></h2>
                <p>منصة موحّدة لمتابعة العملاء والفريق والأداء بكل وضوح ودقة.</p>
            </div>
        </div>
    </aside>
</div>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.togglePassword);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
        });
    });
</script>

</body>
</html>
