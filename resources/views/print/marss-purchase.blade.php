@extends('layouts.app')

@section('content')
@php
    use App\Support\Money;

    $subTotal = $subTotal ?? '0.00';
    $discountAmount = $discountAmount ?? '0.00';
    $discountType = $discountType ?? 'fixed';
    $discountRate = $discountRate ?? null;
    $deliveryCharge = $deliveryCharge ?? '0.00';
    $returnAdjustment = $returnAdjustment ?? '0.00';
    $paidAmount = $paidAmount ?? '0.00';
    $dueAmount = $dueAmount ?? '0.00';
    $previousDue = $previousDue ?? '0.00';

    $hasDiscount = (float) $discountAmount > 0;
    $hasDelivery = (float) $deliveryCharge > 0;
    $hasReturnAdj = (float) $returnAdjustment > 0;
    $netTotal = Money::maxZero(Money::sub(Money::add(Money::sub($subTotal, $discountAmount), $deliveryCharge), $returnAdjustment));
    $totalDueAmount = Money::add($previousDue, $dueAmount);
    $wordsAmount = (float) $totalDueAmount > 0 ? $totalDueAmount : $netTotal;

    $summaryRowCount = 5 + ($hasDiscount ? 2 : 0) + ($hasDelivery ? 1 : 0) + ($hasReturnAdj ? 1 : 0);
    $cleanRate = ($discountRate !== null && (float) $discountRate > 0)
        ? rtrim(rtrim(number_format((float) $discountRate, 2), '0'), '.')
        : null;

    $statusText = $paymentStatus ?? ((float) $dueAmount <= 0 ? 'Fully Paid' : ((float) $paidAmount > 0 ? 'Partial Paid' : 'Unpaid'));
@endphp

<style>
    .marss-purchase-memo-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        min-height: calc(100vh - 138px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .dark .marss-purchase-memo-card,
    [light-mode="dark"] .marss-purchase-memo-card {
        background: #0f172a;
        border-color: #334155;
    }

    .purchase-meta-table {
        width: 100%;
        max-width: 220px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1.5px solid #000000;
        margin-top: 8px;
    }
    .purchase-meta-table td {
        border-right: 1px solid #000000;
        border-bottom: 1px solid #000000;
        padding: 4px 8px;
        font-size: 12px;
        color: #000000;
    }
    .purchase-meta-table tr td:last-child {
        border-right: none;
    }
    .purchase-meta-table tr:last-child td {
        border-bottom: none;
    }
    .dark .purchase-meta-table,
    .dark .purchase-meta-table td,
    [light-mode="dark"] .purchase-meta-table,
    [light-mode="dark"] .purchase-meta-table td {
        border-color: #475569;
        color: #f1f5f9;
    }

    .purchase-memo-table {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid #000000;
    }
    .purchase-memo-table th {
        background: #f1f5f9;
        color: #000000;
        font-weight: 700;
        font-size: 12px;
        border: 1px solid #000000;
        padding: 7px 10px;
        text-align: center;
    }
    .purchase-memo-table td {
        border: 1px solid #000000;
        color: #000000;
        font-size: 12.5px;
        padding: 7px 10px;
        background: #ffffff;
    }
    .purchase-memo-table tbody.memo-summary td {
        border: 1.5px solid #000000;
    }
    .dark .purchase-memo-table,
    .dark .purchase-memo-table th,
    .dark .purchase-memo-table td,
    .dark .purchase-memo-table tbody.memo-summary td,
    [light-mode="dark"] .purchase-memo-table,
    [light-mode="dark"] .purchase-memo-table th,
    [light-mode="dark"] .purchase-memo-table td,
    [light-mode="dark"] .purchase-memo-table tbody.memo-summary td {
        border-color: #475569;
        color: #f1f5f9;
    }
    .dark .purchase-memo-table th,
    [light-mode="dark"] .purchase-memo-table th {
        background: #1e293b;
    }
    .dark .purchase-memo-table td,
    [light-mode="dark"] .purchase-memo-table td {
        background: #0f172a;
    }

    @media print {
        @page {
            size: portrait;
            margin: 0mm;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .marss-purchase-memo-card {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 16px 16px 0 16px !important;
            min-height: calc(100vh - 4px) !important;
            background: #ffffff !important;
            color: #000000 !important;
        }
        .marss-purchase-memo-card * {
            color: #000000 !important;
        }
        .marss-purchase-memo-card .memo-accent-emerald {
            color: #047857 !important;
        }
        .marss-purchase-memo-card .purchase-meta-table,
        .marss-purchase-memo-card .purchase-meta-table td,
        .marss-purchase-memo-card .purchase-memo-table,
        .marss-purchase-memo-card .purchase-memo-table th,
        .marss-purchase-memo-card .purchase-memo-table td,
        .marss-purchase-memo-card .purchase-memo-table tbody.memo-summary td {
            border-color: #000000 !important;
            background: #ffffff !important;
        }
        .marss-purchase-memo-card .purchase-memo-table th {
            background: #f1f5f9 !important;
        }
        .memo-bottom-color-bar {
            width: calc(100% + 32px) !important;
            margin-left: -16px !important;
            margin-right: -16px !important;
            margin-bottom: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .memo-bottom-color-bar .red-bar {
            background-color: #dc2626 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .memo-bottom-color-bar .green-bar {
            background-color: #15803d !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="marss-purchase-memo-card overflow-hidden">
    <div class="flex-1 flex flex-col">
        {{-- 3-Column Equal Header: Left (Billed To), Center (Purchase Details + Logo + Print), Right (Company Info) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start mb-3 print:grid-cols-3">
            {{-- Left Column: Billed To + Meta Table --}}
            <div class="text-left text-black dark:text-slate-100">
                <h4 class="text-sm font-bold uppercase tracking-wider mb-1 text-black dark:text-white">BILLED TO</h4>
                <p class="font-bold text-sm mb-0.5 text-black dark:text-white">{{ $supplierName }}</p>
                @if($supplierAddress && $supplierAddress !== 'N/A')
                    <p class="text-xs mb-0.5 text-slate-800 dark:text-slate-300">{{ $supplierAddress }}</p>
                @else
                    <p class="text-xs mb-0.5 text-slate-800 dark:text-slate-300">N/A</p>
                @endif
                <p class="text-xs mb-2 text-slate-800 dark:text-slate-300">
                    Phone: <span class="font-semibold font-mono text-black dark:text-white">{{ $supplierMobile ?: 'N/A' }}</span>
                </p>

                <table class="purchase-meta-table">
                    <tr>
                        <td class="bg-slate-50 dark:bg-slate-800 font-semibold whitespace-nowrap">Invoice No:</td>
                        <td class="font-bold font-mono text-emerald-700 dark:text-emerald-400 memo-accent-emerald whitespace-nowrap">{{ $docNo }}</td>
                    </tr>
                    <tr>
                        <td class="bg-slate-50 dark:bg-slate-800 font-semibold whitespace-nowrap">Invoice Date:</td>
                        <td class="font-medium font-mono whitespace-nowrap">{{ $date }}</td>
                    </tr>
                </table>
            </div>

            {{-- Center Column: Purchase Details Title, Logo & Print Button --}}
            <div class="flex flex-col items-center justify-center text-center">
                <h2 class="text-lg font-bold text-black dark:text-white tracking-tight mb-2">Purchase Details</h2>
                <img
                    src="{{ asset('backend/assets/img/marss-corporation-icon2.svg') }}"
                    onerror="this.src='{{ asset('backend/assets/img/marss-corporation-icon.svg') }}'"
                    alt="MARSS CORPORATION"
                    class="h-11 max-w-[190px] object-contain mb-2.5"
                >
                <button
                    type="button"
                    onclick="window.print()"
                    class="no-print inline-flex items-center gap-1.5 px-5 py-1.5 rounded-full bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white text-xs font-semibold shadow-sm transition-all cursor-pointer border-0"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Print</span>
                </button>
            </div>

            {{-- Right Column: Company Official Information --}}
            <div class="text-left md:text-right print:text-right text-black dark:text-slate-100">
                <h4 class="text-sm font-bold uppercase tracking-wider mb-0.5 text-black dark:text-white">MARSS CORPORATION</h4>
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 memo-accent-emerald mb-1">{{ $moduleLabel ?? 'Retail & Wholesale Management System' }}</p>
                <p class="text-xs text-slate-800 dark:text-slate-300 mb-0.5">{{ $tagline ?? 'All Kinds of Dry & Gel Battery Supplier' }}</p>
                <p class="text-xs text-slate-800 dark:text-slate-300 mb-0.5">Success Super Market, Sadar Police Fari,</p>
                <p class="text-xs text-slate-800 dark:text-slate-300 mb-1">Ataikula Road, Pabna</p>
                <p class="text-xs text-slate-800 dark:text-slate-300 mb-0.5">
                    Mobile: <span class="font-semibold font-mono text-black dark:text-white">01975-703216, 01715-842083</span>
                </p>
                <p class="text-xs text-slate-800 dark:text-slate-300">
                    Email: <span class="font-medium text-black dark:text-white">marsscorporation2018@gmail.com</span>
                </p>
            </div>
        </div>

        {{-- Items & Summary Table --}}
        <div class="w-full overflow-x-auto">
            <table class="purchase-memo-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">SL. No.</th>
                        <th style="text-align: left;">Product</th>
                        <th style="width: 12%;">Quantity</th>
                        <th style="width: 16%; text-align: right;">Rate</th>
                        <th style="width: 18%; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $idx => $l)
                        <tr>
                            <td class="text-center font-medium">{{ $idx + 1 }}</td>
                            <td class="text-left font-medium">
                                {{ $l['name'] }}
                                @if(($l['return_qty'] ?? 0) > 0)
                                    <span class="text-rose-600 font-bold text-[11px] ml-1">(Returned: {{ $l['return_qty'] }})</span>
                                @endif
                            </td>
                            <td class="text-center font-medium">{{ $l['qty'] }}</td>
                            <td class="text-right font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($l['rate']) }}</td>
                            <td class="text-right font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($l['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-4">No product lines found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tbody class="memo-summary break-inside-avoid">
                    <tr>
                        <td colspan="2" rowspan="{{ $summaryRowCount }}" class="text-center align-middle font-extrabold text-sm sm:text-base">
                            <span class="{{ $statusText === 'Fully Paid' ? 'text-black dark:text-emerald-400' : ($statusText === 'Partial Paid' ? 'text-amber-700 dark:text-amber-400' : 'text-rose-700 dark:text-rose-400') }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td colspan="2" class="text-right font-semibold">Sub Total:</td>
                        <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($subTotal) }}</td>
                    </tr>
                    @if($hasDiscount)
                        <tr>
                            <td colspan="2" class="text-right font-semibold">
                                {{ ($discountType === 'percent' && $cleanRate) ? "Discount ({$cleanRate}%):" : 'Discount:' }}
                            </td>
                            <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">-{{ Money::formatBd($discountAmount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right font-semibold">Net Total:</td>
                            <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($netTotal) }}</td>
                        </tr>
                    @endif
                    @if($hasDelivery)
                        <tr>
                            <td colspan="2" class="text-right font-semibold">Delivery &amp; Transport Charge:</td>
                            <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($deliveryCharge) }}</td>
                        </tr>
                    @endif
                    @if($hasReturnAdj)
                        <tr>
                            <td colspan="2" class="text-right font-semibold">Return Adjustment:</td>
                            <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">-{{ Money::formatBd($returnAdjustment) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="2" class="text-right font-semibold bg-slate-50/80 dark:bg-slate-800/70">Paid Amount:</td>
                        <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap bg-slate-50/80 dark:bg-slate-800/70">{{ Money::formatBd($paidAmount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right font-semibold">Due Amount:</td>
                        <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($dueAmount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right font-semibold">Previous Due Amount:</td>
                        <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($previousDue) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right font-semibold">Total Due Amount:</td>
                        <td class="text-right font-bold font-mono tabular-nums whitespace-nowrap">{{ Money::formatBd($totalDueAmount) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bill Footer Section: Taka in Words, Signatures, Powered By, Red/Green Bottom Bar --}}
    <div class="mt-auto pt-8 w-full break-inside-avoid">
        <div class="w-full mb-10 flex items-end gap-2 text-xs text-black dark:text-slate-200">
            <span class="font-bold whitespace-nowrap">Taka in Words:</span>
            <span class="flex-1 border-b border-dotted border-black dark:border-slate-500 font-bold pl-1.5">{{ Money::toWords($wordsAmount) }}</span>
        </div>

        <div class="flex justify-between items-end mt-8 pb-4 text-xs font-semibold text-black dark:text-slate-200">
            <div class="w-[35%] text-center border-t border-dotted border-black dark:border-slate-500 pt-1">Received by</div>
            <div class="w-[35%] text-center border-t border-dotted border-black dark:border-slate-500 pt-1">Authorized Signature</div>
        </div>

        <div class="text-center text-[11px] text-slate-500 dark:text-slate-400 my-2">
            Powered by: <a href="https://codenextit.com" target="_blank" class="hover:underline">CodeNext IT</a> - <a href="https://codenextit.com" target="_blank" class="hover:underline">www.codenextit.com</a>
        </div>

        <div class="memo-bottom-color-bar flex h-3 -mx-6 -mb-6 mt-2">
            <div class="red-bar w-1/2 bg-[#dc2626]"></div>
            <div class="green-bar w-1/2 bg-[#15803d]"></div>
        </div>
    </div>
</div>
@endsection
