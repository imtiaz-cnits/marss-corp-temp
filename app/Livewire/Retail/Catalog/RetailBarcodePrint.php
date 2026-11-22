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

    public bool $showName = true;

    public bool $showCode = true;

    public bool $showPrice = true;

    public bool $showBarcode = true;

    public string $startBarcode = '';

    public string $endBarcode = '';

    public string $seqPrefix = 'MC';

    public int $seqStart = 1001;

    public int $seqCount = 12;

    /** @var list<array{code: string, name: string, price: ?string, show_name: bool, show_code: bool, show_price: bool, show_barcode: bool}> */
    public array $labels = [];

    public function mount(): void
    {
        if (request()->routeIs('retail.barcode')) {
            $this->tab = 'sequential';
        }
    }

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
        $p = RetailProduct::find($productId);
        if ($p) {
            $qty = max(1, min(1000, (int) round((float) ($p->quantity ?? 0)) ?: 1));
            $this->copies = $qty;
        }
    }

    public function increaseCopies(): void
    {
        $this->copies = min(1000, max(0, $this->copies) + 1);
    }

    public function decreaseCopies(): void
    {
        $this->copies = max(1, $this->copies - 1);
    }

    public function generateProductLabels(): void
    {
        $product = RetailProduct::find($this->selectedProductId);
        if (! $product) {
            $this->dispatch('toast', type: 'error', message: 'Select a product first.');

            return;
        }
        $code = $product->primaryCode() ?: "P{$product->id}";
        $count = max(1, min(1000, $this->copies));
        $priceVal = (float) $product->sell_price > 0 ? $product->sell_price : $product->cost_price;

        $this->labels = array_fill(0, $count, [
            'code' => (string) $code,
            'name' => (string) $product->product_name,
            'price' => $this->showPrice ? (string) $priceVal : null,
            'show_name' => $this->showName,
            'show_code' => $this->showCode,
            'show_price' => $this->showPrice,
            'show_barcode' => $this->showBarcode,
        ]);
    }

    public function generateSequentialLabels(): void
    {
        $startStr = trim($this->startBarcode);
        $endStr = trim($this->endBarcode);

        if ($startStr !== '' && $endStr !== '') {
            $startPrefix = '';
            $endPrefix = '';
            if (str_contains($startStr, '-')) {
                $lastDash = strrpos($startStr, '-');
                $startPrefix = substr($startStr, 0, $lastDash);
                $startNumStr = substr($startStr, $lastDash + 1);
            } else {
                $startNumStr = $startStr;
            }

            if (str_contains($endStr, '-')) {
                $lastDash = strrpos($endStr, '-');
                $endPrefix = substr($endStr, 0, $lastDash);
                $endNumStr = substr($endStr, $lastDash + 1);
            } else {
                $endNumStr = $endStr;
            }

            if (! is_numeric($startNumStr) || ! is_numeric($endNumStr)) {
                $this->dispatch('toast', type: 'error', message: 'Invalid barcode number. Please enter a valid number.');

                return;
            }

            $startNum = (int) $startNumStr;
            $endNum = (int) $endNumStr;

            if ($startPrefix !== $endPrefix && $startPrefix !== '' && $endPrefix !== '') {
                $this->dispatch('toast', type: 'error', message: 'The barcode prefix should be the same for both start and end.');

                return;
            }
            if ($startNum > $endNum) {
                $this->dispatch('toast', type: 'error', message: 'Start barcode must be less than or equal to the end barcode.');

                return;
            }

            $prefix = $startPrefix ?: $endPrefix;
            $labels = [];
            for ($i = $startNum; $i <= $endNum; $i++) {
                $code = ($prefix !== '' ? $prefix.'-' : '').$i;
                $labels[] = [
                    'code' => $code,
                    'name' => '',
                    'price' => null,
                    'show_name' => false,
                    'show_code' => true,
                    'show_price' => false,
                    'show_barcode' => true,
                ];
            }
            $this->labels = $labels;

            return;
        }

        if ($startStr !== '' || $endStr !== '') {
            $this->dispatch('toast', type: 'error', message: 'Please enter both start and end barcode values.');

            return;
        }

        $count = max(1, $this->seqCount);
        $labels = [];
        for ($i = 0; $i < $count; $i++) {
            $code = trim($this->seqPrefix).($this->seqStart + $i);
            $labels[] = [
                'code' => $code,
                'name' => 'MARSS CORPORATION',
                'price' => null,
                'show_name' => false,
                'show_code' => true,
                'show_price' => false,
                'show_barcode' => true,
            ];
        }
        $this->labels = $labels;
    }

    public function resetBarcodes(): void
    {
        $this->reset(['selectedProductId', 'startBarcode', 'endBarcode', 'labels']);
        $this->copies = 12;
        $this->showName = true;
        $this->showCode = true;
        $this->showPrice = true;
        $this->showBarcode = true;
    }

    public function render()
    {
        $products = RetailProduct::orderBy('product_name')->get(['id', 'product_name', 'product_code', 'quantity', 'cost_price', 'sell_price']);
        $selectedProduct = $this->selectedProductId ? $products->firstWhere('id', $this->selectedProductId) : null;

        return view('livewire.retail.catalog.retail-barcode-print', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
        ]);
    }
}
