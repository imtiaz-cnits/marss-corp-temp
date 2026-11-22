<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatterySubCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Sub-Categories')]
class BatterySubCategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public ?int $editingId = null;

    public string $name = '';

    public ?int $category_id = null;

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'category_id']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $sc = BatterySubCategory::findOrFail($id);
        $this->editingId = $sc->id;
        $this->name = (string) $sc->name;
        $this->category_id = $sc->category_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:150'], 'category_id' => ['required', 'integer', 'exists:battery_categories,id']]);
        BatterySubCategory::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'category_id' => $this->category_id,
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery sub-category saved!');
    }

    public function confirmDelete(int $id): void
    {
        $sc = BatterySubCategory::findOrFail($id);
        $this->deletingId = $sc->id;
        $this->deletingName = (string) $sc->name;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteAction(): void
    {
        if ($this->deletingId) {
            $this->delete($this->deletingId);
        }
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        BatterySubCategory::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function render()
    {
        return view('livewire.battery.catalog.battery-sub-category-list', [
            'subCategories' => BatterySubCategory::with('category')->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))->orderByDesc('id')->paginate(15),
            'categories' => BatteryCategory::orderBy('name')->get(),
        ]);
    }
}
