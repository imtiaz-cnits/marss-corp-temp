<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailSubCategory;
use App\Models\Retail\RetailUnit;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Product List')]
class RetailProductList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $brandFilter = '';

    public string $categoryFilter = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $product_name = '';

    public string $product_code = '';

    public string $quantity = '0';

    public string $cost_price = '0';

    public string $sell_price = '0';

    public string $door_side = '';

    public ?int $brand_id = null;

    public ?int $category_id = null;

    public ?int $sub_category_id = null;

    public ?int $unit_id = null;

    public string $status = 'active';

    public $image;

    public ?string $existingImage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'product_name', 'product_code', 'door_side', 'brand_id', 'category_id', 'sub_category_id', 'image', 'existingImage']);
        $this->quantity = '0';
        $this->cost_price = '0';
        $this->sell_price = '0';
        $this->status = 'active';
        $this->unit_id = RetailUnit::query()->value('id');
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = RetailProduct::findOrFail($id);
        $this->editingId = $p->id;
        $this->product_name = (string) $p->product_name;
        $this->product_code = is_array($p->product_code) ? implode(', ', $p->product_code) : (string) ($p->product_code ?? '');
        $this->quantity = (string) ($p->quantity ?? '0');
        $this->cost_price = Money::round($p->cost_price ?? 0);
        $this->sell_price = Money::round($p->sell_price ?? 0);
        $this->door_side = (string) ($p->door_side ?? '');
        $this->brand_id = $p->brand_id;
        $this->category_id = $p->category_id;
        $this->sub_category_id = $p->sub_category_id;
        $this->unit_id = $p->unit_id;
        $this->status = (string) ($p->status ?: 'active');
        $this->existingImage = $p->img_url;
        $this->image = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'product_name' => ['required', 'string', 'max:220'],
            'product_code' => ['nullable', 'string', 'max:190'],
            'quantity' => ['required', 'numeric'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:3072'],
        ]);

        $code = trim($this->product_code);
        if ($code !== '') {
            $dup = RetailProduct::query()
                ->where(function ($q) use ($code) {
                    $q->where('product_code', $code)
                        ->orWhere('product_code', json_encode([$code]))
                        ->orWhere('product_code', 'like', '%"'.$code.'"%');
                })
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();
            if ($dup) {
                $this->addError('product_code', 'This barcode/product code already exists in Retail catalog.');

                return;
            }
        }

        $imgPath = $this->existingImage;
        if ($this->image) {
            $imgPath = $uploads->store($this->image, 'products', $this->existingImage);
        }

        $brandId = $this->brand_id ?: RetailBrand::query()->value('id');
        $categoryId = $this->category_id ?: RetailCategory::query()->value('id');

        $payload = [
            'product_name' => trim($this->product_name),
            'product_code' => [$code ?: ('RET-'.time())],
            'quantity' => (string) (int) round((float) $this->quantity),
            'cost_price' => Money::round($this->cost_price),
            'sell_price' => Money::round($this->sell_price),
            'door_side' => $this->door_side ?: null,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'sub_category_id' => $this->sub_category_id,
            'unit_id' => $this->unit_id ?: RetailUnit::query()->value('id'),
            'status' => $this->status,
            'img_url' => $imgPath,
            'user_id' => Auth::id(),
        ];

        $product = RetailProduct::updateOrCreate(['id' => $this->editingId], $payload);
        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Product', "Saved Retail product '{$product->product_name}'.", ['product_id' => $product->id]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: "Product '{$product->product_name}' saved!");
    }

    public function delete(int $id, SafeImageUploadService $uploads): void
    {
        $p = RetailProduct::findOrFail($id);
        if ($p->orderDetails()->exists() || $p->purchaseOrderDetails()->exists()) {
            $this->dispatch('toast', type: 'error', message: "Cannot delete '{$p->product_name}' because historical sales or purchases reference it.");

            return;
        }
        $name = $p->product_name;
        $uploads->delete($p->img_url);
        $p->delete();
        ActivityLogger::log('Delete', 'Retail / Product', "Deleted Retail product '{$name}'.");
        $this->dispatch('toast', type: 'success', message: "Product '{$name}' deleted.");
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = RetailProduct::with(['brand', 'category', 'subCategory', 'unit'])->orderByDesc('id');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('product_name', 'like', $term)->orWhere('product_code', 'like', $term)->orWhere('door_side', 'like', $term));
        }
        if ($this->brandFilter !== '') {
            $query->where('brand_id', (int) $this->brandFilter);
        }
        if ($this->categoryFilter !== '') {
            $query->where('category_id', (int) $this->categoryFilter);
        }

        return view('livewire.retail.catalog.retail-product-list', [
            'products' => $query->paginate($this->perPage),
            'brands' => RetailBrand::orderBy('name')->get(),
            'categories' => RetailCategory::orderBy('category_name')->get(),
            'subCategories' => $this->category_id
                ? RetailSubCategory::where('category_id', $this->category_id)->orderBy('sub_category_name')->get()
                : RetailSubCategory::orderBy('sub_category_name')->get(),
            'units' => RetailUnit::orderBy('unit_name')->get(),
        ]);
    }
}
