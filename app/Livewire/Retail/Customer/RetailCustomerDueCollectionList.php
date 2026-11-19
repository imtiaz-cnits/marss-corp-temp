<?php

namespace App\Livewire\Retail\Customer;

use App\Models\Retail\RetailCustomerPaymentDetail;
use App\Support\DateFilter;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Customer Due Collections')]
class RetailCustomerDueCollectionList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        $query = RetailCustomerPaymentDetail::with('customer')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->whereHas('customer', fn ($q) => $q->where('customer_name', 'like', $t)->orWhere('mobile', 'like', $t));
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'due_collection_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.retail.customer.retail-customer-due-collection-list', [
            'rows' => $query->paginate(20),
        ]);
    }
}
