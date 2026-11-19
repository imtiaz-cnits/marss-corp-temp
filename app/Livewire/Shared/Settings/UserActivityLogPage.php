<?php

namespace App\Livewire\Shared\Settings;

use App\Models\Shared\UserActivityLog;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Activity Log')]
class UserActivityLogPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $actionFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 25;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'actionFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function clearAllLogs(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        UserActivityLog::query()->delete();
        $this->dispatch('toast', type: 'success', message: 'Activity logs cleared.');
    }

    public function render()
    {
        $query = UserActivityLog::query()->orderByDesc('id');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('user_name', 'like', $term)
                ->orWhere('module', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('ip_address', 'like', $term));
        }

        if ($this->actionFilter !== '') {
            $query->where('action', $this->actionFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return view('livewire.shared.settings.user-activity-log-page', [
            'logs' => $query->paginate($this->perPage),
            'actions' => UserActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
