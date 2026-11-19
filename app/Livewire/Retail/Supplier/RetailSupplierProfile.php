<?php

namespace App\Livewire\Retail\Supplier;

use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchasePaymentDetail;
use App\Models\Retail\RetailPurchaseReturn;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailSupplierDueCollection;
use App\Services\Retail\RetailDueCollectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Supplier Profile')]
class RetailSupplierProfile extends Component
{
    public int $supplierId;

    public bool $showPayModal = false;

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $collection_type = 'all';

    public string $date = '';

    public function mount(int $id): void
    {
        $this->supplierId = $id;
        $this->date = Carbon::today()->toDateString();
    }

    public function openPayModal(RetailDueCollectionService $service): void
    {
        $bal = $service->supplierBalance($this->supplierId);
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPayment(RetailDueCollectionService $service): void
    {
        try {
            $service->paySupplierDue($this->supplierId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'transaction_id' => $this->transaction_id ?: null,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());

            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: 'Supplier payment recorded via FIFO!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showPayModal = false;
    }

    public function render(RetailDueCollectionService $service)
    {
        $supplier = RetailSupplier::findOrFail($this->supplierId);
        $balance = $service->supplierBalance($supplier->id);
        $purchases = RetailPurchase::where('supplier_id', $supplier->id)->orderByDesc('id')->get();
        $payments = RetailPurchasePaymentDetail::with('purchase')
            ->whereIn('purchases_id', $purchases->pluck('id'))
            ->orderByDesc('id')
            ->get();
        $prevPayments = RetailSupplierDueCollection::where('supplier_id', $supplier->id)
            ->whereRaw('CAST(COALESCE(purchase_payable_amount, 0) AS DECIMAL(15,2)) > 0')
            ->orderByDesc('id')
            ->get();
        $returns = RetailPurchaseReturn::with('product')->where('supplier_id', $supplier->id)->orderByDesc('id')->get();

        return view('livewire.retail.supplier.retail-supplier-profile', compact('supplier', 'balance', 'purchases', 'payments', 'prevPayments', 'returns'));
    }
}
