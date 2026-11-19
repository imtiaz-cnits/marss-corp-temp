@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Expenses</h1>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-rose-50 text-sm font-extrabold text-rose-700">Total: {{ Money::formatBd($totalFiltered) }}</span>
            <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Expense</button>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search details..." class="h-[38px] px-3 w-52 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            </div>
            <x-ui.export-buttons table-id="batExpTable" filename="battery-expenses" title="MARSS Battery Expenses" />
        </div>
        <table id="batExpTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Details</th><th class="px-3 py-3 text-right">Amount</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($expenses as $e)
                    <tr>
                        <td class="px-3 py-2.5">{{ $e->date }}</td>
                        <td class="px-3 py-2.5 font-bold">{{ $e->expenseType?->type_name ?? '—' }}</td>
                        <td class="px-3 py-2.5">{{ $e->expense_details ?: '—' }}</td>
                        <td class="px-3 py-2.5 text-right font-extrabold text-rose-600">{{ Money::formatBd($e->expense_amount) }}</td>
                        <td class="px-3 py-2.5 text-right">
                            <button type="button" wire:click="openEdit({{ $e->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                            <button type="button" wire:click="delete({{ $e->id }})" wire:confirm="Delete?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row="true"><td colspan="5" class="px-4 py-8 text-center text-slate-500">No Battery expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $expenses->links() }}</div>
    </div>

    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Battery Expense' : 'Add Battery Expense'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Type *</label><select wire:model="expense_type_id" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($types as $t)<option value="{{ $t->id }}">{{ $t->type_name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Date *</label><x-ui.date-picker wire:model="date" /></div>
                <div><label class="block text-xs font-bold mb-1">Amount *</label><input type="number" step="0.01" wire:model="expense_amount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Staff</label><select wire:model="staff_id" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">None</option>@foreach($staffMembers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                <div class="col-span-2"><label class="block text-xs font-bold mb-1">Details</label><input type="text" wire:model="expense_details" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>
</div>
