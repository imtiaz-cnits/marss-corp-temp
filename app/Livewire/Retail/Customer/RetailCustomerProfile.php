<?php

namespace App\Livewire\Retail\Customer;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailOrderPaymentDetail;
use App\Models\Retail\RetailProductReturn;
use App\Services\Retail\RetailDueCollectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Customer Profile')]
class RetailCustomerProfile extends Component
{
    public int $customerId;

    public bool $showCollectModal = false;

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $collection_type = 'all';

    public string $date = '';

    public function mount(int $id): void
    {
        $this->customerId = $id;
        $this->date = Carbon::today()->toDateString();
    }

    public function openCollect(RetailDueCollectionService $service): void
    {
        $bal = $service->customerBalance($this->customerId);
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->showCollectModal = true;
    }

    public function submitCollect(RetailDueCollectionService $service): void
    {
        try {
            $service->collectCustomerDue($this->customerId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'transaction_id' => $this->transaction_id ?: null,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showCollectModal = false;
            $this->dispatch('toast', type: 'success', message: 'Customer due collected via FIFO!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showCollectModal = false;
    }

    public function render(RetailDueCollectionService $service)
    {
        $customer = RetailCustomer::findOrFail($this->customerId);
        $balance = $service->customerBalance($customer->id);
        $orders = RetailOrder::where('customer_id', $customer->id)->orderByDesc('id')->get();
        $payments = RetailOrderPaymentDetail::with('order')->whereIn('order_id', $orders->pluck('id'))->orderByDesc('id')->get();
        $returns = RetailProductReturn::with('product')->where('customer_id', $customer->id)->orderByDesc('id')->get();

        return view('livewire.retail.customer.retail-customer-profile', compact('customer', 'balance', 'orders', 'payments', 'returns'));
    }
}
