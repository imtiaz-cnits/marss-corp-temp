<?php

namespace App\Livewire\Retail\Expense;

use App\Models\Retail\RetailExpense;
use App\Models\Shared\User;
use App\Support\Money;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Staff Salary Profile')]
class RetailStaffProfile extends Component
{
    public string $staffId = '';

    public function render()
    {
        $staff = User::visible()->orderBy('name')->get(['id', 'name', 'role', 'mobile', 'email']);
        $expenses = RetailExpense::with(['staff', 'expenseType'])
            ->whereNotNull('staff_id')
            ->when($this->staffId !== '', fn ($q) => $q->where('staff_id', (int) $this->staffId))
            ->orderByDesc('id')
            ->get();

        return view('livewire.retail.expense.retail-staff-profile', [
            'staff' => $staff,
            'expenses' => $expenses,
            'totalSalary' => Money::sum($expenses->pluck('expense_amount')),
        ]);
    }
}
