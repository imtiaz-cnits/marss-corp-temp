<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatterySubCategory;
use App\Models\Battery\BatteryUnit;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Battery Products')]
class BatteryProductList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $brandFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $product_name = '';

    public string $product_code = '';

    public string $quantity = '0';

    public string $cost_price = '0';

    public string $sell_price = '0';

    public ?int $brand_id = null;

    public ?int $category_id = null;

    public ?int $sub_category_id = null;

    public ?int $unit_id = null;

    public string $status = 'active';

    public $image;

    public ?string $existingImage = null;

    public function openCreate(): void
    {
        $this->reset(['editingId', 'product_name', 'product_code', 'brand_id', 'category_id', 'sub_category_id', 'image', 'existingImage']);
        $this->quantity = '0';
        $this->cost_price = '0';
        $this->sell_price = '0';
        $this->status = 'active';
        $this->unit_id = BatteryUnit::query()->value('id');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = BatteryProduct::findOrFail($id);
        $this->editingId = $p->id;
        $this->product_name = (string) $p->product_name;
        $this->product_code = implode(', ', $p->decodedCodes());
        $this->quantity = (string) ($p->quantity ?? '0');
        $this->cost_price = Money::round($p->cost_price ?? 0);
        $this->sell_price = Money::round($p->sell_price ?? 0);
        $this->brand_id = $p->brand_id;
        $this->category_id = $p->category_id;
        $this->sub_category_id = $p->sub_category_id;
        $this->unit_id = $p->unit_id;
        $this->status = (string) ($p->status ?: 'active');
        $this->existingImage = $p->img_url;
        $this->image = null;
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'product_name' => ['required', 'string', 'max:220'],
            'quantity' => ['required', 'numeric'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:3072'],
        ]);

        $codes = array_values(array_filter(array_map('trim', explode(',', $this->product_code))));
        if (empty($codes)) {
            $codes = ['BAT-'.time()];
        }

        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'battery-products', $this->existingImage);
        }

        $brandId = $this->brand_id ?: (BatteryBrand::query()->value('id') ?? BatteryBrand::create(['name' => 'General', 'user_id' => Auth::id()])->id);
        $categoryId = $this->category_id ?: (BatteryCategory::query()->value('id') ?? BatteryCategory::create(['name' => 'General', 'user_id' => Auth::id()])->id);

        $product = BatteryProduct::updateOrCreate(['id' => $this->editingId], [
            'product_name' => trim($this->product_name),
            'product_code' => $codes,
            'quantity' => (string) (int) round((float) $this->quantity),
            'cost_price' => Money::round($this->cost_price),
            'sell_price' => Money::round($this->sell_price),
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'sub_category_id' => $this->sub_category_id,
            'unit_id' => $this->unit_id ?: BatteryUnit::query()->value('id'),
            'status' => $this->status,
            'img_url' => $path,
            'user_id' => Auth::id(),
        ]);

        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Battery / Product', "Saved Battery product '{$product->product_name}'.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: "Battery product '{$product->product_name}' saved!");
    }

    public function delete(int $id, SafeImageUploadService $uploads): void
    {
        $p = BatteryProduct::findOrFail($id);
        if ($p->orderDetails()->exists() || $p->purchaseOrderDetails()->exists()) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete a Battery product referenced by invoices or purchases.');

            return;
        }
        $uploads->delete($p->img_url);
        $p->delete();
        $this->dispatch('toast', type: 'success', message: 'Battery product deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = BatteryProduct::with(['brand', 'category', 'unit'])->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('product_name', 'like', $t)->orWhere('product_code', 'like', $t));
        }
        if ($this->brandFilter !== '') {
            $query->where('brand_id', (int) $this->brandFilter);
        }

        return view('livewire.battery.catalog.battery-product-list', [
            'products' => $query->paginate(15),
            'brands' => BatteryBrand::orderBy('name')->get(),
            'categories' => BatteryCategory::orderBy('name')->get(),
            'subCategories' => $this->category_id ? BatterySubCategory::where('category_id', $this->category_id)->orderBy('name')->get() : BatterySubCategory::orderBy('name')->get(),
            'units' => BatteryUnit::orderBy('unit_name')->get(),
        ]);
    }
}
