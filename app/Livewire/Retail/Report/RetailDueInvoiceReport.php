<?php

namespace App\Livewire\Retail\Report;

use App\Models\Retail\RetailOrder;
use App\Support\DateFilter;
use App\Support\Money;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Due Invoice Report')]
class RetailDueInvoiceReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = RetailOrder::with('customer')
            ->whereRaw('CAST(COALESCE(due_amount,0) AS DECIMAL(15,2)) > 0')
            ->orderByDesc('id');

        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('order_no', 'like', $t)->orWhereHas('customer', fn ($cq) => $cq->where('customer_name', 'like', $t)->orWhere('mobile', 'like', $t)));
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyInvoiceDateFilter($query, 'invoice_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        $totalDue = Money::sum((clone $query)->pluck('due_amount'));
        $orders = $query->paginate($this->perPage);

        return view('livewire.retail.report.retail-due-invoice-report', [
            'orders' => $orders,
            'totalDue' => $totalDue,
        ]);
    }
}
