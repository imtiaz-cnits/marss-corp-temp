@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Investments</h1>
            <p class="text-xs sm:text-sm text-slate-500">Track capital inflows from registered Retail investors.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm font-extrabold text-emerald-700">Total: {{ Money::formatBd($totalInvested) }}</span>
            <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Record Investment
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Investor</th><th class="px-4 py-3">Details</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($invests as $i)
                        <tr>
                            <td class="px-4 py-3 text-slate-500">{{ $i->date }}</td>
                            <td class="px-4 py-3 font-bold">{{ $i->investor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $i->invest_details ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-emerald-700 tabular-nums">{{ Money::formatBd($i->invest_amount) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:click="openEdit({{ $i->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="delete({{ $i->id }})" wire:confirm="Delete this investment?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No investments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $invests->links() }}</div>
    </div>

    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Investment' : 'Record Investment'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div>
                <label class="block text-xs font-bold mb-1">Investor *</label>
                <select wire:model="investor_info_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="">Select Investor</option>
                    @foreach($investors as $inv)<option value="{{ $inv->id }}">{{ $inv->name }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Date *</label><x-ui.date-picker wire:model="date" /></div>
                <div><label class="block text-xs font-bold mb-1">Amount (৳) *</label><input type="number" step="0.01" min="0.01" wire:model="invest_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
            </div>
            <div><label class="block text-xs font-bold mb-1">Details</label><input type="text" wire:model="invest_details" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save</button>
            </div>
        </form>
    </x-ui.modal>
</div>
