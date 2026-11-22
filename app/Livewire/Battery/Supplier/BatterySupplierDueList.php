<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatterySupplier;
use App\Services\Battery\BatteryDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Supplier Due List')]
class BatterySupplierDueList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showPayModal = false;

    public bool $showImageModal = false;

    public string $viewingImageUrl = '';

    public string $viewingImageTitle = '';

    public ?int $paySupplierId = null;

    public string $paySupplierName = '';

    public string $paySupplierPrevDue = '0.00';

    public string $payPurchasePrevDue = '0.00';

    public string $payTotalDue = '0.00';

    public string $paid_amount = '';

    public string $discount_amount = '';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $collection_type = 'all';

    public string $date = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openPay(int $id, BatteryDueCollectionService $service): void
    {
        $s = BatterySupplier::findOrFail($id);
        $bal = $service->supplierBalance($s->id);
        $this->paySupplierId = $s->id;
        $this->paySupplierName = (string) $s->name;
        $this->paySupplierPrevDue = $bal['previous_due'];
        $this->payPurchasePrevDue = $bal['purchases_due'];
        $this->payTotalDue = $bal['total_due'];
        $this->paid_amount = '';
        $this->discount_amount = '';
        $this->payment_method = 'Cash';
        $this->transaction_id = '';
        $this->collection_type = 'all';
        $this->date = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function selectPaymentMethod(string $method): void
    {
        $this->payment_method = $method;
        if (strtolower($method) === 'cash') {
            $this->transaction_id = '';
        }
    }

    public function submitPay(BatteryDueCollectionService $service): void
    {
        $pay = (float) ($this->paid_amount !== '' ? $this->paid_amount : 0);
        $disc = (float) ($this->discount_amount !== '' ? $this->discount_amount : 0);
        $totalDue = (float) $this->payTotalDue;

        if ($pay <= 0 && $disc <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Please enter the pay amount.');

            return;
        }
        if (($pay + $disc) > ($totalDue + 0.009)) {
            $this->dispatch('toast', type: 'error', message: 'The amount you paid is more than the Total Previous Due Amount.');

            return;
        }

        try {
            $service->paySupplierDue((int) $this->paySupplierId, [
                'paid_amount' => $pay,
                'discount_amount' => $disc,
                'payment_method' => $this->payment_method ?: 'Cash',
                'transaction_id' => $this->transaction_id !== '' ? trim($this->transaction_id) : null,
                'collection_type' => $this->collection_type,
                'date' => $this->date ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: "Paid Battery supplier '{$this->paySupplierName}'!");
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

    public function render()
    {
        $filtered = BatterySupplier::withSum('purchases', 'due_amount')
            ->when(trim($this->search) !== '', function ($q) {
                $t = '%'.trim($this->search).'%';
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', $t)
                    ->orWhere('mobile', 'like', $t)
                    ->orWhere('company', 'like', $t)
                    ->orWhere('supplier_id', 'like', $t));
            })
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($s) => Money::cmp(Money::add($s->purchase_payable_amount ?? 0, $s->purchases_sum_due_amount ?? 0), 0) > 0)
            ->values();

        $totalPreviousDue = Money::sum($filtered->map(fn ($s) => Money::maxZero($s->purchase_payable_amount ?? 0)));
        $totalInvoiceDue = Money::sum($filtered->map(fn ($s) => Money::maxZero($s->purchases_sum_due_amount ?? 0)));
        $grandDue = Money::add($totalPreviousDue, $totalInvoiceDue);

        $page = $this->getPage();
        $slice = $filtered->slice(($page - 1) * $this->perPage, $this->perPage)->values();
        $suppliers = new LengthAwarePaginator(
            $slice,
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.battery.supplier.battery-supplier-due-list', compact(
            'suppliers',
            'totalPreviousDue',
            'totalInvoiceDue',
            'grandDue'
        ));
    }
}
