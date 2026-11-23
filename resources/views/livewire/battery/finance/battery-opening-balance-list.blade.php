@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Opening Balance</h1>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Opening Balance</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto hidden lg:block">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Note</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($rows as $r)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ \Carbon\Carbon::parse($r->date)->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-emerald-700 tabular-nums">{{ Money::formatBd($r->amount) }}</td>
                            <td class="px-4 py-3">{{ $r->note ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:click="openEdit({{ $r->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="confirmDelete({{ $r->id }})" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No Battery opening balances recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile & Tablet Cards View (1 col on mobile, 2 cols on tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($rows as $idx => $r)
                <div class="unified-ui-border rounded-2xl p-3 sm:p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs transition-all flex flex-col justify-between border border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">#{{ ($rows->firstItem() ?? 1) + $idx }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-800">
                                <i class="fa-regular fa-calendar-check text-[10px]"></i>
                                {{ \Carbon\Carbon::parse($r->date)->format('d-m-Y') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl my-2 items-center border border-slate-100 dark:border-slate-800">
                            <div class="border-r border-slate-300 dark:border-slate-800 text-start px-2">
                                <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Amount</span>
                                <span class="font-extrabold text-rose-600 dark:text-rose-400 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($r->amount) }}</span>
                            </div>
                            <div class="text-start px-2">
                                <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Note</span>
                                <span class="text-xs text-slate-700 dark:text-slate-300 truncate block">{{ $r->note ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="openEdit({{ $r->id }})" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $r->id }})" class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 dark:bg-rose-950/40 dark:border-slate-800 dark:text-rose-400 flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Delete">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-500">
                    No Battery opening balances recorded.
                </div>
            @endforelse
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $rows->firstItem() ?? 0 }} – {{ $rows->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $rows->total() }}</span>
                    <span>entries</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $rows->links() }}
                </div>
            </div>
        
    </div>
    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Opening Balance' : 'Add Opening Balance'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Date *</label><x-ui.date-picker wire:model="date" /></div>
            <div><label class="block text-xs font-bold mb-1">Amount (৳) *</label><input type="number" step="0.01" wire:model="amount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
            <div><label class="block text-xs font-bold mb-1">Note</label><input type="text" wire:model="note" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>

    {{-- CUSTOM DELETE CONFIRMATION MODAL (1:1 Reference) --}}
    @if($showDeleteModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 text-center animate-in fade-in zoom-in-95 duration-150">
                <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/50">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Are you sure?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    Do you really want to delete <span class="font-bold text-slate-800 dark:text-slate-200">{{ $deletingLabel }}</span>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="h-10 px-5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-50 cursor-pointer">Cancel</button>
                    <button type="button" wire:click="executeDelete" class="h-10 px-5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-xs border-0 cursor-pointer">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
