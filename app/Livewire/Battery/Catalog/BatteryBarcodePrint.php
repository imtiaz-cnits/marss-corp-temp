<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryProduct;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Barcode Print')]
class BatteryBarcodePrint extends Component
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

    public string $seqPrefix = 'MCB';

    public int $seqStart = 1001;

    public int $seqCount = 12;

    /** @var list<array{code: string, name: string, price: ?string, show_name: bool, show_code: bool, show_price: bool, show_barcode: bool}> */
    public array $labels = [];

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
        $p = BatteryProduct::find($productId);
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
        $p = BatteryProduct::find($this->selectedProductId);
        if (! $p) {
            $this->dispatch('toast', type: 'error', message: 'Select a Battery product first.');

            return;
        }
        $code = $p->primaryCode() ?: "BP{$p->id}";
        $count = max(1, min(1000, $this->copies));
        $priceVal = (float) $p->sell_price > 0 ? $p->sell_price : $p->cost_price;

        $this->labels = array_fill(0, $count, [
            'code' => (string) $code,
            'name' => (string) $p->product_name,
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

        $labels = [];
        for ($i = 0; $i < max(1, $this->seqCount); $i++) {
            $labels[] = [
                'code' => trim($this->seqPrefix).($this->seqStart + $i),
                'name' => 'MARSS BATTERY',
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
        $products = BatteryProduct::orderBy('product_name')->get();
        $selectedProduct = $this->selectedProductId ? $products->firstWhere('id', $this->selectedProductId) : null;

        return view('livewire.battery.catalog.battery-barcode-print', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
        ]);
    }
}
