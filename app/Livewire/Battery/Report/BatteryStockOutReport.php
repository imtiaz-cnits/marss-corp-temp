<?php

namespace App\Livewire\Battery\Report;

use App\Models\Battery\BatteryProduct;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Low Stock Report')]
class BatteryStockOutReport extends Component
{
    use WithPagination;

    public int $threshold = 10;

    public int $perPage = 15;

    public function updatingThreshold(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = BatteryProduct::with(['brand', 'category', 'unit'])
            ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < ?', [$this->threshold])
            ->orderByRaw('CAST(COALESCE(quantity,0) AS SIGNED) ASC')
            ->paginate($this->perPage);

        return view('livewire.battery.report.battery-stock-out-report', compact('products'));
    }
}
