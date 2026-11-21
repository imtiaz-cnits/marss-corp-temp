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
        @page { size: A4 portrait; margin: 16px; }
        .invoice-sheet {
            padding: 16px;
            box-sizing: border-box;
        }
        .print-footer-bottom {
            margin-top: auto;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .invoice-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                padding: 16px 16px 110px 16px !important;
                display: block !important;
            }
            .print-footer-bottom {
                position: fixed !important;
                bottom: 16px !important;
                left: 16px !important;
                right: 16px !important;
                background: #ffffff !important;
                padding-top: 12px !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 text-black p-4">
    @yield('content')
</body>
</html>
