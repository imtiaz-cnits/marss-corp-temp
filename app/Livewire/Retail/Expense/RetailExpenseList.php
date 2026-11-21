<?php

namespace App\Livewire\Retail\Expense;

use App\Models\Retail\RetailExpense;
use App\Models\Retail\RetailExpenseType;
use App\Models\Shared\User;
use App\Services\Shared\ActivityLogger;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Expenses')]
class RetailExpenseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $expense_type_id = null;

    public ?int $staff_id = null;

    public string $expense_amount = '';

    public string $expense_details = '';

    public string $date = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'expense_type_id', 'staff_id', 'expense_amount', 'expense_details']);
        $this->date = Carbon::today()->toDateString();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = RetailExpense::findOrFail($id);
        $this->editingId = $e->id;
        $this->expense_type_id = $e->expense_type_id;
        $this->staff_id = $e->staff_id;
        $this->expense_amount = Money::round($e->expense_amount);
        $this->expense_details = (string) ($e->expense_details ?? '');
        $this->date = DateFilter::parseDateString($e->date) ?? Carbon::today()->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'expense_amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
        ]);

        $exp = RetailExpense::updateOrCreate(['id' => $this->editingId], [
            'expense_type_id' => $this->expense_type_id,
            'staff_id' => $this->staff_id,
            'expense_amount' => Money::round($this->expense_amount),
            'expense_details' => trim($this->expense_details),
            'date' => $this->date,
            'user_id' => Auth::id(),
        ]);

        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Expense', "Recorded Retail expense ৳{$exp->expense_amount}.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Expense saved!');
    }

    public function delete(int $id): void
    {
        RetailExpense::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Expense deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = RetailExpense::with(['expenseType', 'staff'])->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('expense_details', 'like', $t)->orWhereHas('expenseType', fn ($eq) => $eq->where('type_name', 'like', $t)));
        }
        if ($this->typeFilter !== '') {
            $query->where('expense_type_id', (int) $this->typeFilter);
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        $totalFiltered = Money::round((clone $query)->sum('expense_amount'));

        return view('livewire.retail.expense.retail-expense-list', [
            'expenses' => $query->paginate(20),
            'types' => RetailExpenseType::orderBy('type_name')->get(),
            'staffMembers' => User::visible()->orderBy('name')->get(['id', 'name', 'role']),
            'totalFiltered' => $totalFiltered,
        ]);
    }
}
