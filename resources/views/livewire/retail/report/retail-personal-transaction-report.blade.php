@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Personal &amp; Investor Transaction Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">Personal expense withdrawals alongside Retail capital investments.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-xs font-bold uppercase text-slate-500">Total Personal Expenses</p><p class="text-xl font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($data['total_expense']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-xs font-bold uppercase text-slate-500">Total Retail Investments</p><p class="text-xl font-extrabold text-emerald-700 mt-1 tabular-nums">{{ Money::formatBd($data['total_invest']) }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Personal Expenses</div>
            <table class="w-full text-xs text-left">
                <thead class="bg-[#15803d] text-white uppercase"><tr><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5">Category</th><th class="px-3 py-2.5">Details</th><th class="px-3 py-2.5 text-right">Amount</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($data['expenses'] as $e)
                        <tr><td class="px-3 py-2">{{ $e->date }}</td><td class="px-3 py-2 font-bold">{{ $e->expenseType?->type_name }}</td><td class="px-3 py-2">{{ $e->expense_details }}</td><td class="px-3 py-2 text-right font-bold text-rose-600">{{ Money::formatBd($e->expense_amount) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No personal expenses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Retail Investments</div>
            <table class="w-full text-xs text-left">
                <thead class="bg-[#15803d] text-white uppercase"><tr><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5">Investor</th><th class="px-3 py-2.5">Details</th><th class="px-3 py-2.5 text-right">Amount</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($data['invests'] as $i)
                        <tr><td class="px-3 py-2">{{ $i->date }}</td><td class="px-3 py-2 font-bold">{{ $i->investor?->name }}</td><td class="px-3 py-2">{{ $i->invest_details }}</td><td class="px-3 py-2 text-right font-bold text-emerald-700">{{ Money::formatBd($i->invest_amount) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No investments in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
