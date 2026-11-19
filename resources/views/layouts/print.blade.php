<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Invoice Print') - MARSS CORPORATION</title>
    <link rel="shortcut icon" href="{{ asset('backend/assets/icons/favicon.svg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@400;500;600;700;800&family=Valley+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: portrait; margin: 0mm; }
        @media print {
            body { margin: 0; padding: 0; background: #fff !important; }
            .invoice-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; min-height: 100vh !important; padding: 12mm 15mm 10mm 15mm !important; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 text-black py-5 px-3">
    @yield('content')
</body>
</html>
