<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Sub-Categories</h1>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Sub-Category</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Sub-Category</th><th class="px-4 py-3">Parent Category</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($subCategories as $idx => $sc)
                    <tr>
                        <td class="px-4 py-3 text-slate-500">{{ $subCategories->firstItem() + $idx }}</td>
                        <td class="px-4 py-3 font-bold">{{ $sc->name }}</td>
                        <td class="px-4 py-3">{{ $sc->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="openEdit({{ $sc->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                            <button type="button" wire:click="delete({{ $sc->id }})" wire:confirm="Delete?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No sub-categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $subCategories->links() }}</div>
    </div>
    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Sub-Category' : 'Add Sub-Category'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Parent Category *</label><select wire:model="category_id" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-bold mb-1">Name *</label><input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>
</div>
