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
        @page {
            size: A4 portrait;
            margin: 0;
        }
        .invoice-sheet {
            box-sizing: border-box;
            width: 100%;
            max-width: 8.27in;
            min-height: 270mm;
            margin: 0 auto;
            padding: 16px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            background: #fff;
        }
        .invoice-content {
            flex: 1 0 auto;
        }
        .print-footer-bottom {
            margin-top: auto !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                height: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 270mm !important;
                height: 100% !important;
                padding: 16px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
            }
            table {
                page-break-inside: auto;
            }
            thead {
                display: table-header-group !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tbody {
                page-break-inside: auto;
            }
            .print-footer-bottom {
                position: static !important;
                margin-top: auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                padding-top: 24px !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 text-black p-4">
    @yield('content')
</body>
</html>
