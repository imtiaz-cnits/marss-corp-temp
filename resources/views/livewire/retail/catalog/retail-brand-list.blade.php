<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Brands</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage isolated Retail product brands.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus"></i> Add Brand
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search brand..." class="h-9 px-3 w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
        </div>
        <div class="overflow-x-auto">
            <table id="retailBrandsTable" data-export-skip-last="true" class="w-full text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Brand Name</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($brands as $idx => $b)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-slate-500">{{ $brands->firstItem() + $idx }}</td>
                            <td class="px-4 py-3 font-bold flex items-center gap-2.5">
                                @if($b->logo && file_exists(public_path($b->logo)))
                                    <img src="{{ asset($b->logo) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                @endif
                                <span>{{ $b->name }}</span>
                            </td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">{{ ucfirst((string) ($b->status ?: 'active')) }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $b->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="delete({{ $b->id }})" wire:confirm="Delete brand '{{ $b->name }}'?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="4" class="px-4 py-8 text-center text-slate-500">No brands found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $brands->links() }}</div>
    </div>

    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Brand' : 'Add Brand'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div>
                <label class="block text-xs font-bold mb-1">Brand Name *</label>
                <input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Logo</label>
                <input type="file" wire:model="logo" accept="image/*" class="w-full text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Status</label>
                <select wire:model="status" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="active">Active</option><option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save</button>
            </div>
        </form>
    </x-ui.modal>
</div>
