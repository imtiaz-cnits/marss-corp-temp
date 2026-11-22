<?php

namespace App\Livewire\Retail\Returns;

use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProductReturn;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseReturn;
use App\Services\Retail\RetailReturnService;
use App\Support\DateFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Return Management')]
class RetailReturnManagement extends Component
{
    use WithPagination;

    public string $tab = 'sales';

    // Toolbar & Filters
    public string $quickInvoiceSearch = '';

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    // Create Modal
    public bool $showCreateModal = false;

    public string $date = '';

    public ?int $selectedOrderId = null;

    public ?int $selectedPurchaseId = null;

    public ?RetailOrder $selectedOrderModel = null;

    public ?RetailPurchase $selectedPurchaseModel = null;

    /** @var array<int, array{detail_id: int, product_name: string, sold_qty: int, max_qty: int, unit_price: string, return_qty: int, checked: bool}> */
    public array $returnLines = [];

    public bool $selectAllLines = false;

    public string $returnReason = '';

    // Summary Modal (1:1 Reference Screenshot 5)
    public bool $showSummaryModal = false;

    public ?RetailProductReturn $summaryReturn = null;

    public ?RetailPurchaseReturn $summaryPurchaseReturn = null;

    /** @var \Illuminate\Support\Collection<int, RetailProductReturn|RetailPurchaseReturn> */
    public $summaryItems = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['sales', 'purchase'], true) ? $tab : 'sales';
        $this->reset(['search', 'startDate', 'endDate', 'quickInvoiceSearch']);
        $this->resetPage();
    }

    public function quickSearchAndReturn(): void
    {
        $term = trim($this->quickInvoiceSearch);
        if ($term === '') {
            $this->dispatch('toast', type: 'error', message: 'Please enter an invoice number.');

            return;
        }

        $clean = ltrim($term, '#');
        $numOnly = (int) preg_replace('/[^0-9]/', '', $term);

        if ($this->tab === 'sales') {
            $order = RetailOrder::query()
                ->where('order_no', $term)
                ->orWhere('order_no', '#'.$clean)
                ->orWhere('order_no', $clean)
                ->orWhere('order_no', 'like', '%'.$clean.'%')
                ->when($numOnly > 0, fn ($q) => $q->orWhere('id', $numOnly))
                ->first();

            if (! $order) {
                $this->dispatch('toast', type: 'error', message: "No sales order found matching '{$term}'.");

                return;
            }

            $this->openCreateModal();
            $this->selectedOrderId = $order->id;
            $this->updatedSelectedOrderId($order->id);
        } else {
            $purchase = RetailPurchase::query()
                ->where('purchase_id', $term)
                ->orWhere('purchase_id', '#'.$clean)
                ->orWhere('purchase_id', $clean)
                ->orWhere('purchase_id', 'like', '%'.$clean.'%')
                ->when($numOnly > 0, fn ($q) => $q->orWhere('id', $numOnly))
                ->first();

            if (! $purchase) {
                $this->dispatch('toast', type: 'error', message: "No purchase order found matching '{$term}'.");

                return;
            }

            $this->openCreateModal();
            $this->selectedPurchaseId = $purchase->id;
            $this->updatedSelectedPurchaseId($purchase->id);
        }
    }

    public function openCreateModal(): void
    {
        $this->reset(['selectedOrderId', 'selectedPurchaseId', 'selectedOrderModel', 'selectedPurchaseModel', 'returnLines', 'returnReason', 'selectAllLines']);
        $this->date = Carbon::today()->toDateString();
        $this->showCreateModal = true;
    }

    public function updatedSelectAllLines(bool $value): void
    {
        foreach ($this->returnLines as $idx => $line) {
            $this->returnLines[$idx]['checked'] = $value;
            if ($value && (int) ($this->returnLines[$idx]['return_qty'] ?? 0) === 0) {
                $this->returnLines[$idx]['return_qty'] = $this->returnLines[$idx]['max_qty'];
            } elseif (! $value) {
                $this->returnLines[$idx]['return_qty'] = 0;
            }
        }
    }

    public function updatedReturnLines($value, $key): void
    {
        if (str_ends_with($key, '.checked')) {
            $idx = (int) explode('.', $key)[0];
            if ($value && (int) ($this->returnLines[$idx]['return_qty'] ?? 0) === 0) {
                $this->returnLines[$idx]['return_qty'] = $this->returnLines[$idx]['max_qty'];
            } elseif (! $value) {
                $this->returnLines[$idx]['return_qty'] = 0;
            }
            $this->selectAllLines = ! empty($this->returnLines) && collect($this->returnLines)->every(fn ($l) => ! empty($l['checked']));
        } elseif (str_ends_with($key, '.return_qty')) {
            $idx = (int) explode('.', $key)[0];
            if ((int) $value > 0) {
                $this->returnLines[$idx]['checked'] = true;
            }
            $this->selectAllLines = ! empty($this->returnLines) && collect($this->returnLines)->every(fn ($l) => ! empty($l['checked']));
        }
    }

    public function updatedSelectedOrderId($value): void
    {
        $this->returnLines = [];
        $this->selectedOrderModel = null;
        $this->selectAllLines = false;

        if (! $value) {
            return;
        }

        $order = RetailOrder::with(['customer', 'details.product'])->find((int) $value);
        if (! $order) {
            return;
        }

        $this->selectedOrderModel = $order;

        foreach ($order->details as $d) {
            $sold = (int) round((float) $d->quantity);
            $alreadyReturned = (int) ($d->return_quantity ?? 0);
            $max = max(0, $sold - $alreadyReturned);

            if ($max > 0) {
                $this->returnLines[] = [
                    'detail_id' => $d->id,
                    'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
                    'sold_qty' => $sold,
                    'max_qty' => $max,
                    'unit_price' => (string) $d->selling_price,
                    'return_qty' => 0,
                    'checked' => false,
                ];
            }
        }
    }

    public function updatedSelectedPurchaseId($value): void
    {
        $this->returnLines = [];
        $this->selectedPurchaseModel = null;
        $this->selectAllLines = false;

        if (! $value) {
            return;
        }

        $purchase = RetailPurchase::with(['supplier', 'orderDetails.product'])->find((int) $value);
        if (! $purchase) {
            return;
        }

        $this->selectedPurchaseModel = $purchase;

        foreach ($purchase->orderDetails as $d) {
            $bought = (int) round((float) $d->quantity);
            $alreadyReturned = (int) ($d->return_quantity ?? 0);
            $max = max(0, $bought - $alreadyReturned);

            if ($max > 0) {
                $this->returnLines[] = [
                    'detail_id' => $d->id,
                    'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
                    'sold_qty' => $bought,
                    'max_qty' => $max,
                    'unit_price' => (string) $d->cost_price,
                    'return_qty' => 0,
                    'checked' => false,
                ];
            }
        }
    }

    public function submitReturn(RetailReturnService $service): void
    {
        try {
            $validLines = array_filter($this->returnLines, fn ($l) => ! empty($l['checked']) && (int) ($l['return_qty'] ?? 0) > 0);

            if (empty($validLines)) {
                $this->dispatch('toast', type: 'error', message: 'Specify at least one checked product with a return quantity greater than zero.');

                return;
            }

            if ($this->tab === 'sales') {
                $items = array_map(fn ($l) => ['order_detail_id' => $l['detail_id'], 'quantity' => (int) $l['return_qty']], array_values($validLines));
                $service->createSalesReturn((int) $this->selectedOrderId, $items, $this->date ?: Carbon::today()->toDateString(), (int) Auth::id());
                $msg = 'Retail sales return processed and stock restored!';
            } else {
                $items = array_map(fn ($l) => ['purchase_order_detail_id' => $l['detail_id'], 'quantity' => (int) $l['return_qty']], array_values($validLines));
                $service->createPurchaseReturn((int) $this->selectedPurchaseId, $items, $this->date ?: Carbon::today()->toDateString(), (int) Auth::id());
                $msg = 'Retail purchase return processed and stock deducted!';
            }

            $this->showCreateModal = false;
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openSummary(int $id): void
    {
        if ($this->tab === 'sales') {
            $single = RetailProductReturn::with(['order', 'customer', 'product', 'user'])->findOrFail($id);
            $this->summaryReturn = $single;
            $this->summaryItems = RetailProductReturn::with('product')
                ->where('return_number', $single->return_number)
                ->get();
            $this->summaryPurchaseReturn = null;
        } else {
            $single = RetailPurchaseReturn::with(['purchase', 'supplier', 'product', 'user'])->findOrFail($id);
            $this->summaryPurchaseReturn = $single;
            $this->summaryItems = RetailPurchaseReturn::with('product')
                ->where('return_number', $single->return_number)
                ->get();
            $this->summaryReturn = null;
        }
        $this->showSummaryModal = true;
    }

    public function closeSummaryModal(): void
    {
        $this->showSummaryModal = false;
        $this->summaryReturn = null;
        $this->summaryPurchaseReturn = null;
        $this->summaryItems = [];
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        $salesQuery = RetailProductReturn::with(['order', 'customer', 'product'])->orderByDesc('id');
        $purchaseQuery = RetailPurchaseReturn::with(['purchase', 'supplier', 'product'])->orderByDesc('id');

        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $salesQuery->where(function ($q) use ($t) {
                $q->where('return_number', 'like', $t)
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_no', 'like', $t))
                    ->orWhereHas('customer', fn ($cq) => $cq->where('customer_name', 'like', $t))
                    ->orWhereHas('product', fn ($pq) => $pq->where('product_name', 'like', $t));
            });

            $purchaseQuery->where(function ($q) use ($t) {
                $q->where('return_number', 'like', $t)
                    ->orWhereHas('purchase', fn ($pq) => $pq->where('purchase_id', 'like', $t))
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', $t))
                    ->orWhereHas('product', fn ($pq) => $pq->where('product_name', 'like', $t));
            });
        }

        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($salesQuery, 'date', $this->startDate ?: null, $this->endDate ?: null);
            DateFilter::applyDateFilter($purchaseQuery, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.retail.returns.retail-return-management', [
            'salesReturns' => $this->tab === 'sales' ? $salesQuery->paginate($this->perPage) : null,
            'purchaseReturns' => $this->tab === 'purchase' ? $purchaseQuery->paginate($this->perPage) : null,
            'orders' => $this->showCreateModal && $this->tab === 'sales'
                ? RetailOrder::with('customer')->orderByDesc('id')->limit(100)->get()
                : collect(),
            'purchases' => $this->showCreateModal && $this->tab === 'purchase'
                ? RetailPurchase::with('supplier')->orderByDesc('id')->limit(100)->get()
                : collect(),
        ]);
    }
}
