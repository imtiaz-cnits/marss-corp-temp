<?php

namespace App\Livewire\Retail\Returns;

use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProductReturn;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseReturn;
use App\Services\Retail\RetailReturnService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Return Management')]
class RetailReturnManagement extends Component
{
    use WithPagination;

    public string $tab = 'sales';

    public bool $showCreateModal = false;

    public string $date = '';

    public ?int $selectedOrderId = null;

    public ?int $selectedPurchaseId = null;

    /** @var array<int, array{detail_id: int, product_name: string, max_qty: int, unit_price: string, return_qty: int}> */
    public array $returnLines = [];

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['sales', 'purchase'], true) ? $tab : 'sales';
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['selectedOrderId', 'selectedPurchaseId', 'returnLines']);
        $this->date = Carbon::today()->toDateString();
        $this->showCreateModal = true;
    }

    public function updatedSelectedOrderId($value): void
    {
        $this->returnLines = [];
        if (! $value) {
            return;
        }
        $order = RetailOrder::with('details.product')->find((int) $value);
        if (! $order) {
            return;
        }
        foreach ($order->details as $d) {
            $max = max(0, (int) round((float) $d->quantity) - (int) ($d->return_quantity ?? 0));
            if ($max > 0) {
                $this->returnLines[] = [
                    'detail_id' => $d->id,
                    'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
                    'max_qty' => $max,
                    'unit_price' => (string) $d->selling_price,
                    'return_qty' => 0,
                ];
            }
        }
    }

    public function updatedSelectedPurchaseId($value): void
    {
        $this->returnLines = [];
        if (! $value) {
            return;
        }
        $purchase = RetailPurchase::with('orderDetails.product')->find((int) $value);
        if (! $purchase) {
            return;
        }
        foreach ($purchase->orderDetails as $d) {
            $max = max(0, (int) round((float) $d->quantity) - (int) ($d->return_quantity ?? 0));
            if ($max > 0) {
                $this->returnLines[] = [
                    'detail_id' => $d->id,
                    'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
                    'max_qty' => $max,
                    'unit_price' => (string) $d->cost_price,
                    'return_qty' => 0,
                ];
            }
        }
    }

    public function submitReturn(RetailReturnService $service): void
    {
        try {
            if ($this->tab === 'sales') {
                $items = array_map(fn ($l) => ['order_detail_id' => $l['detail_id'], 'quantity' => (int) $l['return_qty']], $this->returnLines);
                $service->createSalesReturn((int) $this->selectedOrderId, $items, $this->date ?: Carbon::today()->toDateString(), (int) Auth::id());
                $msg = 'Retail sales return processed and stock restored!';
            } else {
                $items = array_map(fn ($l) => ['purchase_order_detail_id' => $l['detail_id'], 'quantity' => (int) $l['return_qty']], $this->returnLines);
                $service->createPurchaseReturn((int) $this->selectedPurchaseId, $items, $this->date ?: Carbon::today()->toDateString(), (int) Auth::id());
                $msg = 'Retail purchase return processed and stock deducted!';
            }
            $this->showCreateModal = false;
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        return view('livewire.retail.returns.retail-return-management', [
            'salesReturns' => $this->tab === 'sales'
                ? RetailProductReturn::with(['order', 'customer', 'product'])->orderByDesc('id')->paginate(15)
                : null,
            'purchaseReturns' => $this->tab === 'purchase'
                ? RetailPurchaseReturn::with(['purchase', 'supplier', 'product'])->orderByDesc('id')->paginate(15)
                : null,
            'orders' => $this->showCreateModal && $this->tab === 'sales'
                ? RetailOrder::with('customer')->orderByDesc('id')->limit(150)->get()
                : collect(),
            'purchases' => $this->showCreateModal && $this->tab === 'purchase'
                ? RetailPurchase::with('supplier')->orderByDesc('id')->limit(150)->get()
                : collect(),
        ]);
    }
}
