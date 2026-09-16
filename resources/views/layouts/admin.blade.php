@php
    $fontOptions = config('system-fonts.options');
    $defaultFont = config('system-fonts.default', 'cairo');
    $selectedFont = setting('appearance.font_family', $defaultFont);
    $systemFont = $fontOptions[$selectedFont] ?? $fontOptions[$defaultFont];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') | {{ setting('general.system_name', 'Holol CRM') }}</title>

    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var theme = stored === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $systemFont['stylesheet'] }}" rel="stylesheet">
    @stack('styles')

    <style>
        :root {
            --system-font-family: {!! $systemFont['family'] !!};
            --primary-color: {{ setting('appearance.primary_color', '#0d6efd') }};
            --secondary-color: {{ setting('appearance.secondary_color', '#6c757d') }};
            --sidebar-bg: {{ setting('appearance.sidebar_bg', '#111827') }};
            --sidebar-text: {{ setting('appearance.sidebar_text', '#ffffff') }};
            --sidebar-active-bg: {{ setting('appearance.sidebar_active_bg', '#0d6efd') }};
            --sidebar-active-text: {{ setting('appearance.sidebar_active_text', '#ffffff') }};
            --topbar-bg: {{ setting('appearance.topbar_bg', '#ffffff') }};
            --button-radius: {{ setting('appearance.button_radius', '8px') }};
            --card-radius: {{ setting('appearance.card_radius', '14px') }};
        }
    </style>

    @livewireStyles
</head>

<body>

    <div class="admin-wrapper">
        @include('layouts.partials.sidebar')

        <div class="admin-main">
            @include('layouts.partials.topbar')

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                toastr.success(@json(session('success')));
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                toastr.error(@json(session('error')));
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                toastr.warning(@json(session('warning')));
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                toastr.error('راجع البيانات المطلوبة');
            });
        </script>
    @endif
@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const dropdown = button.closest('.sidebar-dropdown');

                if (!dropdown) {
                    return;
                }

                dropdown.classList.toggle('is-open');
            });
        });

        const themeToggle = document.getElementById('themeToggle');

        if (themeToggle) {
            const themeIcon = themeToggle.querySelector('i');

            const applyIcon = function (theme) {
                if (!themeIcon) {
                    return;
                }

                themeIcon.classList.toggle('bi-moon-stars', theme !== 'dark');
                themeIcon.classList.toggle('bi-sun', theme === 'dark');
            };

            applyIcon(document.documentElement.getAttribute('data-bs-theme'));

            themeToggle.addEventListener('click', function () {
                const html = document.documentElement;
                const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';

                html.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                applyIcon(next);
            });
        }
    });
</script>
</body>

</html>
