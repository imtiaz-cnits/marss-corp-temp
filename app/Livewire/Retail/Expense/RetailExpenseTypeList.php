<?php

namespace App\Livewire\Retail\Expense;

use App\Models\Retail\RetailExpenseType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Expense Types')]
class RetailExpenseTypeList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public ?int $editingId = null;

    public string $type_name = '';

    public string $status = 'active';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'type_name']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $t = RetailExpenseType::findOrFail($id);
        $this->editingId = $t->id;
        $this->type_name = (string) $t->type_name;
        $this->status = (string) ($t->status ?: 'active');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['type_name' => ['required', 'string', 'max:150']]);

        RetailExpenseType::updateOrCreate(['id' => $this->editingId], [
            'type_name' => trim($this->type_name),
            'status' => $this->status,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Expense type saved!');
    }

    public function confirmDelete(int $id): void
    {
        $t = RetailExpenseType::findOrFail($id);
        if (stripos((string) $t->type_name, 'salary') !== false) {
            $this->dispatch('toast', type: 'error', message: 'Salary expense types are protected and cannot be deleted.');

            return;
        }
        $this->deletingId = $t->id;
        $this->deletingLabel = "expense type '{$t->type_name}'";
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if ($this->deletingId) {
            $this->delete($this->deletingId);
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->deletingLabel = '';
        }
    }

    public function delete(int $id): void
    {
        $t = RetailExpenseType::findOrFail($id);
        if (stripos((string) $t->type_name, 'salary') !== false) {
            $this->dispatch('toast', type: 'error', message: 'Salary expense types are protected and cannot be deleted.');

            return;
        }
        $t->delete();
        $this->dispatch('toast', type: 'success', message: 'Expense type deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.retail.expense.retail-expense-type-list', [
            'types' => RetailExpenseType::query()
                ->when($this->search !== '', fn ($q) => $q->where('type_name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate($this->perPage),
        ]);
    }
}
