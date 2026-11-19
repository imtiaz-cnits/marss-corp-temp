<?php

namespace App\Livewire\Retail\Supplier;

use App\Models\Retail\RetailSupplierDueCollection;
use App\Support\DateFilter;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Supplier Due Collections')]
class RetailSupplierDueCollectionList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        $query = RetailSupplierDueCollection::with('supplier')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->whereHas('supplier', fn ($q) => $q->where('name', 'like', $t)->orWhere('mobile', 'like', $t));
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'due_collection_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.retail.supplier.retail-supplier-due-collection-list', [
            'rows' => $query->paginate(20),
        ]);
    }
}
