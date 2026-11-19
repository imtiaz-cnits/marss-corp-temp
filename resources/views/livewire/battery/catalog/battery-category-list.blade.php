<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Categories</h1>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Category</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800"><input type="text" wire:model.live.debounce.300ms="search" placeholder="Search battery category..." class="h-9 px-3 w-64 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
        <table class="w-full text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Category Name</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($categories as $idx => $c)
                    <tr>
                        <td class="px-4 py-3 text-slate-500">{{ $categories->firstItem() + $idx }}</td>
                        <td class="px-4 py-3 font-bold">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="openEdit({{ $c->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                            <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="Delete '{{ $c->name }}'?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">No Battery categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $categories->links() }}</div>
    </div>
    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Battery Category' : 'Add Battery Category'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Category Name *</label><input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div><label class="block text-xs font-bold mb-1">Image</label><input type="file" wire:model="image" accept="image/*" class="w-full text-xs"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>
</div>
