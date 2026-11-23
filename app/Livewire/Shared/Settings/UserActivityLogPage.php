<?php

namespace App\Livewire\Shared\Settings;

use App\Models\Shared\User;
use App\Models\Shared\UserActivityLog;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Activity Logs & History')]
class UserActivityLogPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $userFilter = '';

    public string $actionFilter = '';

    public string $moduleFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 15;

    public function mount(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedModuleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'userFilter', 'actionFilter', 'moduleFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function clearAllLogs(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        UserActivityLog::query()->delete();
        $this->dispatch('toast', type: 'success', message: 'Activity logs cleared successfully.');
    }

    public function render()
    {
        // 4 Stat Cards
        $totalLogs = UserActivityLog::count();
        $loginSessionsCount = UserActivityLog::whereIn('action', ['Login', 'Logout', 'Session', 'LOGIN', 'LOGOUT'])->count();
        $modificationsCount = UserActivityLog::whereIn('action', ['Create', 'Update', 'Delete', 'Payment', 'Purchase', 'Sale', 'CREATE', 'UPDATE', 'DELETE'])->count();
        $registeredUsersCount = User::count();

        // Dropdown lists
        $usersList = User::orderBy('name')->get(['id', 'name', 'email']);
        $actionsList = UserActivityLog::select('action')->distinct()->whereNotNull('action')->orderBy('action')->pluck('action');
        $modulesList = UserActivityLog::select('module')->distinct()->whereNotNull('module')->orderBy('module')->pluck('module');

        $query = UserActivityLog::query()->orderByDesc('id');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('user_name', 'like', $term)
                    ->orWhere('user_email', 'like', $term)
                    ->orWhere('module', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('ip_address', 'like', $term);
            });
        }

        if ($this->userFilter !== '') {
            $query->where(function ($q) {
                $q->where('user_id', $this->userFilter)
                    ->orWhere('user_name', $this->userFilter);
            });
        }

        if ($this->actionFilter !== '') {
            $query->where('action', $this->actionFilter);
        }

        if ($this->moduleFilter !== '') {
            $query->where('module', $this->moduleFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return view('livewire.shared.settings.user-activity-log-page', [
            'logs' => $query->paginate($this->perPage),
            'totalLogs' => $totalLogs,
            'loginSessionsCount' => $loginSessionsCount,
            'modificationsCount' => $modificationsCount,
            'registeredUsersCount' => $registeredUsersCount,
            'usersList' => $usersList,
            'actionsList' => $actionsList,
            'modulesList' => $modulesList,
        ]);
    }
}
