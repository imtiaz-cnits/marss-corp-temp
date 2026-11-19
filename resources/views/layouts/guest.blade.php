<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MARSS CORPORATION | Sign In' }}</title>
    <link rel="shortcut icon" href="{{ asset('backend/assets/icons/favicon.svg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@400;500;600;700;800&family=Valley+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <script>
        (function(){
            var m = localStorage.getItem('lightMode') === 'dark' ? 'dark' : 'light';
            if (m === 'dark') document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('light-mode', m);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-[#0b0f19] text-slate-900 dark:text-slate-100">
    {{ $slot }}
    @livewireScripts
</body>
</html>
