<?php

namespace App\Livewire\Retail\Purchase;

use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailSupplier;
use App\Services\Retail\RetailPurchaseService;
use App\Services\Shared\SafeImageUploadService;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Purchases')]
class RetailPurchaseList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    // Create / Edit Modal
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

    public $attachment;

    public bool $hasMultiplePayments = false;

    /** @var array<int, array{product_id: int, product_name: string, quantity: int, cost_price: string}> */
    public array $items = [];

    // Direct Payment Modal
    public bool $showPayModal = false;

    public ?int $payPurchaseId = null;

    public string $payPurchaseCode = '';

    public string $payCurrentDue = '0.00';

    public string $payAmount = '0';

    public string $payDiscount = '0';

    public string $payMethod = 'Cash';

    public string $payDate = '';

    // Split Invoice Modal
    public bool $showSplitModal = false;

    public ?int $splitPurchaseId = null;

    public string $splitPurchaseCode = '';

    /** @var array<int, array{id: int, product_name: string, quantity: int, cost_price: string, subtotal: string, return_quantity: int}> */
    public array $splitDetails = [];

    /** @var list<int> */
    public array $splitSelectedIds = [];

    public string $splitNewDiscount = '0';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'supplier_id', 'referance_no', 'addProductId', 'attachment', 'items', 'hasMultiplePayments']);
        $this->date = Carbon::today()->toDateString();
        $this->discount_type = 'fixed';
        $this->discount_value = '0';
        $this->delivery_charge = '0';
        $this->paid_amount = '0';
        $this->payment_method = 'Cash';
        $this->showFormModal = true;
    }

    public function openEdit(int $purchaseId): void
    {
        $p = RetailPurchase::with(['orderDetails.product', 'paymentDetails'])->findOrFail($purchaseId);
        $this->editingId = $p->id;
        $this->supplier_id = $p->supplier_id;
        $this->date = Carbon::parse($p->date ?? now())->toDateString();
        $this->referance_no = (string) ($p->referance_no ?? '');
        $this->discount_type = $p->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->discount_value = $this->discount_type === 'percent'
            ? (string) ($p->discount_rate ?? 0)
            : Money::round($p->discount_amount ?? 0);
        $this->delivery_charge = Money::round($p->delivery_charge ?? 0);
        $this->paid_amount = Money::round($p->paid_amount ?? 0);
        $this->payment_method = $p->paymentDetails->first()?->payment_method ?? 'Cash';
        $this->hasMultiplePayments = $p->paymentDetails->count() > 1;
        $this->attachment = null;
        $this->items = $p->orderDetails->map(fn ($d) => [
            'product_id' => (int) $d->product_id,
            'product_name' => (string) ($d->product?->product_name ?? "Product #{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'cost_price' => Money::round($d->cost_price ?? 0),
        ])->values()->all();
        $this->showFormModal = true;
    }

    public function addItem(): void
    {
        if (! $this->addProductId) {
            return;
        }
        $prod = RetailProduct::find((int) $this->addProductId);
        if (! $prod) {
            return;
        }
        $this->items[] = [
            'product_id' => $prod->id,
            'product_name' => (string) $prod->product_name,
            'quantity' => 1,
            'cost_price' => Money::round($prod->cost_price ?? 0),
        ];
        $this->addProductId = '';
    }

    public function removeItem(int $idx): void
    {
        unset($this->items[$idx]);
        $this->items = array_values($this->items);
    }

    public function savePurchase(RetailPurchaseService $service, SafeImageUploadService $uploads): void
    {
        $this->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'date' => ['required', 'date'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        try {
            $attachPath = null;
            if ($this->attachment) {
                $attachPath = $uploads->store($this->attachment, 'purchases', null, true);
            }

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
            if ($attachPath) {
                $payload['attach_document'] = $attachPath;
            }
            if (! $this->editingId || ! $this->hasMultiplePayments) {
                $payload['paid_amount'] = $this->paid_amount;
            }

            if ($this->editingId) {
                $p = $service->update($this->editingId, $payload, (int) Auth::id());
                $msg = "Purchase {$p->purchase_id} updated!";
            } else {
                $p = $service->create($payload, (int) Auth::id());
                $msg = "Purchase {$p->purchase_id} created!";
            }

            $this->showFormModal = false;
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openPay(int $purchaseId): void
    {
        $p = RetailPurchase::findOrFail($purchaseId);
        $this->payPurchaseId = $p->id;
        $this->payPurchaseCode = (string) $p->purchase_id;
        $this->payCurrentDue = Money::round($p->due_amount ?? 0);
        $this->payAmount = $this->payCurrentDue;
        $this->payDiscount = '0';
        $this->payMethod = 'Cash';
        $this->payDate = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPay(RetailPurchaseService $service): void
    {
        try {
            $service->recordPayment((int) $this->payPurchaseId, [
                'paid_amount' => $this->payAmount,
                'discount_amount' => $this->payDiscount,
                'payment_method' => $this->payMethod,
                'date' => $this->payDate ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: "Payment recorded for {$this->payPurchaseCode}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openSplit(int $purchaseId): void
    {
        $p = RetailPurchase::with('orderDetails.product')->findOrFail($purchaseId);
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

    public function submitSplit(RetailPurchaseService $service): void
    {
        try {
            $newP = $service->splitInvoice(
                (int) $this->splitPurchaseId,
                array_map('intval', $this->splitSelectedIds),
                ['new_discount_amount' => $this->splitNewDiscount],
                (int) Auth::id(),
            );
            $this->showSplitModal = false;
            $this->dispatch('toast', type: 'success', message: "Split successful! New purchase {$newP->purchase_id} created.");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function deletePurchase(int $purchaseId, RetailPurchaseService $service): void
    {
        try {
            $service->delete($purchaseId);
            $this->dispatch('toast', type: 'success', message: 'Purchase deleted and stock reversed cleanly.');
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
        $query = RetailPurchase::with(['supplier', 'orderDetails'])->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('purchase_id', 'like', $t)->orWhere('referance_no', 'like', $t)->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', $t)));
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        $formSub = Money::sum(array_map(fn ($i) => Money::mul($i['cost_price'] ?? 0, max(0, (int) ($i['quantity'] ?? 0))), $this->items));

        return view('livewire.retail.purchase.retail-purchase-list', [
            'purchases' => $query->paginate($this->perPage),
            'suppliers' => $this->showFormModal ? RetailSupplier::orderBy('name')->get(['id', 'name']) : collect(),
            'products' => $this->showFormModal ? RetailProduct::orderBy('product_name')->get(['id', 'product_name', 'cost_price']) : collect(),
            'formSubTotal' => $formSub,
        ]);
    }
}
