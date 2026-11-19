<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatterySupplierDueCollection;
use App\Support\DateFilter;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Supplier Due Collection History')]
class BatterySupplierDueCollectionList extends Component
{
    use WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        $query = BatterySupplierDueCollection::with('supplier')->orderByDesc('id');
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'payment_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.supplier.battery-supplier-due-collection-list', [
            'rows' => $query->paginate(20),
        ]);
    }
}
