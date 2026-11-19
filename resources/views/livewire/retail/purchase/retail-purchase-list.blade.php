@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Purchase Invoices</h1>
            <p class="text-xs sm:text-sm text-slate-500">Create, edit, pay, split, and print Retail supplier purchases with WAC inventory reconciliation.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus"></i> New Purchase
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Purchase #, ref, supplier..." class="h-[38px] px-3 w-56 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            </div>
            <x-ui.export-buttons table-id="retailPurchasesTable" filename="retail-purchases" title="MARSS Retail Purchases" />
        </div>
        <div class="overflow-x-auto">
            <table id="retailPurchasesTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Purchase ID</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3 text-right">Subtotal</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th><th class="px-3 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($purchases as $p)
                        @php $hasDue = (float) ($p->due_amount ?? 0) > 0.009; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 font-extrabold text-emerald-700 dark:text-emerald-400">
                                {{ $p->purchase_id }}
                                @if($p->referance_no)<div class="text-[10px] text-slate-400 font-normal">Ref: {{ $p->referance_no }}</div>@endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-500 whitespace-nowrap">{{ $p->date }}</td>
                            <td class="px-3 py-2.5 font-bold">{{ $p->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ Money::formatBd($p->grand_subtotal) }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums font-bold text-emerald-700">{{ Money::formatBd($p->paid_amount) }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums font-bold {{ $hasDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($p->due_amount) }}</td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                                @if($hasDue)
                                    <button type="button" wire:click="openPay({{ $p->id }})" title="Record Payment" class="px-2 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-money-bill-wave"></i></button>
                                @endif
                                @if($p->orderDetails->count() > 1)
                                    <button type="button" wire:click="openSplit({{ $p->id }})" title="Split Invoice" class="px-2 py-1 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-code-branch"></i></button>
                                @endif
                                <button type="button" wire:click="openEdit({{ $p->id }})" title="Edit" class="px-2 py-1 rounded-lg bg-sky-100 text-sky-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                                <a href="{{ route('retail.purchase.show', $p->id) }}" target="_blank" title="Print" class="inline-block px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold"><i class="fa-solid fa-print"></i></a>
                                <button type="button" wire:click="deletePurchase({{ $p->id }})" wire:confirm="Delete purchase {{ $p->purchase_id }} and reverse stock?" title="Delete" class="px-2 py-1 rounded-lg bg-red-100 text-red-600 text-xs font-bold cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Retail purchases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $purchases->links() }}</div>
    </div>

    {{-- Create / Edit Purchase Modal --}}
    <x-ui.modal :show="$showFormModal" max-width="full-edit" :title="$editingId ? 'Edit Retail Purchase' : 'New Retail Purchase'" close-method="closeModals">
        <form wire:submit="savePurchase" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Supplier *</label>
                    <select wire:model="supplier_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                    @error('supplier_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Purchase Date *</label>
                    <x-ui.date-picker wire:model="date" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Reference / Challan No</label>
                    <input type="text" wire:model="referance_no" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>

            <div class="flex gap-2">
                <select wire:model="addProductId" class="flex-1 h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">+ Select Retail product to add...</option>
                    @foreach($products as $pr)<option value="{{ $pr->id }}">{{ $pr->product_name }} (Cost: {{ $pr->cost_price }})</option>@endforeach
                </select>
                <button type="button" wire:click="addItem" class="h-9 px-4 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer">Add Item</button>
            </div>

            <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                <table class="w-full text-xs">
                    <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Unit Cost</th><th class="px-3 py-2 text-right">Subtotal</th><th class="px-3 py-2 w-10"></th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($items as $idx => $it)
                            <tr>
                                <td class="px-3 py-2 font-semibold">{{ $it['product_name'] }}</td>
                                <td class="px-3 py-2"><input type="number" min="1" wire:model.live="items.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"></td>
                                <td class="px-3 py-2"><input type="number" step="0.01" min="0" wire:model.live="items.{{ $idx }}.cost_price" class="w-28 h-8 text-right px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"></td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">{{ Money::formatBd(Money::mul($it['cost_price'] ?? 0, $it['quantity'] ?? 0)) }}</td>
                                <td class="px-3 py-2 text-center"><button type="button" wire:click="removeItem({{ $idx }})" class="text-red-500 cursor-pointer"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-slate-400">Add products above to build purchase invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Discount</label>
                    <div class="flex">
                        <select wire:model="discount_type" class="h-10 px-2 rounded-l-xl border-[1.5px] border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold"><option value="fixed">৳</option><option value="percent">%</option></select>
                        <input type="number" step="0.01" min="0" wire:model="discount_value" class="w-full h-10 px-2 rounded-r-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    </div>
                </div>
                <div><label class="block text-xs font-bold mb-1">Delivery (৳)</label><input type="number" step="0.01" min="0" wire:model="delivery_charge" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Paid (৳)</label><input type="number" step="0.01" min="0" wire:model="paid_amount" @disabled($editingId && $hasMultiplePayments) class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold disabled:opacity-50"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="Bank">Bank</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option></select></div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800">
                <div class="text-sm font-extrabold text-emerald-700">Subtotal: {{ Money::formatBd($formSubTotal) }}</div>
                <div class="flex gap-2">
                    <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Purchase</button>
                </div>
            </div>
        </form>
    </x-ui.modal>

    {{-- Direct Payment Modal --}}
    <x-ui.modal :show="$showPayModal" :title="'Pay Purchase — ' . $payPurchaseCode" close-method="closeModals">
        <form wire:submit="submitPay" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex justify-between text-sm font-bold">
                <span>Remaining Due:</span><span class="text-rose-600">{{ Money::formatBd($payCurrentDue) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid Amount *</label><input type="number" step="0.01" min="0" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount</label><input type="number" step="0.01" min="0" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="Bank">Bank</option><option value="bKash">bKash</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="payDate" /></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Payment</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Split Purchase Modal --}}
    <x-ui.modal :show="$showSplitModal" max-width="lg" :title="'Split Purchase Invoice — ' . $splitPurchaseCode" close-method="closeModals">
        <form wire:submit="submitSplit" class="space-y-3">
            <p class="text-xs text-slate-500">Select items to move into a new purchase invoice (stock remains untouched; payments reconcile proportionally).</p>
            <div class="divide-y divide-slate-200 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl">
                @foreach($splitDetails as $sd)
                    <label class="flex items-center justify-between p-3 text-xs sm:text-sm cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <span class="flex items-center gap-2.5">
                            <input type="checkbox" value="{{ $sd['id'] }}" wire:model="splitSelectedIds" @disabled($sd['return_quantity'] > 0) class="w-4 h-4 rounded text-emerald-700">
                            <span class="font-bold">{{ $sd['product_name'] }}</span>
                            <span class="text-slate-400">x {{ $sd['quantity'] }} @ {{ Money::formatBd($sd['cost_price']) }}</span>
                        </span>
                        <span class="font-bold">{{ Money::formatBd($sd['subtotal']) }}</span>
                    </label>
                @endforeach
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">New Invoice Discount (৳)</label>
                <input type="number" step="0.01" min="0" wire:model="splitNewDiscount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-sm font-bold cursor-pointer">Split Into New Invoice</button>
            </div>
        </form>
    </x-ui.modal>
</div>
