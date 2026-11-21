<?php

namespace App\Livewire\Retail\Report;

use App\Models\Retail\RetailProduct;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Low Stock Report')]
class RetailStockOutReport extends Component
{
    use WithPagination;

    public int $threshold = 10;

    public string $search = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingThreshold(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = RetailProduct::with(['brand', 'category', 'unit'])
            ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < ?', [$this->threshold])
            ->when(trim($this->search) !== '', fn ($q) => $q->where('product_name', 'like', '%'.trim($this->search).'%'))
            ->orderByRaw('CAST(COALESCE(quantity,0) AS SIGNED) ASC')
            ->paginate($this->perPage);

        return view('livewire.retail.report.retail-stock-out-report', compact('products'));
    }
}
