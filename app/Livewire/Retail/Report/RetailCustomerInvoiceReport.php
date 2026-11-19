<?php

namespace App\Livewire\Retail\Report;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Support\DateFilter;
use App\Support\Money;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Customer Invoice Report')]
class RetailCustomerInvoiceReport extends Component
{
    public string $customerId = '';

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        $query = RetailOrder::with('customer')->orderByDesc('id');
        if ($this->customerId !== '') {
            $query->where('customer_id', (int) $this->customerId);
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyInvoiceDateFilter($query, 'invoice_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        $orders = $query->limit(250)->get();

        return view('livewire.retail.report.retail-customer-invoice-report', [
            'customers' => RetailCustomer::orderBy('customer_name')->get(['id', 'customer_name', 'mobile']),
            'orders' => $orders,
            'totalSales' => Money::sum($orders->pluck('sub_total')),
            'totalPaid' => Money::sum($orders->pluck('paid_amount')),
            'totalDue' => Money::sum($orders->pluck('due_amount')),
        ]);
    }
}
