<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailProduct;
use App\Services\Retail\RetailStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Stock Overview')]
class RetailStockOverview extends Component
{
    use WithPagination;

    public string $tab = 'all';

    public string $search = '';

    public int $perPage = 25;

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

    public function openAdjust(int $productId): void
    {
        $p = RetailProduct::findOrFail($productId);
        $this->adjustProductId = $p->id;
        $this->adjustProductName = (string) $p->product_name;
        $this->adjustQuantity = (int) round((float) ($p->quantity ?? 0));
        $this->adjustReason = '';
        $this->showAdjustModal = true;
    }

    public function submitAdjust(RetailStockService $stockService): void
    {
        try {
            $stockService->manualAdjust(
                (int) $this->adjustProductId,
                (int) $this->adjustQuantity,
                $this->adjustReason ?: 'Manual stock adjustment',
                (int) Auth::id(),
            );
            $this->showAdjustModal = false;
            $this->dispatch('toast', type: 'success', message: "Stock updated for '{$this->adjustProductName}'!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showAdjustModal = false;
    }

    public function render(RetailStockService $stockService)
    {
        $query = RetailProduct::with(['brand', 'category', 'unit']);

        if ($this->tab === 'out') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) <= 0');
        } elseif ($this->tab === 'low') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) > 0 AND CAST(COALESCE(quantity, 0) AS SIGNED) < 10');
        } elseif ($this->tab === 'available') {
            $query->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) >= 10');
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('product_name', 'like', $term)->orWhere('product_code', 'like', $term));
        }

        return view('livewire.retail.catalog.retail-stock-overview', [
            'summary' => $stockService->summaryMetrics(),
            'products' => $query->orderByRaw('CAST(COALESCE(quantity, 0) AS SIGNED) ASC')->paginate($this->perPage),
        ]);
    }
}
