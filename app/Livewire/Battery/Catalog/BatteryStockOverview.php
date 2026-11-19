<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryProduct;
use App\Services\Battery\BatteryStockService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Stock Overview')]
class BatteryStockOverview extends Component
{
    use WithPagination;

    public string $tab = 'all';

    public string $search = '';

    public bool $showAdjustModal = false;

    public ?int $adjustProductId = null;

    public string $adjustProductName = '';

    public int $adjustQuantity = 0;

    public string $adjustReason = '';

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['all', 'out', 'low', 'available'], true) ? $tab : 'all';
        $this->resetPage();
    }

    public function openAdjust(int $id): void
    {
        $p = BatteryProduct::findOrFail($id);
        $this->adjustProductId = $p->id;
        $this->adjustProductName = (string) $p->product_name;
        $this->adjustQuantity = (int) round((float) ($p->quantity ?? 0));
        $this->adjustReason = '';
        $this->showAdjustModal = true;
    }

    public function submitAdjust(BatteryStockService $service): void
    {
        try {
            $service->manualAdjust((int) $this->adjustProductId, (int) $this->adjustQuantity, $this->adjustReason ?: 'Manual adjustment');
            $this->showAdjustModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery stock updated!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showAdjustModal = false;
    }

    public function render(BatteryStockService $service)
    {
        $query = BatteryProduct::with(['brand', 'unit']);
        if ($this->tab === 'out') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) <= 0');
        } elseif ($this->tab === 'low') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) > 0 AND CAST(COALESCE(quantity, 0) AS SIGNED) < 10');
        } elseif ($this->tab === 'available') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) >= 10');
        }
        if (trim($this->search) !== '') {
            $query->where('product_name', 'like', '%'.trim($this->search).'%');
        }

        return view('livewire.battery.catalog.battery-stock-overview', [
            'summary' => $service->summaryMetrics(),
            'products' => $query->orderByRaw('CAST(COALESCE(quantity, 0) AS SIGNED) ASC')->paginate(20),
        ]);
    }
}
