<?php

namespace App\Livewire\Battery\Customer;

use App\Models\Battery\BatteryCustomerPaymentDetail;
use App\Support\DateFilter;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Customer Due Collections')]
class BatteryCustomerDueCollectionList extends Component
{
    use WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        $query = BatteryCustomerPaymentDetail::with('customer')->orderByDesc('id');
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'due_collection_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.customer.battery-customer-due-collection-list', [
            'rows' => $query->paginate(20),
        ]);
    }
}
