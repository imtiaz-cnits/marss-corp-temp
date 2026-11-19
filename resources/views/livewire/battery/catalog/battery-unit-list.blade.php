<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Units</h1>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Unit</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Unit Name</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($units as $idx => $u)
                    <tr>
                        <td class="px-4 py-3 text-slate-500">{{ $units->firstItem() + $idx }}</td>
                        <td class="px-4 py-3 font-bold">{{ $u->unit_name }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="openEdit({{ $u->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                            <button type="button" wire:click="delete({{ $u->id }})" wire:confirm="Delete '{{ $u->unit_name }}'?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">No Battery units found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $units->links() }}</div>
    </div>
    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Unit' : 'Add Unit'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Unit Name *</label><input type="text" wire:model="unit_name" placeholder="Pcs, Set, Box..." class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>
</div>
