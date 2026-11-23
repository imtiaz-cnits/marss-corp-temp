@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Purchase Payment Ledger</h1>
        <div class="flex items-center gap-2">
            <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Purchase #</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Method</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Discount</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($payments as $p)
                    <tr><td class="px-3 py-2.5">{{ $p->purchase_due_collection_date }}</td><td class="px-3 py-2.5 font-bold">{{ $p->purchase?->purchase_id }}</td><td class="px-3 py-2.5">{{ $p->purchase?->supplier?->name ?? '—' }}</td><td class="px-3 py-2.5">{{ $p->payment_method ?: 'Cash' }}</td><td class="px-3 py-2.5 text-right font-bold text-emerald-700">{{ Money::formatBd($p->paid_amount) }}</td><td class="px-3 py-2.5 text-right">{{ Money::formatBd($p->discount_amount) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No Battery purchase payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $payments->firstItem() ?? 0 }} – {{ $payments->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $payments->total() }}</span>
                    <span>payments</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $payments->links() }}
                </div>
            </div>
        
    </div>
</div>
