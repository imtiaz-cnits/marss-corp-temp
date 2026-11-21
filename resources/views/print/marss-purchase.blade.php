@extends('layouts.print')
@section('title', 'Purchase Invoice ' . $docNo)

@section('content')
@php
    use App\Support\Money;
    $netPayable = Money::maxZero(Money::sub(Money::add(Money::sub($subTotal, $discountAmount), $deliveryCharge), $returnAdjustment));
@endphp

<div class="max-w-[8in] mx-auto mb-4 flex items-center justify-between gap-2 no-print">
    <a href="{{ $backUrl }}" class="h-10 px-4 rounded-xl bg-slate-800 text-white text-sm font-bold inline-flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <button type="button" onclick="window.print()" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold inline-flex items-center gap-2 cursor-pointer"><i class="fa-solid fa-print"></i> Print Purchase</button>
</div>

<div class="invoice-sheet bg-white border border-slate-300 shadow-xl w-full max-w-[8in] min-h-[10.5in] mx-auto p-4 flex flex-col justify-between text-black">
    <div>
        <div class="flex items-start justify-between gap-3 border-b-2 border-[#15803d] pb-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('backend/assets/icons/marss-corporation-icon.svg') }}" alt="MARSS" class="w-12 h-12 object-contain">
                <div>
                    <h1 class="text-2xl font-black text-[#15803d] leading-none"><span class="text-[#dc2626]">MARSS</span> CORPORATION</h1>
                    <p class="text-xs font-bold text-slate-600 mt-1">{{ $moduleLabel }} Voucher</p>
                </div>
            </div>
            <div class="text-right text-xs">
                <div class="font-extrabold text-sm">Purchase No: {{ $docNo }}</div>
                <div>Date: <strong>{{ $date }}</strong></div>
                @if($refNo)<div>Ref / Challan: {{ $refNo }}</div>@endif
            </div>
        </div>

        <div class="my-3 p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs">
            <div class="font-extrabold text-sm">{{ $supplierName }} @if($supplierCompany) ({{ $supplierCompany }}) @endif</div>
            <div>Mobile: {{ $supplierMobile ?: '—' }} | Address: {{ $supplierAddress ?: '—' }}</div>
        </div>

        <table class="w-full border-collapse border border-black text-xs">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-black px-2 py-1.5 w-[7%]">#</th>
                    <th class="border border-black px-2 py-1.5 text-left">Product</th>
                    <th class="border border-black px-2 py-1.5 text-right w-[15%]">Unit Cost</th>
                    <th class="border border-black px-2 py-1.5 text-center w-[10%]">Qty</th>
                    <th class="border border-black px-2 py-1.5 text-right w-[18%]">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $idx => $l)
                    <tr>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5 font-medium">{{ $l['name'] }} @if($l['return_qty'] > 0)<span class="text-red-700 font-bold">(Ret: {{ $l['return_qty'] }})</span>@endif</td>
                        <td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($l['rate'], false) }}</td>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $l['qty'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-right font-semibold tabular-nums">{{ Money::formatBd($l['total'], false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-between items-start mt-3 gap-4">
            <div class="flex-1 text-xs pt-1">
                <p class="font-bold">Net Payable in Words:</p>
                <p class="italic">{{ Money::toWords($netPayable) }}</p>
            </div>
            <table class="w-64 border-collapse border border-black text-xs">
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Grand Subtotal</td><td class="border border-black px-2.5 py-1 text-right font-bold tabular-nums">{{ Money::formatBd($subTotal, false) }}</td></tr>
                @if((float) $discountAmount > 0)<tr><td class="border border-black px-2.5 py-1">Discount</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">-{{ Money::formatBd($discountAmount, false) }}</td></tr>@endif
                @if((float) $deliveryCharge > 0)<tr><td class="border border-black px-2.5 py-1">Delivery Charge</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">{{ Money::formatBd($deliveryCharge, false) }}</td></tr>@endif
                @if((float) $returnAdjustment > 0)<tr class="text-red-700"><td class="border border-black px-2.5 py-1">Return Adjustment</td><td class="border border-black px-2.5 py-1 text-right tabular-nums">-{{ Money::formatBd($returnAdjustment, false) }}</td></tr>@endif
                <tr><td class="border border-black px-2.5 py-1 font-semibold">Paid Amount</td><td class="border border-black px-2.5 py-1 text-right font-bold text-[#15803d] tabular-nums">{{ Money::formatBd($paidAmount, false) }}</td></tr>
                <tr class="bg-slate-100 font-extrabold"><td class="border border-black px-2.5 py-1.5">Due Payable</td><td class="border border-black px-2.5 py-1.5 text-right tabular-nums">{{ Money::formatBd($dueAmount, false) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="print-footer-bottom mt-auto pt-12">
        <div class="flex justify-between text-xs font-bold mb-4">
            <div class="w-40 text-center border-t border-black pt-1">Supplier Signature</div>
            <div class="w-44 text-center border-t border-black pt-1">Authorized Signatory</div>
        </div>
        <div class="h-2 w-full flex"><div class="w-1/2 bg-[#dc2626]"></div><div class="w-1/2 bg-[#15803d]"></div></div>
    </div>
</div>
@endsection
