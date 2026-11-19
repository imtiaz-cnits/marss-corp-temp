@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Supplier Payment History</h1>
        <p class="text-xs sm:text-sm text-slate-500">Audit log of all Retail supplier due settlement disbursements.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search supplier..." class="h-[38px] px-3 w-56 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            </div>
            <x-ui.export-buttons table-id="retailSuppColTable" filename="retail-supplier-payments" title="MARSS Retail Supplier Due Collections" />
        </div>
        <div class="overflow-x-auto">
            <table id="retailSuppColTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Method</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Discount</th><th class="px-3 py-3 text-right">Remaining Due</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($rows as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 text-slate-500">{{ $r->due_collection_date }}</td>
                            <td class="px-3 py-2.5 font-bold">{{ $r->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5">{{ $r->payment_method ?: 'Cash' }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-emerald-700 tabular-nums">{{ Money::formatBd($r->paid_amount) }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($r->discount_amount) }}</td>
                            <td class="px-3 py-2.5 text-right font-bold tabular-nums">{{ Money::formatBd($r->due_amount) }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No supplier payment records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $rows->links() }}</div>
    </div>
</div>
