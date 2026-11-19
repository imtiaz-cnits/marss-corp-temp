<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatterySupplier;
use App\Services\Battery\BatteryDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Supplier Due List')]
class BatterySupplierDueList extends Component
{
    public string $search = '';

    public bool $showPayModal = false;

    public ?int $paySupplierId = null;

    public string $paySupplierName = '';

    public string $payTotalDue = '0.00';

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $collection_type = 'all';

    public string $date = '';

    public function openPay(int $id, BatteryDueCollectionService $service): void
    {
        $s = BatterySupplier::findOrFail($id);
        $bal = $service->supplierBalance($s->id);
        $this->paySupplierId = $s->id;
        $this->paySupplierName = (string) $s->name;
        $this->payTotalDue = $bal['total_due'];
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->date = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPay(BatteryDueCollectionService $service): void
    {
        try {
            $service->paySupplierDue((int) $this->paySupplierId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: "Paid Battery supplier '{$this->paySupplierName}'!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showPayModal = false;
    }

    public function render()
    {
        $suppliers = BatterySupplier::withSum('purchases', 'due_amount')
            ->when(trim($this->search) !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($s) => Money::cmp(Money::add($s->purchase_payable_amount ?? 0, $s->purchases_sum_due_amount ?? 0), 0) > 0)
            ->values();

        $grandDue = Money::sum($suppliers->map(fn ($s) => Money::add($s->purchase_payable_amount ?? 0, $s->purchases_sum_due_amount ?? 0)));

        return view('livewire.battery.supplier.battery-supplier-due-list', compact('suppliers', 'grandDue'));
    }
}
