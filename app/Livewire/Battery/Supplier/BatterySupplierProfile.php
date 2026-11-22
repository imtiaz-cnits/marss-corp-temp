<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatteryPurchasePaymentDetail;
use App\Models\Battery\BatteryPurchaseReturn;
use App\Models\Battery\BatterySupplier;
use App\Models\Battery\BatterySupplierDueCollection;
use App\Services\Battery\BatteryDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Supplier Profile')]
class BatterySupplierProfile extends Component
{
    public int $supplierId;

    public string $activeTab = 'purchases';

    public bool $showPayModal = false;

    public bool $showImageModal = false;

    public string $viewingImageUrl = '';

    public string $viewingImageTitle = '';

    public string $paid_amount = '';

    public string $discount_amount = '';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $collection_type = 'all';

    public string $date = '';

    public function mount(int $id): void
    {
        $this->supplierId = $id;
        $this->date = Carbon::today()->toDateString();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['purchases', 'returns', 'transactions'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function openPay(BatteryDueCollectionService $service): void
    {
        $bal = $service->supplierBalance($this->supplierId);
        $this->paid_amount = (float) $bal['total_due'] > 0 ? $bal['total_due'] : '';
        $this->discount_amount = '';
        $this->payment_method = 'Cash';
        $this->transaction_id = '';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function setCollectionType(string $type): void
    {
        if (in_array($type, ['all', 'previous', 'purchase'], true)) {
            $this->collection_type = $type;
        }
    }

    public function setPaymentMethod(string $method): void
    {
        $this->payment_method = $method;
    }

    public function submitPay(BatteryDueCollectionService $service): void
    {
        try {
            $service->paySupplierDue($this->supplierId, [
                'paid_amount' => $this->paid_amount !== '' ? $this->paid_amount : 0,
                'discount_amount' => $this->discount_amount !== '' ? $this->discount_amount : 0,
                'payment_method' => $this->payment_method ?: 'Cash',
                'transaction_id' => trim($this->transaction_id) !== '' ? trim($this->transaction_id) : null,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery supplier payment recorded successfully!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openImageModal(string $url, string $title = ''): void
    {
        $this->viewingImageUrl = $url;
        $this->viewingImageTitle = $title;
        $this->showImageModal = true;
    }

    public function closeModal(): void
    {
        $this->showPayModal = false;
        $this->showImageModal = false;
        $this->viewingImageUrl = '';
        $this->viewingImageTitle = '';
    }

    public function render(BatteryDueCollectionService $service)
    {
        $supplier = BatterySupplier::findOrFail($this->supplierId);
        $balance = $service->supplierBalance($supplier->id);
        $purchases = BatteryPurchase::with('paymentDetails')->where('supplier_id', $supplier->id)->orderByDesc('id')->get();
        $payments = BatteryPurchasePaymentDetail::with('purchase')
            ->whereIn('purchases_id', $purchases->pluck('id'))
            ->whereRaw('CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2)) > 0')
            ->orderByDesc('id')
            ->get();
        $prevPayments = BatterySupplierDueCollection::where('supplier_id', $supplier->id)
            ->whereRaw('CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2)) > 0')
            ->orderByDesc('id')
            ->get();
        $returns = BatteryPurchaseReturn::with(['product', 'purchase'])->where('supplier_id', $supplier->id)->orderByDesc('id')->get();

        $totalReturnsAdjusted = Money::sum($purchases->pluck('return_adjustment_amount'));
        $availableCredit = Money::maxZero(Money::sub($balance['total_returns'], $totalReturnsAdjusted));

        // Unified transaction history (zero FIFO duplicates since BatteryDueCollectionService splits Step 1 vs Step 2 cleanly)
        $transactions = collect();
        foreach ($payments as $pay) {
            $transactions->push([
                'sort_at' => $pay->created_at?->timestamp ?? 0,
                'date_formatted' => $pay->created_at ? $pay->created_at->format('d-m-Y h:i A') : ($pay->purchase_due_collection_date ? Carbon::parse($pay->purchase_due_collection_date)->format('d-m-Y') : 'N/A'),
                'ref' => $pay->purchase?->purchase_id ?: ('#PurID'.str_pad((string) $pay->purchases_id, 5, '0', STR_PAD_LEFT)),
                'paid_amount' => Money::round($pay->paid_amount ?? 0),
                'discount_amount' => Money::round($pay->discount_amount ?? 0),
                'payment_method' => $pay->payment_method ?: 'Cash',
                'transaction_id' => $pay->transaction_id ?: 'N/A',
                'status' => $pay->payment_status ?: 'Success',
            ]);
        }
        foreach ($prevPayments as $dc) {
            $transactions->push([
                'sort_at' => $dc->created_at?->timestamp ?? 0,
                'date_formatted' => $dc->created_at ? $dc->created_at->format('d-m-Y h:i A') : ($dc->payment_date ? Carbon::parse($dc->payment_date)->format('d-m-Y') : 'N/A'),
                'ref' => 'Due Collection',
                'paid_amount' => Money::round($dc->paid_amount ?? 0),
                'discount_amount' => Money::round($dc->discount_amount ?? 0),
                'payment_method' => $dc->payment_method ?: 'Cash',
                'transaction_id' => $dc->transaction_id ?: 'N/A',
                'status' => 'Paid',
            ]);
        }
        $transactions = $transactions->sortByDesc('sort_at')->values();

        return view('livewire.battery.supplier.battery-supplier-profile', compact(
            'supplier',
            'balance',
            'purchases',
            'payments',
            'prevPayments',
            'returns',
            'transactions',
            'totalReturnsAdjusted',
            'availableCredit'
        ));
    }
}
