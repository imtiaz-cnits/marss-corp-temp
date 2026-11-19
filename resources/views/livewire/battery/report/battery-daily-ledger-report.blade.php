@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Daily Income &amp; Expense Ledger</h1>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="batLedgerTable" filename="battery-daily-ledger" title="MARSS Battery Daily Ledger" />
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-xs font-bold uppercase text-slate-500">Opening</p><p class="text-lg font-extrabold">{{ Money::formatBd($ledger['opening_balance']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-xs font-bold uppercase text-slate-500">Inflow</p><p class="text-lg font-extrabold text-emerald-700">{{ Money::formatBd($ledger['total_inflow']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-xs font-bold uppercase text-slate-500">Outflow</p><p class="text-lg font-extrabold text-rose-600">{{ Money::formatBd($ledger['total_outflow']) }}</p></div>
        <div class="bg-emerald-700 text-white rounded-2xl p-4"><p class="text-xs font-bold uppercase text-emerald-100">Closing</p><p class="text-lg font-extrabold">{{ Money::formatBd($ledger['closing_balance']) }}</p></div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <table id="batLedgerTable" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Ref</th><th class="px-3 py-3">Party</th><th class="px-3 py-3 text-right">Inflow</th><th class="px-3 py-3 text-right">Outflow</th><th class="px-3 py-3 text-right">Balance</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr class="bg-emerald-50/60 dark:bg-emerald-950/20 font-bold"><td class="px-3 py-2">{{ $startDate }}</td><td class="px-3 py-2" colspan="5">Opening Balance</td><td class="px-3 py-2 text-right">{{ Money::formatBd($ledger['opening_balance']) }}</td></tr>
                @forelse($ledger['entries'] as $r)
                    <tr><td class="px-3 py-2">{{ $r['date'] }}</td><td class="px-3 py-2 font-semibold">{{ $r['type'] }}</td><td class="px-3 py-2 font-mono text-xs">{{ $r['reference'] }}</td><td class="px-3 py-2">{{ $r['party'] }}</td><td class="px-3 py-2 text-right text-emerald-700 font-bold">{{ (float) $r['inflow'] > 0 ? Money::formatBd($r['inflow']) : '—' }}</td><td class="px-3 py-2 text-right text-rose-600 font-bold">{{ (float) $r['outflow'] > 0 ? Money::formatBd($r['outflow']) : '—' }}</td><td class="px-3 py-2 text-right font-extrabold">{{ Money::formatBd($r['running_balance']) }}</td></tr>
                @empty
                    <tr data-empty-row="true"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Battery ledger transactions in range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
