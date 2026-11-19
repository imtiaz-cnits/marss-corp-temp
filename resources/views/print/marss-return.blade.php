@extends('layouts.print')
@section('title', $title . ' ' . $returnNo)

@section('content')
@php use App\Support\Money; @endphp

<div class="max-w-[8in] mx-auto mb-4 flex items-center justify-between gap-2 no-print">
    <a href="{{ $backUrl }}" class="h-10 px-4 rounded-xl bg-slate-800 text-white text-sm font-bold inline-flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <button type="button" onclick="window.print()" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold inline-flex items-center gap-2 cursor-pointer"><i class="fa-solid fa-print"></i> Print Voucher</button>
</div>

<div class="invoice-sheet bg-white border border-slate-300 shadow-xl w-full max-w-[8in] min-h-[9in] mx-auto p-6 flex flex-col justify-between text-black">
    <div>
        <div class="flex items-start justify-between border-b-2 border-[#15803d] pb-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('backend/assets/icons/marss-corporation-icon.svg') }}" alt="MARSS" class="w-12 h-12 object-contain">
                <div>
                    <h1 class="text-2xl font-black text-[#15803d] leading-none"><span class="text-[#dc2626]">MARSS</span> CORPORATION</h1>
                    <p class="text-xs font-bold text-slate-600 mt-1">{{ $title }}</p>
                </div>
            </div>
            <div class="text-right text-xs">
                <div class="font-extrabold text-sm">Return #: {{ $returnNo }}</div>
                <div>Source Ref: <strong>{{ $refNo }}</strong></div>
                <div>Date: {{ $date }}</div>
            </div>
        </div>

        <div class="my-3 text-xs">
            <strong>{{ $partyLabel }}:</strong> {{ $partyName }} {{ $partyMobile ? "({$partyMobile})" : '' }}
        </div>

        <table class="w-full border-collapse border border-black text-xs">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-black px-2 py-1.5 w-[7%]">#</th>
                    <th class="border border-black px-2 py-1.5 text-left">Returned Item</th>
                    <th class="border border-black px-2 py-1.5 text-center w-[12%]">Qty</th>
                    <th class="border border-black px-2 py-1.5 text-right w-[16%]">Unit Rate</th>
                    <th class="border border-black px-2 py-1.5 text-right w-[18%]">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $idx => $l)
                    <tr>
                        <td class="border border-black px-2 py-1.5 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5 font-medium">{{ $l['name'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-center font-bold">{{ $l['qty'] }}</td>
                        <td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($l['rate'], false) }}</td>
                        <td class="border border-black px-2 py-1.5 text-right font-semibold tabular-nums">{{ Money::formatBd($l['amount'], false) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-100 font-extrabold">
                <tr><td colspan="4" class="border border-black px-2 py-1.5 text-right">Total Return Value:</td><td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($totalAmount) }}</td></tr>
                <tr><td colspan="4" class="border border-black px-2 py-1.5 text-right">Due Adjusted:</td><td class="border border-black px-2 py-1.5 text-right tabular-nums">{{ Money::formatBd($totalDueReduced) }}</td></tr>
            </tfoot>
        </table>

        <p class="mt-3 text-xs"><strong>In Words:</strong> <em>{{ Money::toWords($totalAmount) }}</em></p>
    </div>

    <div class="pt-12">
        <div class="flex justify-between text-xs font-bold mb-4">
            <div class="w-40 text-center border-t border-black pt-1">{{ $partyLabel }} Signature</div>
            <div class="w-44 text-center border-t border-black pt-1">For MARSS CORPORATION</div>
        </div>
        <div class="h-2 w-full flex"><div class="w-1/2 bg-[#dc2626]"></div><div class="w-1/2 bg-[#15803d]"></div></div>
    </div>
</div>
@endsection
