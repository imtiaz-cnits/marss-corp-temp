<?php

namespace App\Livewire\Retail\Customer;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailCustomerPaymentDetail;
use App\Services\Retail\RetailDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customer Due List')]
class RetailCustomerDueList extends Component
{
    use WithPagination;

    public string $activeTab = 'due-list';

    public string $search = '';

    public int $perPage = 15;

    // Collect Customer Due Modal
    public bool $showCollectModal = false;

    public ?int $collectCustomerId = null;

    public string $collectCustomerCode = '';

    public string $collectCustomerName = '';

    public string $collectPreviousDue = '0.00';

    public string $collectOrderDue = '0.00';

    public string $collectTotalDue = '0.00';

    public string $paid_amount = '0';

    public string $discount_amount = '0';

    public string $payment_method = 'Cash';

    public string $collection_type = 'all';

    public string $date = '';

    public string $transaction_id = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab === 'collection-list' ? 'collection-list' : 'due-list';
        $this->resetPage();
    }

    public function openCollect(int $customerId, RetailDueCollectionService $service): void
    {
        $c = RetailCustomer::findOrFail($customerId);
        $bal = $service->customerBalance($c->id);
        $this->collectCustomerId = $c->id;
        $this->collectCustomerCode = (string) ($c->customer_id ?: ('CUST-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT)));
        $this->collectCustomerName = (string) $c->customer_name;
        $this->collectPreviousDue = Money::round($bal['previous_due'] ?? 0);
        $this->collectOrderDue = Money::round($bal['orders_due'] ?? 0);
        $this->collectTotalDue = Money::round($bal['total_due'] ?? 0);
        $this->paid_amount = $this->collectTotalDue;
        $this->discount_amount = '0';
        $this->payment_method = 'Cash';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->transaction_id = '';
        $this->showCollectModal = true;
    }

    public function submitCollect(RetailDueCollectionService $service): void
    {
        try {
            $service->collectCustomerDue((int) $this->collectCustomerId, [
                'paid_amount' => $this->paid_amount ?: '0',
                'discount_amount' => $this->discount_amount ?: '0',
                'payment_method' => $this->payment_method ?: 'Cash',
                'collection_type' => $this->collection_type ?: 'all',
                'date' => $this->date ?: Carbon::today()->toDateString(),
                'transaction_id' => trim($this->transaction_id) ?: null,
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
        // Tab 1: Customer Due List (filtered)
        $allDueCustomers = RetailCustomer::withSum('orders', 'due_amount')
            ->when(trim($this->search) !== '', function ($q) {
                $t = '%' . trim($this->search) . '%';
                $q->where(fn ($sub) => $sub
                    ->where('customer_name', 'like', $t)
                    ->orWhere('mobile', 'like', $t)
                    ->orWhere('customer_id', 'like', $t)
                );
            })
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($c) => Money::cmp(Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0), 0) > 0)
            ->values();

        $dueListCount = $allDueCustomers->count();
        $totalPreviousDue = Money::sum($allDueCustomers->pluck('previous_due_amount'));
        $totalOrderDue = Money::sum($allDueCustomers->pluck('orders_sum_due_amount'));
        $grandDue = Money::sum($allDueCustomers->map(fn ($c) => Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0)));

        // Manual pagination for filtered due customers
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $dueSlice = $allDueCustomers->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $duePaginator = new LengthAwarePaginator(
            $dueSlice,
            $dueListCount,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Tab 2: Due Collection List
        $colQuery = RetailCustomerPaymentDetail::with('customer')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%' . trim($this->search) . '%';
            $colQuery->where(fn ($q) => $q
                ->whereHas('customer', fn ($cq) => $cq->where('customer_name', 'like', $t)->orWhere('customer_id', 'like', $t)->orWhere('mobile', 'like', $t))
                ->orWhere('due_collection_date', 'like', $t)
                ->orWhere('payment_method', 'like', $t)
            );
        }

        $collectionListCount = (clone $colQuery)->count();
        $colTotals = (clone $colQuery)->reorder()->selectRaw('
            COALESCE(SUM(CAST(COALESCE(previous_due_amount, 0) AS DECIMAL(15,2))), 0) as sum_prev,
            COALESCE(SUM(CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2))), 0) as sum_paid,
            COALESCE(SUM(CAST(COALESCE(due_amount, 0) AS DECIMAL(15,2))), 0) as sum_due
        ')->first();

        $collections = $this->activeTab === 'collection-list'
            ? $colQuery->paginate($this->perPage)
            : new LengthAwarePaginator([], $collectionListCount, $this->perPage, 1);

        // Live modal calculations
        $targetDue = match ($this->collection_type) {
            'previous' => $this->collectPreviousDue,
            'invoice' => $this->collectOrderDue,
            default => $this->collectTotalDue,
        };
        $paidClean = Money::maxZero($this->paid_amount ?: 0);
        $discClean = Money::maxZero($this->discount_amount ?: 0);
        $remainingDue = Money::maxZero(Money::sub($targetDue, Money::add($paidClean, $discClean)));
        $collectStatus = Money::cmp($remainingDue, 0) <= 0
            ? 'Fully Paid'
            : (Money::cmp(Money::add($paidClean, $discClean), 0) > 0 ? 'Partial Paid' : 'Pending');

        return view('livewire.retail.customer.retail-customer-due-list', [
            'customers' => $duePaginator,
            'collections' => $collections,
            'dueListCount' => $dueListCount,
            'collectionListCount' => $collectionListCount,
            'totalPreviousDue' => $totalPreviousDue,
            'totalOrderDue' => $totalOrderDue,
            'grandDue' => $grandDue,
            'subTotalPayable' => Money::round($colTotals->sum_prev ?? 0),
            'subTotalPaid' => Money::round($colTotals->sum_paid ?? 0),
            'subTotalDue' => Money::round($colTotals->sum_due ?? 0),
            'remainingDue' => $remainingDue,
            'collectStatus' => $collectStatus,
        ]);
    }
}
