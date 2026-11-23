<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Geographic Masters</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage Districts, Upazilas, Thanas, and Customer Delivery Locations.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold shadow-xs flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus"></i> Add {{ ucfirst(rtrim($tab, 's')) }}
        </button>
    </div>

    {{-- Tab Bar --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['districts' => 'Districts', 'upazilas' => 'Upazilas', 'thanas' => 'Thanas', 'locations' => 'Locations'] as $k => $label)
            <button
                type="button"
                wire:click="switchTab('{{ $k }}')"
                class="h-9 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === $k ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-600' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search {{ $tab }}..." class="h-9 px-3 w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-emerald-700">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Name</th>
                        @if(in_array($tab, ['upazilas', 'thanas']))
                            <th class="px-4 py-3">District</th>
                        @endif
                        @if($tab === 'thanas')
                            <th class="px-4 py-3">Upazila</th>
                        @endif
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($items as $idx => $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-slate-500">{{ $items->firstItem() + $idx }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">
                                {{ $item->district_name ?? $item->upazila_name ?? $item->Thana_name ?? $item->name }}
                            </td>
                            @if(in_array($tab, ['upazilas', 'thanas']))
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->district?->district_name ?? '—' }}</td>
                            @endif
                            @if($tab === 'thanas')
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->upazila?->upazila_name ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ ucfirst((string) ($item->status ?: 'active')) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $item->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Delete this item?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $items->firstItem() ?? 0 }} – {{ $items->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $items->total() }}</span>
                    <span>locations</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $items->links() }}
                </div>
            </div>
        @endif
    </div>

    <x-ui.modal :show="$showModal" :title="($editingId ? 'Edit ' : 'Add ') . ucfirst(rtrim($tab, 's'))" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            @if(in_array($tab, ['upazilas', 'thanas']))
                <div>
                    <label class="block text-xs font-bold mb-1">District *</label>
                    <select wire:model.live="district_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select District</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->district_name }}</option>
                        @endforeach
                    </select>
                    @error('district_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif
            @if($tab === 'thanas')
                <div>
                    <label class="block text-xs font-bold mb-1">Upazila</label>
                    <select wire:model="upazila_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select Upazila</option>
                        @foreach($upazilas as $u)
                            <option value="{{ $u->id }}">{{ $u->upazila_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold mb-1">Name *</label>
                <input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Status</label>
                <select wire:model="status" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-sm font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save</button>
            </div>
        </form>
    </x-ui.modal>
</div>
