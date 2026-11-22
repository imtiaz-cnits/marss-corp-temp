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

    public string $sort = 'category_asc';

    public int $perPage = 15;

    public bool $showAdjustModal = false;

    public ?int $adjustProductId = null;

    public string $adjustProductName = '';

    public int $adjustCurrentStock = 0;

    public string $adjustUnitName = 'Pcs';

    public int $adjustQuantity = 0;

    public string $adjustReason = 'Physical Inventory Recount';

    public bool $showImageModal = false;

    public ?string $viewingImageUrl = null;

    public ?string $viewingImageTitle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['all', 'out', 'low', 'available'], true) ? $tab : 'all';
        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $this->sort = in_array($sort, ['category_asc', 'name_asc', 'name_desc', 'stock_asc', 'stock_desc', 'default'], true) ? $sort : 'category_asc';
        $this->resetPage();
    }

    public function openAdjust(int $productId): void
    {
        $p = RetailProduct::with('unit')->findOrFail($productId);
        $this->adjustProductId = $p->id;
        $this->adjustProductName = (string) $p->product_name;
        $this->adjustCurrentStock = (int) round((float) ($p->quantity ?? 0));
        $this->adjustUnitName = (string) ($p->unit?->unit_name ?: 'Pcs');
        $this->adjustQuantity = $this->adjustCurrentStock;
        $this->adjustReason = 'Physical Inventory Recount';
        $this->showAdjustModal = true;
    }

    public function openImageModal(string $url, string $title = 'Product Preview'): void
    {
        $this->viewingImageUrl = $url;
        $this->viewingImageTitle = $title;
        $this->showImageModal = true;
    }

    public function submitAdjust(RetailStockService $stockService): void
    {
        try {
            $stockService->manualAdjust(
                (int) $this->adjustProductId,
                (int) $this->adjustQuantity,
                $this->adjustReason ?: 'Physical Inventory Recount',
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
        $this->showImageModal = false;
        $this->viewingImageUrl = null;
    }

    public function render(RetailStockService $stockService)
    {
        $query = RetailProduct::query()
            ->select('products.*')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->with(['brand', 'category', 'unit']);

        if ($this->tab === 'out') {
            $query->whereRaw('CAST(COALESCE(products.quantity, 0) AS SIGNED) <= 0');
        } elseif ($this->tab === 'low') {
            $query->whereRaw('CAST(COALESCE(products.quantity, 0) AS SIGNED) > 0 AND CAST(COALESCE(products.quantity, 0) AS SIGNED) <= 5');
        } elseif ($this->tab === 'available') {
            $query->whereRaw('CAST(COALESCE(products.quantity, 0) AS SIGNED) > 5');
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('products.product_name', 'like', $term)
                    ->orWhere('products.product_code', 'like', $term)
                    ->orWhere('categories.category_name', 'like', $term);
            });
        }

        match ($this->sort) {
            'name_asc' => $query->orderBy('products.product_name', 'asc'),
            'name_desc' => $query->orderBy('products.product_name', 'desc'),
            'stock_asc' => $query->orderByRaw('CAST(COALESCE(products.quantity, 0) AS SIGNED) ASC'),
            'stock_desc' => $query->orderByRaw('CAST(COALESCE(products.quantity, 0) AS SIGNED) DESC'),
            'default' => $query->orderByDesc('products.id'),
            default => $query->orderByRaw("COALESCE(categories.category_name, 'ZZZ') ASC")->orderBy('products.product_name', 'asc'),
        };

        $filteredAll = (clone $query)->get();
        $filteredTotalQty = (int) $filteredAll->sum(fn ($p) => (int) round((float) ($p->quantity ?? 0)));
        $filteredTotalCost = (float) $filteredAll->sum(fn ($p) => (float) ($p->cost_price ?? 0) * (float) ($p->quantity ?? 0));
        $filteredTotalSell = (float) $filteredAll->sum(fn ($p) => (float) ($p->sell_price ?? 0) * (float) ($p->quantity ?? 0));

        $exportRows = $filteredAll->map(function ($p) {
            $qty = (int) round((float) ($p->quantity ?? 0));
            $codes = $p->decodedCodes();

            return [
                'name' => (string) ($p->product_name ?: '-'),
                'barcode' => ! empty($codes) ? implode(', ', $codes) : 'N/A',
                'category' => (string) ($p->category?->category_name ?: 'N/A'),
                'qty' => $qty,
                'unit' => (string) ($p->unit?->unit_name ?: 'Pcs'),
                'cost' => (float) ($p->cost_price ?? 0),
                'sell' => (float) ($p->sell_price ?? 0),
                'status' => $qty <= 0 ? 'Out of Stock' : ($qty <= 5 ? 'Low Stock' : 'Available'),
            ];
        })->values()->all();

        return view('livewire.retail.catalog.retail-stock-overview', [
            'summary' => $stockService->summaryMetrics(),
            'products' => $query->paginate($this->perPage),
            'filteredTotalQty' => $filteredTotalQty,
            'filteredTotalCost' => $filteredTotalCost,
            'filteredTotalSell' => $filteredTotalSell,
            'exportRows' => $exportRows,
        ]);
    }
}
