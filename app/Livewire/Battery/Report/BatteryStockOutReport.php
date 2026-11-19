<?php

namespace App\Livewire\Battery\Report;

use App\Models\Battery\BatteryProduct;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Low Stock Report')]
class BatteryStockOutReport extends Component
{
    public int $threshold = 10;

    public function render()
    {
        $products = BatteryProduct::with(['brand', 'category', 'unit'])
            ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < ?', [$this->threshold])
            ->orderByRaw('CAST(COALESCE(quantity,0) AS SIGNED) ASC')
            ->get();

        return view('livewire.battery.report.battery-stock-out-report', compact('products'));
    }
}
