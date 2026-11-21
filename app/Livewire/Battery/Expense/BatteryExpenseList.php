<?php

namespace App\Livewire\Battery\Expense;

use App\Models\Battery\BatteryExpense;
use App\Models\Battery\BatteryExpenseType;
use App\Models\Shared\User;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Expenses')]
class BatteryExpenseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $expense_type_id = null;

    public ?int $staff_id = null;

    public string $expense_amount = '';

    public string $expense_details = '';

    public string $date = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'expense_type_id', 'staff_id', 'expense_amount', 'expense_details']);
        $this->date = Carbon::today()->toDateString();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = BatteryExpense::findOrFail($id);
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
            'expense_type_id' => ['required', 'integer', 'exists:battery_expense_types,id'],
            'expense_amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
        ]);
        BatteryExpense::updateOrCreate(['id' => $this->editingId], [
            'expense_type_id' => $this->expense_type_id,
            'staff_id' => $this->staff_id,
            'expense_amount' => Money::round($this->expense_amount),
            'expense_details' => trim($this->expense_details),
            'date' => $this->date,
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery expense saved!');
    }

    public function delete(int $id): void
    {
        BatteryExpense::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = BatteryExpense::with(['expenseType', 'staff'])->orderByDesc('id');
        if (trim($this->search) !== '') {
            $query->where('expense_details', 'like', '%'.trim($this->search).'%');
        }
        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.expense.battery-expense-list', [
            'expenses' => $query->paginate(20),
            'types' => BatteryExpenseType::orderBy('type_name')->get(),
            'staffMembers' => User::visible()->orderBy('name')->get(['id', 'name']),
            'totalFiltered' => Money::round((clone $query)->sum('expense_amount')),
        ]);
    }
}
