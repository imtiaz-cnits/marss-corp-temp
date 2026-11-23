@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Due Invoice Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">All Retail invoices carrying unpaid balances.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Invoice #, customer..." class="h-[38px] px-3 w-48 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="retailDueInvTable" filename="retail-due-invoices" title="MARSS Retail Due Invoices" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="retailDueInvTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Invoice #</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3 text-right">SubTotal</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th><th class="px-3 py-3 text-right">Print</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                        <tr>
                            <td class="px-3 py-2 text-slate-500">{{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('d-m-Y') }}</td>
                            <td class="px-3 py-2 font-bold">{{ $o->order_no }}</td>
                            <td class="px-3 py-2">{{ $o->customer?->customer_name ?? '—' }} <span class="text-xs text-slate-400">{{ $o->customer?->mobile }}</span></td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($o->sub_total) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 tabular-nums">{{ Money::formatBd($o->paid_amount) }}</td>
                            <td class="px-3 py-2 text-right font-extrabold text-rose-600 tabular-nums">{{ Money::formatBd($o->due_amount) }}</td>
                            <td class="px-3 py-2 text-right"><a href="{{ route('retail.invoice.due-print', $o->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">Print</a></td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No due invoices found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                    <tr><th colspan="5" class="px-3 py-3 text-right">Total Outstanding Due:</th><th class="px-3 py-3 text-right text-rose-600 tabular-nums">{{ Money::formatBd($totalDue) }}</th><th></th></tr>
                </tfoot>
            </table>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $orders->firstItem() ?? 0 }} – {{ $orders->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $orders->total() }}</span>
                    <span>invoices</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $orders->links() }}
                </div>
            </div>
        
    </div>
</div>
