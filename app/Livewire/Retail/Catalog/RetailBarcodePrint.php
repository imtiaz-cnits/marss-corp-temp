<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailProduct;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Barcode Print')]
class RetailBarcodePrint extends Component
{
    public string $tab = 'product';

    public ?int $selectedProductId = null;

    public int $copies = 12;

    public bool $showPrice = true;

    public string $seqPrefix = 'MC';

    public int $seqStart = 1001;

    public int $seqCount = 12;

    /** @var list<array{code: string, name: string, price: ?string}> */
    public array $labels = [];

    public function mount(): void
    {
        if (request()->routeIs('retail.barcode')) {
            $this->tab = 'sequential';
        }
    }

    public function generateProductLabels(): void
    {
        $product = RetailProduct::find($this->selectedProductId);
        if (! $product) {
            $this->dispatch('toast', type: 'error', message: 'Select a product first.');

            return;
        }
        $code = is_array($product->product_code) ? ($product->product_code[0] ?? "P{$product->id}") : ($product->product_code ?: "P{$product->id}");
        $count = max(1, min(120, $this->copies));
        $this->labels = array_fill(0, $count, [
            'code' => (string) $code,
            'name' => (string) $product->product_name,
            'price' => $this->showPrice ? (string) ((float) $product->sell_price > 0 ? $product->sell_price : $product->cost_price) : null,
        ]);
    }

    public function generateSequentialLabels(): void
    {
        $count = max(1, min(120, $this->seqCount));
        $labels = [];
        for ($i = 0; $i < $count; $i++) {
            $code = trim($this->seqPrefix).($this->seqStart + $i);
            $labels[] = ['code' => $code, 'name' => 'MARSS CORPORATION', 'price' => null];
        }
        $this->labels = $labels;
    }

    public function render()
    {
        return view('livewire.retail.catalog.retail-barcode-print', [
            'products' => RetailProduct::orderBy('product_name')->get(['id', 'product_name', 'product_code', 'sell_price']),
        ]);
    }
}
