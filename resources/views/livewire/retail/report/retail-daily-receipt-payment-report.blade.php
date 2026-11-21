@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Daily Receipt &amp; Payment Statement</h1>
            <p class="text-xs sm:text-sm text-slate-500">Exact cash reconciliation statement guaranteed to match Retail Daily Ledger Closing Balance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="retailReceiptStmtTable" filename="retail-daily-receipt-payment" title="MARSS Retail Daily Receipt & Payment" />
        </div>
    </div>

    <div class="print-sheet-page bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 sm:p-6 space-y-5">
        <div class="text-center border-b-2 border-emerald-700 pb-3">
            <h2 class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400">MARSS CORPORATION — RETAIL SHOP</h2>
            <p class="text-xs font-semibold text-slate-500">Daily Receipt &amp; Payment Statement ({{ $startDate }} to {{ $endDate }})</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Receipts Side --}}
            <div class="border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-[#15803d] text-white font-bold text-sm">RECEIPTS (Cash Inflow)</div>
                <table id="retailReceiptStmtTable" class="w-full text-xs sm:text-sm">
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <tr class="bg-emerald-50/60 dark:bg-emerald-950/20 font-bold">
                            <td class="px-4 py-2.5">Opening Cash Balance</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ Money::formatBd($stmt['opening_balance']) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Sales &amp; Invoice Collections</td>
                            <td class="px-4 py-2.5 text-right font-semibold tabular-nums">{{ Money::formatBd($stmt['sales_collection']) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Previous Due Collections</td>
                            <td class="px-4 py-2.5 text-right font-semibold tabular-nums">{{ Money::formatBd($stmt['previous_due_collection']) }}</td>
                        </tr>
                        <tr class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                            <td class="px-4 py-3">Total Available Cash</td>
                            <td class="px-4 py-3 text-right text-emerald-700 tabular-nums">{{ Money::formatBd(Money::add($stmt['opening_balance'], $stmt['total_receipts'])) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Payments Side --}}
            <div class="border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-rose-700 text-white font-bold text-sm">PAYMENTS (Cash Outflow)</div>
                <table id="retailPaymentStmtTable" class="w-full text-xs sm:text-sm">
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($stmt['supplier_payments'] as $suppName => $amt)
                            <tr>
                                <td class="px-4 py-2">Supplier Paid: <strong>{{ $suppName }}</strong></td>
                                <td class="px-4 py-2 text-right font-semibold tabular-nums">{{ Money::formatBd($amt) }}</td>
                            </tr>
                        @endforeach
                        @foreach($stmt['expenses'] as $expType => $amt)
                            <tr>
                                <td class="px-4 py-2">Expense: <strong>{{ $expType }}</strong></td>
                                <td class="px-4 py-2 text-right font-semibold tabular-nums">{{ Money::formatBd($amt) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50 dark:bg-slate-800/70 font-bold">
                            <td class="px-4 py-2.5">Total Payments</td>
                            <td class="px-4 py-2.5 text-right text-rose-600 tabular-nums">{{ Money::formatBd($stmt['total_payments']) }}</td>
                        </tr>
                        <tr class="bg-emerald-700 text-white font-extrabold">
                            <td class="px-4 py-3">Closing Cash Balance</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Money::formatBd($stmt['closing_balance']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="print-footer-bottom mt-auto grid grid-cols-3 gap-6 pt-10 text-center text-xs font-bold text-slate-600 dark:text-slate-300">
            <div class="border-t border-slate-400 pt-2">Cashier</div>
            <div class="border-t border-slate-400 pt-2">Accountant</div>
            <div class="border-t border-slate-400 pt-2">Authorized Signatory</div>
        </div>
    </div>
</div>
