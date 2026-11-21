@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Customers</h1>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ Add Customer</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex justify-between">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search customer..." class="h-9 px-3 w-64 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
        </div>
        <table id="batCustTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">ID</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Mobile</th><th class="px-3 py-3 text-right">Prev Due</th><th class="px-3 py-3 text-right">Orders Due</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($customers as $c)
                    <tr>
                        <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $c->customer_id }}</td>
                        <td class="px-3 py-2.5 font-bold"><a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="text-emerald-700 hover:underline">{{ $c->customer_name }}</a></td>
                        <td class="px-3 py-2.5">{{ $c->mobile ?: '—' }}</td>
                        <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($c->previous_due_amount) }}</td>
                        <td class="px-3 py-2.5 text-right font-bold text-rose-600 tabular-nums">{{ Money::formatBd($c->orders_sum_due_amount ?? 0) }}</td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 text-xs font-bold">Profile</a>
                            <button type="button" wire:click="openEdit({{ $c->id }})" class="ml-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                            <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="Delete?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No Battery customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $customers->links() }}</div>
    </div>
    <x-ui.modal :show="$showModal" :title="$editingId ? 'Edit Battery Customer' : 'Add Battery Customer'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Name *</label><input type="text" wire:model="customer_name" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Mobile</label><input type="text" wire:model="mobile" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Email</label><input type="email" wire:model="email" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Opening Due (৳)</label><input type="number" step="0.01" wire:model="previous_due_amount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="col-span-2"><label class="block text-xs font-bold mb-1">Address</label><input type="text" wire:model="address_details" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save</button></div>
        </form>
    </x-ui.modal>
</div>
