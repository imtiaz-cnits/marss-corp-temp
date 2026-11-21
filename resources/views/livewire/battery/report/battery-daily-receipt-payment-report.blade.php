@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Daily Receipt &amp; Payment</h1>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="batteryReceiptStmtTable" filename="battery-daily-receipt-payment" title="MARSS Battery Daily Receipt & Payment" />
        </div>
    </div>

    <div class="print-sheet-page bg-white dark:bg-slate-900 rounded-2xl border p-4 sm:p-6 space-y-5">
        <div class="text-center border-b-2 border-emerald-700 pb-3">
            <h2 class="text-xl font-extrabold text-emerald-700">MARSS CORPORATION — BATTERY DIVISION</h2>
            <p class="text-xs text-slate-500">Daily Receipt &amp; Payment Statement ({{ $startDate }} to {{ $endDate }})</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="border rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-[#15803d] text-white font-bold text-sm">RECEIPTS</div>
                <table id="batteryReceiptStmtTable" class="w-full text-xs sm:text-sm">
                    <tbody class="divide-y">
                        <tr class="font-bold"><td class="px-4 py-2.5">Opening Balance</td><td class="px-4 py-2.5 text-right">{{ Money::formatBd($stmt['opening_balance']) }}</td></tr>
                        <tr><td class="px-4 py-2.5">Battery Sales Collections</td><td class="px-4 py-2.5 text-right">{{ Money::formatBd($stmt['sales_collection']) }}</td></tr>
                        <tr><td class="px-4 py-2.5">Previous Due Collections</td><td class="px-4 py-2.5 text-right">{{ Money::formatBd($stmt['previous_due_collection']) }}</td></tr>
                        <tr class="bg-slate-100 dark:bg-slate-800 font-extrabold"><td class="px-4 py-3">Total Available</td><td class="px-4 py-3 text-right text-emerald-700">{{ Money::formatBd(Money::add($stmt['opening_balance'], $stmt['total_receipts'])) }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="border rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-rose-700 text-white font-bold text-sm">PAYMENTS</div>
                <table id="batteryPaymentStmtTable" class="w-full text-xs sm:text-sm">
                    <tbody class="divide-y">
                        @foreach($stmt['supplier_payments'] as $sn => $amt)
                            <tr><td class="px-4 py-2">Supplier: <strong>{{ $sn }}</strong></td><td class="px-4 py-2 text-right">{{ Money::formatBd($amt) }}</td></tr>
                        @endforeach
                        @foreach($stmt['expenses'] as $et => $amt)
                            <tr><td class="px-4 py-2">Expense: <strong>{{ $et }}</strong></td><td class="px-4 py-2 text-right">{{ Money::formatBd($amt) }}</td></tr>
                        @endforeach
                        <tr class="font-bold"><td class="px-4 py-2.5">Total Payments</td><td class="px-4 py-2.5 text-right text-rose-600">{{ Money::formatBd($stmt['total_payments']) }}</td></tr>
                        <tr class="bg-emerald-700 text-white font-extrabold"><td class="px-4 py-3">Closing Balance</td><td class="px-4 py-3 text-right">{{ Money::formatBd($stmt['closing_balance']) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="print-footer-bottom mt-auto grid grid-cols-3 gap-6 pt-10 text-center text-xs font-bold">
            <div class="border-t border-slate-400 pt-2">Cashier</div><div class="border-t border-slate-400 pt-2">Accountant</div><div class="border-t border-slate-400 pt-2">Authorized Signatory</div>
        </div>
    </div>
</div>
