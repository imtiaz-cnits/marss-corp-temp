@extends('layouts.print')
@section('title', 'Bill Invoice ' . $docNo)

@section('content')
@php
    use App\Support\Money;
    $billAfterDisc = Money::maxZero(Money::sub($subTotal, $discountAmount));
    $netBill = Money::maxZero(Money::sub($billAfterDisc, $returnedAmount));
    $totalDue = Money::add($previousDue, $dueAmount);
    $cleanRate = ($discountRate !== null && (float) $discountRate > 0) ? rtrim(rtrim(number_format((float) $discountRate, 2), '0'), '.') : null;
@endphp

<div class="max-w-[8in] mx-auto mb-4 flex items-center justify-between gap-2 no-print">
    <a href="{{ $backUrl }}" class="h-10 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold inline-flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <button type="button" onclick="window.print()" class="h-10 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold inline-flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-print"></i> Print Bill
    </button>
</div>

<div class="invoice-sheet bg-white border border-slate-300 shadow-xl w-full max-w-[8.27in] mx-auto p-4 flex flex-col justify-between text-black" style="min-height: 270mm;">
    <div class="invoice-content">
        {{-- MARSS Corporation Brand Header --}}
        <div class="flex items-start justify-between gap-3 pb-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('backend/assets/icons/marss-corporation-icon.svg') }}" alt="MARSS" class="w-[52px] h-[52px] object-contain">
                <div>
                    <h1 class="text-2xl font-black tracking-wide leading-none text-[#15803d]">
                        <span class="text-[#dc2626]">MARSS</span> CORPORATION
                    </h1>
                    <p class="text-[11px] font-bold text-[#166534] border-t-[1.5px] border-[#15803d] pt-0.5 mt-1">{!! $tagline !!}</p>
                </div>
            </div>

            <div class="text-center">
                <span class="inline-block bg-[#15803d] text-white text-lg font-extrabold px-5 py-1 rounded-md tracking-wider">Bill</span>
                <p class="text-[10px] font-bold text-slate-500 mt-0.5 uppercase">{{ $moduleLabel }}</p>
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
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Bill No:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $docNo }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Challan No:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-semibold">{{ $challanNo }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Name of Customer:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $partyName }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Address:</span><span class="flex-1 border-b border-dotted border-black px-1.5">{{ $partyAddress }}</span></div>
            </div>
            <div class="col-span-5 space-y-1.5">
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Bill Date:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-bold">{{ $date }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Delivery Date:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-semibold">{{ $date }}</span></div>
                <div class="flex items-end"><span class="font-semibold mr-1.5 whitespace-nowrap">Mob:</span><span class="flex-1 border-b border-dotted border-black px-1.5 font-semibold">{{ $partyMobile }}</span></div>
            </div>
        </div>

        {{-- 5-Column Bill Table --}}
        <table class="w-full border-collapse border border-black text-xs mt-2">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-black px-2 py-1.5 w-[7%] text-center">Sl.No.</th>
                    <th class="border border-black px-2 py-1.5 text-left">Description</th>
                    <th class="border border-black px-2 py-1.5 w-[15%] text-right">Rate</th>
                    <th class="border border-black px-2 py-1.5 w-[10%] text-center">Qty.</th>
                    <th class="border border-black px-2 py-1.5 w-[18%] text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $idx => $line)
                    <tr>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5 font-medium">
                            {{ $line['name'] }}
                            @if(!empty($line['door_side']))
                                <span class="text-[10px] text-slate-600">({{ $line['door_side'] }})</span>
                            @endif
                            @if($line['return_qty'] >= $line['qty'] && $line['qty'] > 0)
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold text-red-700 bg-red-50 border border-red-200 rounded">Returned</span>
                            @elseif($line['return_qty'] > 0)
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold text-orange-700 bg-orange-50 border border-orange-200 rounded">Returned: {{ $line['return_qty'] }} pcs</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($line['rate'], false) }}</td>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $line['qty'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-right font-semibold tabular-nums">{{ Money::formatBd($line['total'], false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals Summary --}}
        <div class="flex justify-between items-start gap-4 mt-3">
            <div class="flex-1 text-xs pt-1">
                <p class="font-bold">In Words:</p>
                <p class="italic text-slate-800 border-b border-dotted border-black pb-1">{{ Money::toWords($netBill) }}</p>
            </div>
            <table class="w-64 border-collapse border border-black text-xs">
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Total Bill</td><td class="border border-black px-2.5 py-1 text-right font-bold tabular-nums">{{ Money::formatBd($subTotal, false) }}</td></tr>
                @if((float) $discountAmount > 0)
                    <tr><td class="border border-black px-2.5 py-1 font-semibold">{{ ($discountType === 'percent' && $cleanRate) ? "Discount ({$cleanRate}%)" : 'Discount' }}</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">-{{ Money::formatBd($discountAmount, false) }}</td></tr>
                @endif
                @if((float) $returnedAmount > 0)
                    <tr class="text-red-700 font-semibold"><td class="border border-black px-2.5 py-1">Returned Amount</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">-{{ Money::formatBd($returnedAmount, false) }}</td></tr>
                    <tr><td class="border border-black px-2.5 py-1 font-semibold">Net Bill</td><td class="border border-black px-2.5 py-1 text-right font-bold tabular-nums">{{ Money::formatBd($netBill, false) }}</td></tr>
                @else
                    <tr><td class="border border-black px-2.5 py-1 font-semibold">Net Amount</td><td class="border border-black px-2.5 py-1 text-right font-bold tabular-nums">{{ Money::formatBd($billAfterDisc, false) }}</td></tr>
                @endif
                @if((float) $deliveryCharge > 0)
                    <tr><td class="border border-black px-2.5 py-1 font-semibold">Delivery Charge</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">{{ Money::formatBd($deliveryCharge, false) }}</td></tr>
                @endif
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Paid Amount</td><td class="border border-black px-2.5 py-1 text-right font-bold text-[#15803d] tabular-nums">{{ Money::formatBd($paidAmount, false) }}</td></tr>
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Today Due</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">{{ Money::formatBd($dueAmount, false) }}</td></tr>
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Previous Due</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">{{ Money::formatBd($previousDue, false) }}</td></tr>
                <tr class="bg-slate-100 font-extrabold"><td class="border border-black px-2.5 py-1.5">Total Due</td><td class="border border-black px-2.5 py-1.5 text-right tabular-nums">{{ Money::formatBd($totalDue, false) }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Bottom Signatures, CodeNext IT Footer & 50/50 Red-Green Color Bar --}}
    <div class="print-footer-bottom mt-auto pt-12">
        <div class="flex justify-between items-end text-xs font-bold mb-4">
            <div class="w-40 text-center border-t border-black pt-1">Customer Signature</div>
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
