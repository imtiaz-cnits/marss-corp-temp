<?php

namespace App\Livewire\Retail\Customer;

use App\Models\Retail\RetailCustomer;
use App\Services\Retail\RetailDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Customer Due List')]
class RetailCustomerDueList extends Component
{
    public string $search = '';

    public bool $showCollectModal = false;

    public ?int $collectCustomerId = null;

    public string $collectCustomerName = '';

    public string $collectTotalDue = '0.00';

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $collection_type = 'all';

    public string $date = '';

    public function openCollect(int $customerId, RetailDueCollectionService $service): void
    {
        $c = RetailCustomer::findOrFail($customerId);
        $bal = $service->customerBalance($c->id);
        $this->collectCustomerId = $c->id;
        $this->collectCustomerName = (string) $c->customer_name;
        $this->collectTotalDue = $bal['total_due'];
        $this->paid_amount = $bal['total_due'];
        $this->discount_amount = '0';
        $this->payment_method = 'Cash';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->showCollectModal = true;
    }

    public function submitCollect(RetailDueCollectionService $service): void
    {
        try {
            $service->collectCustomerDue((int) $this->collectCustomerId, [
                'paid_amount' => $this->paid_amount,
                'discount_amount' => $this->discount_amount,
                'payment_method' => $this->payment_method,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showCollectModal = false;
            $this->dispatch('toast', type: 'success', message: "Due collected from '{$this->collectCustomerName}'!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showCollectModal = false;
    }

    public function render()
    {
        $customers = RetailCustomer::withSum('orders', 'due_amount')
            ->when(trim($this->search) !== '', function ($q) {
                $t = '%'.trim($this->search).'%';
                $q->where(fn ($sub) => $sub->where('customer_name', 'like', $t)->orWhere('mobile', 'like', $t));
            })
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($c) => Money::cmp(Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0), 0) > 0)
            ->values();

        $grandDue = Money::sum($customers->map(fn ($c) => Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0)));

        return view('livewire.retail.customer.retail-customer-due-list', compact('customers', 'grandDue'));
    }
}
