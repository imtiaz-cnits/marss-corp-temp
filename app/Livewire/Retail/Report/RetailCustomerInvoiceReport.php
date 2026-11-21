<?php

namespace App\Livewire\Retail\Report;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Support\DateFilter;
use App\Support\Money;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Customer Invoice Report')]
class RetailCustomerInvoiceReport extends Component
{
    use WithPagination;

    public string $customerId = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public function updatingCustomerId(): void
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
        $query = RetailOrder::with('customer')->orderByDesc('id');
        if ($this->customerId !== '') {
            $query->where('customer_id', (int) $this->customerId);
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyInvoiceDateFilter($query, 'invoice_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        $totalSales = Money::sum((clone $query)->pluck('sub_total'));
        $totalPaid = Money::sum((clone $query)->pluck('paid_amount'));
        $totalDue = Money::sum((clone $query)->pluck('due_amount'));
        $orders = $query->paginate($this->perPage);

        return view('livewire.retail.report.retail-customer-invoice-report', [
            'customers' => RetailCustomer::orderBy('customer_name')->get(['id', 'customer_name', 'mobile']),
            'orders' => $orders,
            'totalSales' => $totalSales,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
        ]);
    }
}
