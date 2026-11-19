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

    public bool $showPrice = true;

    public string $seqPrefix = 'MCB';

    public int $seqStart = 1001;

    public int $seqCount = 12;

    public array $labels = [];

    public function generateProductLabels(): void
    {
        $p = BatteryProduct::find($this->selectedProductId);
        if (! $p) {
            $this->dispatch('toast', type: 'error', message: 'Select a Battery product first.');

            return;
        }
        $code = $p->primaryCode() ?: "BP{$p->id}";
        $this->labels = array_fill(0, max(1, min(120, $this->copies)), [
            'code' => $code,
            'name' => (string) $p->product_name,
            'price' => $this->showPrice ? (string) ((float) $p->sell_price > 0 ? $p->sell_price : $p->cost_price) : null,
        ]);
    }

    public function generateSequentialLabels(): void
    {
        $labels = [];
        for ($i = 0; $i < max(1, min(120, $this->seqCount)); $i++) {
            $labels[] = ['code' => trim($this->seqPrefix).($this->seqStart + $i), 'name' => 'MARSS BATTERY', 'price' => null];
        }
        $this->labels = $labels;
    }

    public function render()
    {
        return view('livewire.battery.catalog.battery-barcode-print', [
            'products' => BatteryProduct::orderBy('product_name')->get(),
        ]);
    }
}
