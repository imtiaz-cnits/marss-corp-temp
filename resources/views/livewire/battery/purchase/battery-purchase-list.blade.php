@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Purchases</h1>
            <p class="text-xs sm:text-sm text-slate-500">Isolated Battery purchase entry, editing, payment, split invoice, and safe deletion.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('battery.purchase.payments') }}" wire:navigate class="h-10 px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold inline-flex items-center">Payment Ledger</a>
            <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">+ New Purchase</button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Purchase #, supplier..." class="h-[38px] px-3 w-52 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            </div>
        </div>
        <table id="batPurchTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Purchase #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3 text-right">Subtotal</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($purchases as $p)
                    @php $hasDue = (float) ($p->due_amount ?? 0) > 0.009; @endphp
                    <tr>
                        <td class="px-3 py-2.5 font-extrabold text-emerald-700">{{ $p->purchase_id }}</td>
                        <td class="px-3 py-2.5 text-slate-500">{{ $p->date }}</td>
                        <td class="px-3 py-2.5 font-bold">{{ $p->supplier?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($p->grand_subtotal) }}</td>
                        <td class="px-3 py-2.5 text-right text-emerald-700 font-bold tabular-nums">{{ Money::formatBd($p->paid_amount) }}</td>
                        <td class="px-3 py-2.5 text-right font-bold {{ $hasDue ? 'text-rose-600' : 'text-slate-400' }} tabular-nums">{{ Money::formatBd($p->due_amount) }}</td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                            @if($hasDue)<button type="button" wire:click="openPay({{ $p->id }})" class="px-2 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-money-bill-wave"></i></button>@endif
                            @if($p->orderDetails->count() > 1)<button type="button" wire:click="openSplit({{ $p->id }})" class="px-2 py-1 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-code-branch"></i></button>@endif
                            <button type="button" wire:click="openEdit({{ $p->id }})" class="px-2 py-1 rounded-lg bg-sky-100 text-sky-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                            <a href="{{ route('battery.purchase.invoice.show', $p->id) }}" target="_blank" class="inline-block px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold"><i class="fa-solid fa-print"></i></a>
                            <button type="button" wire:click="deletePurchase({{ $p->id }})" wire:confirm="Delete purchase {{ $p->purchase_id }}?" class="px-2 py-1 rounded-lg bg-red-100 text-red-600 text-xs font-bold cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row="true"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Battery purchases recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $purchases->links() }}</div>
    </div>

    <x-ui.modal :show="$showFormModal" max-width="full-edit" :title="$editingId ? 'Edit Battery Purchase' : 'New Battery Purchase'" close-method="closeModals">
        <form wire:submit="savePurchase" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="block text-xs font-bold mb-1">Supplier *</label><select wire:model="supplier_id" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Date *</label><x-ui.date-picker wire:model="date" /></div>
                <div><label class="block text-xs font-bold mb-1">Reference</label><input type="text" wire:model="referance_no" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex gap-2">
                <select wire:model="addProductId" class="flex-1 h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"><option value="">+ Add Battery product...</option>@foreach($products as $pr)<option value="{{ $pr->id }}">{{ $pr->product_name }}</option>@endforeach</select>
                <button type="button" wire:click="addItem" class="h-9 px-4 rounded-xl bg-emerald-700 text-white text-xs font-bold">Add</button>
            </div>
            <table class="w-full text-xs border border-slate-200 dark:border-slate-700">
                <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Cost</th><th class="px-3 py-2 w-10"></th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($items as $idx => $it)
                        <tr><td class="px-3 py-2 font-semibold">{{ $it['product_name'] }}</td><td class="px-3 py-2"><input type="number" min="1" wire:model="items.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td><td class="px-3 py-2"><input type="number" step="0.01" wire:model="items.{{ $idx }}.cost_price" class="w-28 h-8 text-right px-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td><td class="px-3 py-2 text-center"><button type="button" wire:click="removeItem({{ $idx }})" class="text-red-500"><i class="fa-solid fa-trash"></i></button></td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div><label class="block text-xs font-bold mb-1">Discount</label><input type="number" step="0.01" wire:model="discount_value" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Delivery</label><input type="number" step="0.01" wire:model="delivery_charge" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Paid (৳)</label><input type="number" step="0.01" wire:model="paid_amount" @disabled($editingId && $hasMultiplePayments) class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold disabled:opacity-50"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="Bank">Bank</option><option value="bKash">bKash</option></select></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save Purchase</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showPayModal" :title="'Pay Battery Purchase — ' . $payPurchaseCode" close-method="closeModals">
        <form wire:submit="submitPay" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 flex justify-between text-sm font-bold"><span>Due:</span><span class="text-rose-600">{{ Money::formatBd($payCurrentDue) }}</span></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid *</label><input type="number" step="0.01" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount</label><input type="number" step="0.01" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="Bank">Bank</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="payDate" /></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Confirm</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showSplitModal" max-width="lg" :title="'Split Battery Purchase — ' . $splitPurchaseCode" close-method="closeModals">
        <form wire:submit="submitSplit" class="space-y-3">
            <div class="divide-y border rounded-xl">
                @foreach($splitDetails as $sd)
                    <label class="flex items-center justify-between p-3 text-xs sm:text-sm cursor-pointer">
                        <span class="flex items-center gap-2"><input type="checkbox" value="{{ $sd['id'] }}" wire:model="splitSelectedIds" @disabled($sd['return_quantity'] > 0)> <strong>{{ $sd['product_name'] }}</strong> (x{{ $sd['quantity'] }})</span>
                        <span class="font-bold">{{ Money::formatBd($sd['subtotal']) }}</span>
                    </label>
                @endforeach
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-purple-700 text-white text-sm font-bold">Split</button></div>
        </form>
    </x-ui.modal>
</div>
