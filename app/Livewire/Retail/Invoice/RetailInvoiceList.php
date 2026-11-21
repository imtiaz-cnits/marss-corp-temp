<?php

namespace App\Livewire\Retail\Invoice;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailProductReturn;
use App\Services\Retail\RetailReturnService;
use App\Services\Retail\RetailSalesService;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Invoice List')]
class RetailInvoiceList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $quickFilter = 'all';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    // Quick View Modal
    public bool $showViewModal = false;

    public ?RetailOrder $viewingOrder = null;

    // Sales Return Modal
    public bool $showReturnModal = false;

    public ?int $returnOrderId = null;

    public string $returnOrderNo = '';

    public string $returnCustomerName = '';

    public string $returnDate = '';

    /** @var array<int, array{order_detail_id: int, product_name: string, sold_qty: int, returned_qty: int, available_qty: int, selling_price: string, quantity: int}> */
    public array $returnItems = [];

    // Direct Due Payment Modal
    public bool $showPaymentModal = false;

    public ?int $payOrderId = null;

    public string $payOrderNo = '';

    public string $payCurrentDue = '0.00';

    public string $payAmount = '0';

    public string $payDiscount = '0';

    public string $payMethod = 'Cash';

    public string $payTrxId = '';

    public string $payDate = '';

    // Full Edit Modal (920px)
    public bool $showEditModal = false;

    public ?int $editOrderId = null;

    public string $editOrderNo = '';

    public ?int $editCustomerId = null;

    public string $editDate = '';

    public string $editDiscountType = 'fixed';

    public string $editDiscountValue = '0';

    public string $editDeliveryCharge = '0';

    public string $editPaidAmount = '0';

    public string $editNote = '';

    public bool $editHasMultiplePayments = false;

    public string $editAddProductId = '';

    /** @var array<int, array{id: ?int, product_id: int, product_name: string, quantity: int, return_quantity: int, price: string, selling_price: string}> */
    public array $editItems = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->quickFilter = ($this->startDate || $this->endDate) ? 'custom' : 'all';
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->quickFilter = ($this->startDate || $this->endDate) ? 'custom' : 'all';
        $this->resetPage();
    }

    public function setQuickFilter(string $filter): void
    {
        $this->quickFilter = $filter;
        $today = Carbon::today();

        if ($filter === 'today') {
            $this->startDate = $today->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '7') {
            $this->startDate = $today->copy()->subDays(6)->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '30') {
            $this->startDate = $today->copy()->subDays(29)->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '365') {
            $this->startDate = $today->copy()->subDays(364)->toDateString();
            $this->endDate = $today->toDateString();
        } else {
            $this->quickFilter = 'all';
            $this->startDate = '';
            $this->endDate = '';
        }

        $this->resetPage();
    }

    public function openView(int $orderId): void
    {
        $this->viewingOrder = RetailOrder::with(['customer', 'user', 'details.product', 'payments', 'productReturns'])->findOrFail($orderId);
        $this->showViewModal = true;
    }

    public function openReturn(int $orderId): void
    {
        $order = RetailOrder::with(['customer', 'details.product'])->findOrFail($orderId);
        $this->returnOrderId = $order->id;
        $this->returnOrderNo = (string) $order->order_no;
        $this->returnCustomerName = (string) ($order->customer?->customer_name ?? 'Walk-in');
        $this->returnDate = Carbon::today()->toDateString();

        $this->returnItems = $order->details->map(function ($d) use ($order) {
            $priorTableReturned = (int) RetailProductReturn::where('order_id', $order->id)
                ->where(function ($q) use ($d) {
                    $q->where('order_detail_id', $d->id)
                        ->orWhere(function ($q2) use ($d) {
                            $q2->whereNull('order_detail_id')->where('product_id', $d->product_id);
                        });
                })
                ->sum('quantity');

            $returnedQty = max((int) ($d->return_quantity ?? 0), $priorTableReturned);
            $soldQty = (int) round((float) $d->quantity);
            $availableQty = max(0, $soldQty - $returnedQty);

            return [
                'order_detail_id' => $d->id,
                'product_name' => (string) ($d->product?->product_name ?? "Product #{$d->product_id}"),
                'sold_qty' => $soldQty,
                'returned_qty' => $returnedQty,
                'available_qty' => $availableQty,
                'selling_price' => Money::round($d->selling_price ?? 0),
                'quantity' => 0,
            ];
        })->values()->all();

        $this->showReturnModal = true;
    }

    public function submitReturn(RetailReturnService $returnService): void
    {
        try {
            $items = collect($this->returnItems)
                ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
                ->map(fn ($row) => [
                    'order_detail_id' => (int) $row['order_detail_id'],
                    'quantity' => (int) $row['quantity'],
                ])
                ->values()
                ->all();

            $returnService->createSalesReturn(
                (int) $this->returnOrderId,
                $items,
                $this->returnDate ?: Carbon::today()->toDateString(),
                (int) Auth::id(),
            );

            $this->showReturnModal = false;
            $this->returnItems = [];
            $this->dispatch('toast', type: 'success', message: "Sales return processed for {$this->returnOrderNo}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openPayment(int $orderId): void
    {
        $order = RetailOrder::findOrFail($orderId);
        $this->payOrderId = $order->id;
        $this->payOrderNo = (string) $order->order_no;
        $this->payCurrentDue = Money::round($order->due_amount ?? 0);
        $this->payAmount = $this->payCurrentDue;
        $this->payDiscount = '0';
        $this->payMethod = 'Cash';
        $this->payTrxId = '';
        $this->payDate = Carbon::today()->toDateString();
        $this->showPaymentModal = true;
    }

    public function submitPayment(RetailSalesService $salesService): void
    {
        try {
            $salesService->recordDirectPayment((int) $this->payOrderId, [
                'paid_amount' => $this->payAmount,
                'discount_amount' => $this->payDiscount,
                'payment_method' => $this->payMethod,
                'transaction_id' => $this->payTrxId ?: null,
                'date' => $this->payDate ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());

            $this->showPaymentModal = false;
            $this->dispatch('toast', type: 'success', message: "Payment recorded for {$this->payOrderNo}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openEdit(int $orderId): void
    {
        $order = RetailOrder::with(['details.product', 'payments'])->findOrFail($orderId);
        $this->editOrderId = $order->id;
        $this->editOrderNo = (string) $order->order_no;
        $this->editCustomerId = $order->customer_id;
        $this->editDate = $order->invoice_date
            ? Carbon::parse($order->invoice_date)->toDateString()
            : ($order->created_at?->toDateString() ?? Carbon::today()->toDateString());
        $this->editDiscountType = $order->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->editDiscountValue = $this->editDiscountType === 'percent'
            ? (string) ($order->discount_rate ?? 0)
            : Money::round($order->discount_amount ?? 0);
        $this->editDeliveryCharge = Money::round($order->delivery_charge ?? 0);
        $this->editPaidAmount = Money::round($order->paid_amount ?? 0);
        $this->editNote = (string) ($order->order_note ?? '');
        $this->editHasMultiplePayments = $order->payments->count() > 1;
        $this->editAddProductId = '';

        $this->editItems = $order->details->map(fn ($d) => [
            'id' => $d->id,
            'product_id' => (int) $d->product_id,
            'product_name' => (string) ($d->product?->product_name ?? "Product #{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'return_quantity' => (int) ($d->return_quantity ?? 0),
            'price' => Money::round($d->price ?? 0),
            'selling_price' => Money::round($d->selling_price ?? 0),
        ])->values()->all();

        $this->showEditModal = true;
    }

    public function addEditProduct(): void
    {
        if (! $this->editAddProductId) {
            return;
        }
        $p = RetailProduct::find((int) $this->editAddProductId);
        if (! $p) {
            return;
        }
        $this->editItems[] = [
            'id' => null,
            'product_id' => $p->id,
            'product_name' => (string) $p->product_name,
            'quantity' => 1,
            'return_quantity' => 0,
            'price' => Money::round($p->cost_price ?? 0),
            'selling_price' => Money::cmp($p->sell_price ?? 0, 0) > 0 ? Money::round($p->sell_price) : Money::round($p->cost_price ?? 0),
        ];
        $this->editAddProductId = '';
    }

    public function removeEditItem(int $idx): void
    {
        if (($this->editItems[$idx]['return_quantity'] ?? 0) > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot remove an item that already has sales returns.');

            return;
        }
        unset($this->editItems[$idx]);
        $this->editItems = array_values($this->editItems);
    }

    public function submitFullEdit(RetailSalesService $salesService): void
    {
        try {
            $payload = [
                'customer_id' => $this->editCustomerId,
                'invoice_date' => $this->editDate,
                'delivery_charge' => $this->editDeliveryCharge,
                'discount_type' => $this->editDiscountType,
                'discount_rate' => $this->editDiscountType === 'percent' ? $this->editDiscountValue : null,
                'discount_amount' => $this->editDiscountType === 'fixed' ? $this->editDiscountValue : null,
                'order_note' => $this->editNote ?: null,
                'items' => $this->editItems,
            ];
            if (! $this->editHasMultiplePayments) {
                $payload['paid_amount'] = $this->editPaidAmount;
            }

            $salesService->updateFullInvoice((int) $this->editOrderId, $payload, (int) Auth::id());
            $this->showEditModal = false;
            $this->dispatch('toast', type: 'success', message: "Invoice {$this->editOrderNo} updated!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModals(): void
    {
        $this->showViewModal = false;
        $this->showReturnModal = false;
        $this->showPaymentModal = false;
        $this->showEditModal = false;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'startDate', 'endDate']);
        $this->quickFilter = 'all';
        $this->resetPage();
    }

    public function render()
    {
        $query = RetailOrder::with(['customer', 'payments'])->orderByDesc('id');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('order_no', 'like', $term)
                    ->orWhere('order_note', 'like', $term)
                    ->orWhereHas('customer', fn ($cq) => $cq->where('customer_name', 'like', $term)->orWhere('mobile', 'like', $term));
            });
        }

        if ($this->startDate || $this->endDate) {
            DateFilter::applyInvoiceDateFilter($query, 'invoice_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.retail.invoice.retail-invoice-list', [
            'orders' => $query->paginate($this->perPage),
            'customers' => $this->showEditModal ? RetailCustomer::orderBy('customer_name')->get(['id', 'customer_name', 'mobile']) : collect(),
            'allProducts' => $this->showEditModal ? RetailProduct::orderBy('product_name')->get(['id', 'product_name', 'quantity']) : collect(),
        ]);
    }
}
