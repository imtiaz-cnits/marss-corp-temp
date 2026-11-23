<?php

namespace App\Livewire\Battery\Report;

use App\Models\Battery\BatteryProduct;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Low Stock & Stock-Out Report')]
class BatteryStockOutReport extends Component
{
    use WithPagination;

    public int $threshold = 10;

    public string $search = '';

    public int $perPage = 15;

    public bool $showImageModal = false;

    public ?string $modalImage = null;

    public ?string $modalTitle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingThreshold(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openImageModal(?string $url, string $title = 'Product Preview'): void
    {
        $this->modalImage = $url;
        $this->modalTitle = $title;
        $this->showImageModal = true;
    }

    public function closeModal(): void
    {
        $this->showImageModal = false;
        $this->modalImage = null;
        $this->modalTitle = null;
    }

    public function render()
    {
        $metrics = BatteryProduct::whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < ?', [$this->threshold])
            ->selectRaw("
                SUM(CASE WHEN CAST(COALESCE(quantity,0) AS SIGNED) > 0 THEN 1 ELSE 0 END) as low_count,
                SUM(CASE WHEN CAST(COALESCE(quantity,0) AS SIGNED) <= 0 THEN 1 ELSE 0 END) as out_count,
                SUM(CASE WHEN CAST(COALESCE(quantity,0) AS SIGNED) > 0 THEN CAST(COALESCE(quantity,0) AS SIGNED) * COALESCE(cost_price, 0) ELSE 0 END) as total_cost,
                SUM(CASE WHEN CAST(COALESCE(quantity,0) AS SIGNED) > 0 THEN CAST(COALESCE(quantity,0) AS SIGNED) * COALESCE(sell_price, 0) ELSE 0 END) as total_sell
            ")->first();

        $query = BatteryProduct::with(['brand', 'category', 'unit'])
            ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < ?', [$this->threshold]);

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                    ->orWhere('product_code', 'like', "%{$s}%");
            });
        }

        $products = $query->orderByRaw('CAST(COALESCE(quantity,0) AS SIGNED) ASC')
            ->paginate($this->perPage);

        return view('livewire.battery.report.battery-stock-out-report', compact('products', 'metrics'));
    }
}
