<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatteryPurchasePaymentDetail;
use App\Models\Battery\BatteryPurchaseReturn;
use App\Models\Battery\BatterySupplier;
use App\Services\Battery\BatteryDueCollectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Supplier Profile')]
class BatterySupplierProfile extends Component
{
    public int $supplierId;

    public bool $showPayModal = false;

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $collection_type = 'all';

    public string $date = '';

    public function mount(int $id): void
    {
        $this->supplierId = $id;
        $this->date = Carbon::today()->toDateString();
    }

    public function openPay(BatteryDueCollectionService $service): void
    {
        $bal = $service->supplierBalance($this->supplierId);
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->date = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPay(BatteryDueCollectionService $service): void
    {
        try {
            $service->paySupplierDue($this->supplierId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery supplier due paid via FIFO!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showPayModal = false;
    }

    public function render(BatteryDueCollectionService $service)
    {
        $supplier = BatterySupplier::findOrFail($this->supplierId);
        $balance = $service->supplierBalance($supplier->id);
        $purchases = BatteryPurchase::where('supplier_id', $supplier->id)->orderByDesc('id')->get();
        $payments = BatteryPurchasePaymentDetail::with('purchase')->whereIn('purchases_id', $purchases->pluck('id'))->orderByDesc('id')->get();
        $returns = BatteryPurchaseReturn::with('product')->where('supplier_id', $supplier->id)->orderByDesc('id')->get();

        return view('livewire.battery.supplier.battery-supplier-profile', compact('supplier', 'balance', 'purchases', 'payments', 'returns'));
    }
}
