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
                            <button type="button" wire:click="confirmDelete({{ $sc->id }})" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
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

    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative w-full max-w-[420px] bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200 dark:border-slate-800 shadow-2xl px-6 pt-7 pb-6 text-center">
                <button type="button" wire:click="closeModal" class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center cursor-pointer" title="Close">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
                <div class="w-[68px] h-[68px] rounded-full bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-100 dark:border-rose-900/60 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-trash-can text-[26px]"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Sub-Category?</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 leading-relaxed mb-5 px-2">
                    Are you sure you want to delete <strong class="text-slate-700 dark:text-slate-200">{{ $deletingName }}</strong>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="closeModal" class="flex-1 h-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-[13.5px] font-semibold cursor-pointer">Cancel</button>
                    <button type="button" wire:click="confirmDeleteAction" class="flex-1 h-[42px] rounded-xl bg-gradient-to-br from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-[13.5px] font-semibold shadow-md inline-flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Yes, Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
