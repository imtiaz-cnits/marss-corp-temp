@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Supplier Due Collections</h1>
        <div class="flex items-center gap-2">
            <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Method</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Discount</th><th class="px-3 py-3 text-right">Remaining</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($rows as $r)
                    <tr><td class="px-3 py-2.5">{{ $r->payment_date }}</td><td class="px-3 py-2.5 font-bold">{{ $r->supplier?->name ?? '—' }}</td><td class="px-3 py-2.5">{{ $r->payment_method ?: 'Cash' }}</td><td class="px-3 py-2.5 text-right font-bold text-emerald-700">{{ Money::formatBd($r->paid_amount) }}</td><td class="px-3 py-2.5 text-right">{{ Money::formatBd($r->discount_amount) }}</td><td class="px-3 py-2.5 text-right font-bold">{{ Money::formatBd($r->due_amount) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No Battery supplier due collections recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $rows->links() }}</div>
    </div>
</div>
