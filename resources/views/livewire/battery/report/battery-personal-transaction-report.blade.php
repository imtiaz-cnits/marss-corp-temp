@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Personal Transaction Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">Strictly isolated Battery personal expenses (zero Retail data leakage).</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="batPersonalTable" filename="battery-personal-transactions" title="MARSS Battery Personal Transactions" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4 flex justify-between items-center">
        <span class="text-xs font-bold uppercase text-slate-500">Total Battery Personal Expenses</span>
        <span class="text-xl font-extrabold text-rose-600 tabular-nums">{{ Money::formatBd($data['total_expense']) }}</span>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <table id="batPersonalTable" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Details</th><th class="px-3 py-3 text-right">Amount</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($expenses as $e)
                    <tr><td class="px-3 py-2">{{ $e->date }}</td><td class="px-3 py-2 font-bold">{{ $e->expenseType?->type_name }}</td><td class="px-3 py-2">{{ $e->expense_details }}</td><td class="px-3 py-2 text-right font-bold text-rose-600">{{ Money::formatBd($e->expense_amount) }}</td></tr>
                @empty
                    <tr data-empty-row="true"><td colspan="4" class="px-4 py-8 text-center text-slate-500">No Battery personal expenses in range.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $expenses->links() }}</div>
    </div>
</div>
