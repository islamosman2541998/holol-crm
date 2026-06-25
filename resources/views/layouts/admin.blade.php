<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') | {{ setting('general.system_name', 'Holol CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
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
</body>

</html>
