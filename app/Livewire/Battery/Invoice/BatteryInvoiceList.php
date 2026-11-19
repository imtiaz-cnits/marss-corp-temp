<?php

namespace App\Livewire\Battery\Invoice;

use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryProduct;
use App\Services\Battery\BatterySalesService;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Invoice List')]
class BatteryInvoiceList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public bool $showPaymentModal = false;

    public ?int $payOrderId = null;

    public string $payOrderNo = '';

    public string $payCurrentDue = '0.00';

    public string $payAmount = '0';

    public string $payDiscount = '0';

    public string $payMethod = 'Cash';

    public string $payDate = '';

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

    public array $editItems = [];

    public function openPayment(int $orderId): void
    {
        $o = BatteryOrder::findOrFail($orderId);
        $this->payOrderId = $o->id;
        $this->payOrderNo = (string) $o->order_no;
        $this->payCurrentDue = Money::round($o->due_amount ?? 0);
        $this->payAmount = $this->payCurrentDue;
        $this->payDiscount = '0';
        $this->payMethod = 'Cash';
        $this->payDate = Carbon::today()->toDateString();
        $this->showPaymentModal = true;
    }

    public function submitPayment(BatterySalesService $service): void
    {
        try {
            $service->recordDirectPayment((int) $this->payOrderId, [
                'paid_amount' => $this->payAmount,
                'discount_amount' => $this->payDiscount,
                'payment_method' => $this->payMethod,
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
        $o = BatteryOrder::with(['details.product', 'payments'])->findOrFail($orderId);
        $this->editOrderId = $o->id;
        $this->editOrderNo = (string) $o->order_no;
        $this->editCustomerId = $o->customer_id;
        $this->editDate = Carbon::parse($o->invoice_date ?? $o->created_at)->toDateString();
        $this->editDiscountType = $o->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->editDiscountValue = $this->editDiscountType === 'percent' ? (string) ($o->discount_rate ?? 0) : Money::round($o->discount_amount ?? 0);
        $this->editDeliveryCharge = Money::round($o->delivery_charge ?? 0);
        $this->editPaidAmount = Money::round($o->paid_amount ?? 0);
        $this->editNote = (string) ($o->order_note ?? '');
        $this->editHasMultiplePayments = $o->payments->count() > 1;
        $this->editItems = $o->details->map(fn ($d) => [
            'id' => $d->id,
            'product_id' => (int) $d->product_id,
            'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'return_quantity' => (int) ($d->return_quantity ?? 0),
            'price' => Money::round($d->price ?? 0),
            'selling_price' => Money::round($d->selling_price ?? 0),
        ])->values()->all();
        $this->showEditModal = true;
    }

    public function addEditProduct(): void
    {
        $p = BatteryProduct::find((int) $this->editAddProductId);
        if (! $p) {
            return;
        }
        $this->editItems[] = [
            'id' => null, 'product_id' => $p->id, 'product_name' => (string) $p->product_name,
            'quantity' => 1, 'return_quantity' => 0,
            'price' => Money::round($p->cost_price ?? 0),
            'selling_price' => Money::cmp($p->sell_price ?? 0, 0) > 0 ? Money::round($p->sell_price) : Money::round($p->cost_price ?? 0),
        ];
        $this->editAddProductId = '';
    }

    public function removeEditItem(int $idx): void
    {
        unset($this->editItems[$idx]);
        $this->editItems = array_values($this->editItems);
    }

    public function submitFullEdit(BatterySalesService $service): void
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
            $service->updateFullInvoice((int) $this->editOrderId, $payload, (int) Auth::id());
            $this->showEditModal = false;
            $this->dispatch('toast', type: 'success', message: "Battery invoice {$this->editOrderNo} updated!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModals(): void
    {
        $this->showPaymentModal = false;
        $this->showEditModal = false;
    }

    public function render()
    {
        $query = BatteryOrder::with('customer')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('order_no', 'like', $t)->orWhereHas('customer', fn ($cq) => $cq->where('customer_name', 'like', $t)));
        }
        if ($this->statusFilter === 'paid') {
            $query->whereRaw('CAST(COALESCE(due_amount,0) AS DECIMAL(15,2)) <= 0');
        } elseif ($this->statusFilter === 'due') {
            $query->whereRaw('CAST(COALESCE(due_amount,0) AS DECIMAL(15,2)) > 0');
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyInvoiceDateFilter($query, 'invoice_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.invoice.battery-invoice-list', [
            'orders' => $query->paginate($this->perPage),
            'customers' => $this->showEditModal ? BatteryCustomer::orderBy('customer_name')->get(['id', 'customer_name']) : collect(),
            'allProducts' => $this->showEditModal ? BatteryProduct::orderBy('product_name')->get(['id', 'product_name', 'quantity']) : collect(),
        ]);
    }
}
