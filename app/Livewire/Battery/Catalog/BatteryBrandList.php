<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryBrand;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Battery Brands')]
class BatteryBrandList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public $image;

    public ?string $existingImage = null;

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'image', 'existingImage']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = BatteryBrand::findOrFail($id);
        $this->editingId = $b->id;
        $this->name = (string) $b->name;
        $this->existingImage = $b->img_url;
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate(['name' => ['required', 'string', 'max:150'], 'image' => ['nullable', 'image', 'max:2048']]);
        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'battery-brands', $this->existingImage);
        }
        BatteryBrand::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'img_url' => $path,
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery brand saved!');
    }

    public function delete(int $id, SafeImageUploadService $uploads): void
    {
        $b = BatteryBrand::findOrFail($id);
        $uploads->delete($b->img_url);
        $b->delete();
        $this->dispatch('toast', type: 'success', message: 'Battery brand deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.battery.catalog.battery-brand-list', [
            'brands' => BatteryBrand::when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))->orderByDesc('id')->paginate(15),
        ]);
    }
}
