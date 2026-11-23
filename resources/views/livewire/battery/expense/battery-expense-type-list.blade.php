<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Expense Type List</h1>
            <p class="text-xs sm:text-sm text-slate-500">Manage Battery expense categories and type classifications.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer shadow-xs">
            <i class="fa-solid fa-plus"></i> Add Type
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search expense type..." class="w-full h-9 pl-9 pr-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-600">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">SHOW:</span>
                <select wire:model.live="perPage" class="h-9 px-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 outline-none cursor-pointer">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto hidden lg:block">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Type Name</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($types as $idx => $t)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 text-slate-500 font-medium">{{ $types->firstItem() + $idx }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $t->type_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ strtolower((string)($t->status ?? 'active')) === 'inactive' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                    {{ ucfirst((string) ($t->status ?: 'active')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $t->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 inline-flex items-center justify-center cursor-pointer transition border border-emerald-200 dark:border-emerald-800" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $t->id }})" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center cursor-pointer transition border border-rose-200 dark:border-rose-800 ml-1" title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No expense types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile & Tablet Cards View (1 col mobile, 2 col tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($types as $idx => $t)
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs space-y-2.5 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 gap-1.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">
                            #{{ $types->firstItem() + $idx }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ strtolower((string)($t->status ?? 'active')) === 'inactive' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                            {{ ucfirst((string) ($t->status ?: 'active')) }}
                        </span>
                    </div>
                    <div>
                        <h6 class="text-sm font-bold text-slate-900 dark:text-white break-words m-0">
                            {{ $t->type_name }}
                        </h6>
                    </div>
                    <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="openEdit({{ $t->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 inline-flex items-center justify-center cursor-pointer transition border border-emerald-200 dark:border-emerald-800" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $t->id }})" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center cursor-pointer transition border border-rose-200 dark:border-rose-800" title="Delete">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-500">No expense types found.</div>
            @endforelse
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between p-3 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $types->firstItem() ?? 0 }} – {{ $types->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $types->total() }}</span>
                <span>entries</span>
            </div>
            <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                {{ $types->links() }}
            </div>
        </div>
    </div>

    {{-- CREATE / EDIT EXPENSE TYPE MODAL (1:1 Reference) --}}
    @if($showModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[70] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-layer-group"></i> {{ $editingId ? 'Edit Expense Type' : 'Add Expense Type' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="save" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Expense Type Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="type_name" placeholder="Enter expense type name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('type_name') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="status" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeModal" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Type</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
