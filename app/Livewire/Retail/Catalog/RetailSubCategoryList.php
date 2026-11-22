<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailSubCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Sub-Categories')]
class RetailSubCategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public ?int $editingId = null;

    public string $sub_category_name = '';

    public ?int $category_id = null;

    public string $status = 'active';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'sub_category_name', 'category_id']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $sc = RetailSubCategory::findOrFail($id);
        $this->editingId = $sc->id;
        $this->sub_category_name = (string) $sc->sub_category_name;
        $this->category_id = $sc->category_id;
        $this->status = (string) ($sc->status ?: 'active');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'sub_category_name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        RetailSubCategory::updateOrCreate(['id' => $this->editingId], [
            'sub_category_name' => trim($this->sub_category_name),
            'category_id' => $this->category_id,
            'status' => $this->status,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Sub-Category saved!');
    }

    public function confirmDelete(int $id): void
    {
        $sc = RetailSubCategory::findOrFail($id);
        $this->deletingId = $sc->id;
        $this->deletingName = (string) $sc->sub_category_name;
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
        RetailSubCategory::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Sub-Category deleted.');
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
        return view('livewire.retail.catalog.retail-sub-category-list', [
            'subCategories' => RetailSubCategory::with('category')
                ->when($this->search !== '', fn ($q) => $q->where('sub_category_name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate(15),
            'categories' => RetailCategory::orderBy('category_name')->get(),
        ]);
    }
}
