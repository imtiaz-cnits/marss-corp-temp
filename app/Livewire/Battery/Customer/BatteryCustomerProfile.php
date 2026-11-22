<?php

namespace App\Livewire\Battery\Customer;

use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryOrderPaymentDetail;
use App\Models\Battery\BatteryProductReturn;
use App\Services\Battery\BatteryDueCollectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Customer Profile')]
class BatteryCustomerProfile extends Component
{
    public int $customerId;

    public bool $showCollectModal = false;

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $collection_type = 'all';

    public string $date = '';

    public function mount(int $id): void
    {
        $this->customerId = $id;
        $this->date = Carbon::today()->toDateString();
    }

    public function openCollect(BatteryDueCollectionService $service): void
    {
        $bal = $service->customerBalance($this->customerId);
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->date = Carbon::today()->toDateString();
        $this->showCollectModal = true;
    }

    public function submitCollect(BatteryDueCollectionService $service): void
    {
        try {
            $service->collectCustomerDue($this->customerId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showCollectModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery customer due collected!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showCollectModal = false;
    }

    public function render(BatteryDueCollectionService $service)
    {
        $customer = BatteryCustomer::findOrFail($this->customerId);
        $balance = $service->customerBalance($customer->id);
        $orders = BatteryOrder::where('customer_id', $customer->id)->orderByDesc('id')->get();
        $payments = BatteryOrderPaymentDetail::with('order')->whereIn('order_id', $orders->pluck('id'))->orderByDesc('id')->get();
        $returns = BatteryProductReturn::with(['product', 'order'])->where('customer_id', $customer->id)->orderByDesc('id')->get();

        return view('livewire.battery.customer.battery-customer-profile', compact('customer', 'balance', 'orders', 'payments', 'returns'));
    }
}
