@extends('layouts.print')
@section('title', $title . ' ' . $returnNo)

@section('content')
@php use App\Support\Money; @endphp

<div class="max-w-[8.27in] mx-auto mb-4 flex items-center justify-between gap-2 no-print">
    <a href="{{ $backUrl }}" class="h-10 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold inline-flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <button type="button" onclick="window.print()" class="h-10 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold inline-flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-print"></i> Print Voucher
    </button>
</div>

<div class="invoice-sheet bg-white border border-slate-300 shadow-xl w-full max-w-[8.27in] mx-auto p-4 flex flex-col justify-between text-black" style="min-height: 270mm;">
    <div class="invoice-content">
        {{-- MARSS Corporation Brand Header --}}
        <div class="flex items-start justify-between gap-3 pb-3 border-b-2 border-[#15803d]">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('backend/assets/icons/marss-corporation-icon.svg') }}" alt="MARSS" class="w-[52px] h-[52px] object-contain">
                <div>
                    <h1 class="text-2xl font-black tracking-wide leading-none text-[#15803d]">
                        <span class="text-[#dc2626]">MARSS</span> CORPORATION
                    </h1>
                    <p class="text-[11px] font-bold text-[#166534] border-t-[1.5px] border-[#15803d] pt-0.5 mt-1">All kinds of Dry &amp; Gel Battery Supplier</p>
                </div>
            </div>

            <div class="text-center">
                <span class="inline-block bg-[#15803d] text-white text-lg font-extrabold px-5 py-1 rounded-md tracking-wider">Return</span>
                <p class="text-[10px] font-bold text-slate-500 mt-0.5 uppercase">{{ $title }}</p>
            </div>

            <div class="text-right text-[10.5px] leading-snug max-w-[240px]">
                <div><strong>Office:</strong> Success Super Market,</div>
                <div>Sadar Police Fari, Ataikula Road Pabna.</div>
                <div><strong>Mobile:</strong> 01975-703216, 01715-842083</div>
                <div><strong>E-mail:</strong> marsscorporation2018@gmail.com</div>
            </div>
        </div>

        {{-- Dotted Metadata Grid --}}
        <div class="grid grid-cols-12 gap-3 text-xs my-3">
            <div class="col-span-7 space-y-1.5">
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Return No:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $returnNo }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Source Invoice Ref:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-semibold">{{ $refNo }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">{{ $partyLabel }}:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $partyName }}</span></div>
            </div>
            <div class="col-span-5 space-y-1.5">
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Return Date:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $date }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Contact Mob:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-semibold">{{ $partyMobile ?: '-' }}</span></div>
            </div>
        </div>

        {{-- 5-Column Return Table --}}
        <table class="w-full border-collapse border border-black text-xs mt-2">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-black px-2 py-1.5 w-[7%] text-center">Sl.No.</th>
                    <th class="border border-black px-2 py-1.5 text-left">Returned Description / Item</th>
                    <th class="border border-black px-2 py-1.5 w-[15%] text-right">Rate</th>
                    <th class="border border-black px-2 py-1.5 w-[10%] text-center">Qty.</th>
                    <th class="border border-black px-2 py-1.5 w-[18%] text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $idx => $l)
                    <tr>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5 font-medium">{{ $l['name'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($l['rate'], false) }}</td>
                        <td class="border border-black px-2 py-1.5 text-center font-bold">{{ $l['qty'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-right font-semibold tabular-nums">{{ Money::formatBd($l['amount'], false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals Summary --}}
        <div class="flex justify-between items-start gap-4 mt-3">
            <div class="flex-1 text-xs pt-1">
                <p class="font-bold">In Words:</p>
                <p class="italic text-slate-800 border-b border-dotted border-black pb-1">{{ Money::toWords($totalAmount) }}</p>
            </div>
            <table class="w-64 border-collapse border border-black text-xs">
                <tr>
                    <td class="border border-black px-2.5 py-1 font-semibold">Total Return Value</td>
                    <td class="border border-black px-2.5 py-1 text-right font-bold text-rose-600 tabular-nums">{{ Money::formatBd($totalAmount, false) }}</td>
                </tr>
                <tr>
                    <td class="border border-black px-2.5 py-1 font-semibold">Due Adjusted</td>
                    <td class="border border-black px-2.5 py-1 text-right font-bold text-emerald-700 tabular-nums">{{ Money::formatBd($totalDueReduced, false) }}</td>
                </tr>
                <tr class="bg-slate-100 font-extrabold">
                    <td class="border border-black px-2.5 py-1.5">Net Refund / Paid</td>
                    <td class="border border-black px-2.5 py-1.5 text-right tabular-nums">{{ Money::formatBd(Money::sub($totalAmount, $totalDueReduced), false) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Bottom Signatures, CodeNext IT Footer & 50/50 Red-Green Color Bar --}}
    <div class="print-footer-bottom mt-auto pt-10">
        <div class="flex justify-between items-end text-xs font-bold mb-4">
            <div class="w-40 text-center border-t border-black pt-1">{{ $partyLabel }} Signature</div>
            <div class="w-44 text-center border-t border-black pt-1">For MARSS CORPORATION</div>
        </div>
        <p class="text-center text-[10px] text-slate-500 mb-2">
            &copy; {{ date('Y') }} MARSS CORPORATION | Software By: CodeNext IT
        </p>
        <div class="h-2 w-full flex overflow-hidden rounded-xs">
            <div class="w-1/2 bg-[#dc2626]"></div>
            <div class="w-1/2 bg-[#15803d]"></div>
        </div>
    </div>
</div>
@endsection
