<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryCategory;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Battery Categories')]
class BatteryCategoryList extends Component
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
        $c = BatteryCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = (string) $c->name;
        $this->existingImage = $c->img_url;
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate(['name' => ['required', 'string', 'max:150'], 'image' => ['nullable', 'image', 'max:2048']]);
        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'battery-categories', $this->existingImage);
        }
        BatteryCategory::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'img_url' => $path,
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery category saved!');
    }

    public function delete(int $id, SafeImageUploadService $uploads): void
    {
        $c = BatteryCategory::findOrFail($id);
        $uploads->delete($c->img_url);
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Battery category deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.battery.catalog.battery-category-list', [
            'categories' => BatteryCategory::when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))->orderByDesc('id')->paginate(15),
        ]);
    }
}
