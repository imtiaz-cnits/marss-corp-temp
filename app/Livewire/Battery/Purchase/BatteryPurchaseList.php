<?php

namespace App\Livewire\Battery\Purchase;

use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatterySupplier;
use App\Services\Battery\BatteryPurchaseService;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Purchases')]
class BatteryPurchaseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $supplier_id = null;

    public string $date = '';

    public string $referance_no = '';

    public string $discount_type = 'fixed';

    public string $discount_value = '0';

    public string $delivery_charge = '0';

    public string $paid_amount = '0';

    public string $payment_method = 'Cash';

    public string $addProductId = '';

    public bool $hasMultiplePayments = false;

    public array $items = [];

    public bool $showPayModal = false;

    public ?int $payPurchaseId = null;

    public string $payPurchaseCode = '';

    public string $payCurrentDue = '0.00';

    public string $payAmount = '0';

    public string $payDiscount = '0';

    public string $payMethod = 'Cash';

    public string $payDate = '';

    public bool $showSplitModal = false;

    public ?int $splitPurchaseId = null;

    public string $splitPurchaseCode = '';

    public array $splitDetails = [];

    public array $splitSelectedIds = [];

    public string $splitNewDiscount = '0';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'supplier_id', 'referance_no', 'addProductId', 'items', 'hasMultiplePayments']);
        $this->date = Carbon::today()->toDateString();
        $this->discount_type = 'fixed';
        $this->discount_value = '0';
        $this->delivery_charge = '0';
        $this->paid_amount = '0';
        $this->payment_method = 'Cash';
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = BatteryPurchase::with(['orderDetails.product', 'paymentDetails'])->findOrFail($id);
        $this->editingId = $p->id;
        $this->supplier_id = $p->supplier_id;
        $this->date = Carbon::parse($p->date ?? now())->toDateString();
        $this->referance_no = (string) ($p->referance_no ?? '');
        $this->discount_type = $p->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->discount_value = $this->discount_type === 'percent' ? (string) ($p->discount_rate ?? 0) : Money::round($p->discount_amount ?? 0);
        $this->delivery_charge = Money::round($p->delivery_charge ?? 0);
        $this->paid_amount = Money::round($p->paid_amount ?? 0);
        $this->hasMultiplePayments = $p->paymentDetails->count() > 1;
        $this->items = $p->orderDetails->map(fn ($d) => [
            'product_id' => (int) $d->product_id,
            'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'cost_price' => Money::round($d->cost_price ?? 0),
        ])->values()->all();
        $this->showFormModal = true;
    }

    public function addItem(): void
    {
        $pr = BatteryProduct::find((int) $this->addProductId);
        if (! $pr) {
            return;
        }
        $this->items[] = [
            'product_id' => $pr->id,
            'product_name' => (string) $pr->product_name,
            'quantity' => 1,
            'cost_price' => Money::round($pr->cost_price ?? 0),
        ];
        $this->addProductId = '';
    }

    public function removeItem(int $idx): void
    {
        unset($this->items[$idx]);
        $this->items = array_values($this->items);
    }

    public function savePurchase(BatteryPurchaseService $service): void
    {
        $this->validate(['supplier_id' => ['required', 'integer', 'exists:battery_suppliers,id'], 'date' => ['required', 'date']]);
        try {
            $payload = [
                'supplier_id' => $this->supplier_id,
                'date' => $this->date,
                'referance_no' => $this->referance_no ?: null,
                'delivery_charge' => $this->delivery_charge,
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_type === 'percent' ? $this->discount_value : null,
                'discount_amount' => $this->discount_type === 'fixed' ? $this->discount_value : null,
                'payment_method' => $this->payment_method,
                'items' => $this->items,
            ];
            if (! $this->editingId || ! $this->hasMultiplePayments) {
                $payload['paid_amount'] = $this->paid_amount;
            }
            $p = $this->editingId
                ? $service->update($this->editingId, $payload, (int) Auth::id())
                : $service->create($payload, (int) Auth::id());
            $this->showFormModal = false;
            $this->dispatch('toast', type: 'success', message: "Battery purchase {$p->purchase_id} saved!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openPay(int $id): void
    {
        $p = BatteryPurchase::findOrFail($id);
        $this->payPurchaseId = $p->id;
        $this->payPurchaseCode = (string) $p->purchase_id;
        $this->payCurrentDue = Money::round($p->due_amount ?? 0);
        $this->payAmount = $this->payCurrentDue;
        $this->payDiscount = '0';
        $this->payDate = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPay(BatteryPurchaseService $service): void
    {
        try {
            $service->recordPayment((int) $this->payPurchaseId, [
                'paid_amount' => $this->payAmount,
                'discount_amount' => $this->payDiscount,
                'payment_method' => $this->payMethod,
                'date' => $this->payDate ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery purchase payment recorded!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openSplit(int $id): void
    {
        $p = BatteryPurchase::with('orderDetails.product')->findOrFail($id);
        $this->splitPurchaseId = $p->id;
        $this->splitPurchaseCode = (string) $p->purchase_id;
        $this->splitSelectedIds = [];
        $this->splitNewDiscount = '0';
        $this->splitDetails = $p->orderDetails->map(fn ($d) => [
            'id' => $d->id,
            'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'cost_price' => Money::round($d->cost_price ?? 0),
            'subtotal' => Money::round($d->subtotal ?? 0),
            'return_quantity' => (int) ($d->return_quantity ?? 0),
        ])->values()->all();
        $this->showSplitModal = true;
    }

    public function submitSplit(BatteryPurchaseService $service): void
    {
        try {
            $newP = $service->splitInvoice((int) $this->splitPurchaseId, array_map('intval', $this->splitSelectedIds), ['new_discount_amount' => $this->splitNewDiscount], (int) Auth::id());
            $this->showSplitModal = false;
            $this->dispatch('toast', type: 'success', message: "Split into {$newP->purchase_id}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function deletePurchase(int $id, BatteryPurchaseService $service): void
    {
        try {
            $service->delete($id);
            $this->dispatch('toast', type: 'success', message: 'Battery purchase deleted!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showPayModal = false;
        $this->showSplitModal = false;
    }

    public function render()
    {
        $query = BatteryPurchase::with(['supplier', 'orderDetails'])->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('purchase_id', 'like', $t)->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', $t)));
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.purchase.battery-purchase-list', [
            'purchases' => $query->paginate(15),
            'suppliers' => $this->showFormModal ? BatterySupplier::orderBy('name')->get(['id', 'name']) : collect(),
            'products' => $this->showFormModal ? BatteryProduct::orderBy('product_name')->get(['id', 'product_name', 'cost_price']) : collect(),
        ]);
    }
}
