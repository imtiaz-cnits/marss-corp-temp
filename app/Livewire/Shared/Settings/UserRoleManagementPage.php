<?php

namespace App\Livewire\Shared\Settings;

use App\Models\Shared\User;
use App\Services\Shared\ActivityLogger;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User List & Roles')]
class UserRoleManagementPage extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $password = '';

    public string $role = 'staff';

    public string $status = 'approved';

    /** @var array<string, bool> */
    public array $permissions = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(string $value): void
    {
        $this->permissions = PermissionRegistry::roleDefaults($value);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'email', 'mobile', 'password']);
        $this->role = 'staff';
        $this->status = 'approved';
        $this->permissions = PermissionRegistry::roleDefaults('staff');
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->mobile = (string) ($user->mobile ?? '');
        $this->password = '';
        $this->role = strtolower(trim((string) $user->role));
        $this->status = strtolower(trim((string) $user->status)) ?: 'approved';
        $this->permissions = PermissionRegistry::effectiveFor($user);
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function toggleParentPermission(string $parent): void
    {
        $next = ! (bool) ($this->permissions[$parent] ?? false);
        $this->permissions[$parent] = $next;

        foreach (array_keys(PermissionRegistry::GROUPS[$parent]['children'] ?? []) as $child) {
            $this->permissions[$child] = $next;
        }
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function createUser(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager', 'staff', 'cashier', 'accountant'])],
            'status' => ['required', Rule::in(['approved', 'pending', 'rejected'])],
        ]);

        if ($this->role === 'super_admin' && ! Auth::user()?->isSuperAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'Only Super Admin can assign the Super Admin role.');

            return;
        }

        $user = User::create([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'mobile' => trim($this->mobile),
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'status' => $this->status,
            'permissions' => $this->normalizePermissions(),
        ]);

        ActivityLogger::log('Create', 'Users & Roles', "Created user '{$user->name}' ({$user->role}, {$user->status}).", ['target_user_id' => $user->id]);
        $this->showCreateModal = false;
        $this->dispatch('toast', type: 'success', message: "User '{$user->name}' created successfully!");
    }

    public function updateUser(): void
    {
        $user = User::findOrFail($this->editingId);

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager', 'staff', 'cashier', 'accountant'])],
            'status' => ['required', Rule::in(['approved', 'pending', 'rejected'])],
        ]);

        if ($this->role === 'super_admin' && ! Auth::user()?->isSuperAdmin() && ! $user->isSuperAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'Only Super Admin can promote to Super Admin.');

            return;
        }

        $user->name = trim($this->name);
        $user->email = strtolower(trim($this->email));
        $user->mobile = trim($this->mobile);
        $user->role = $this->role;
        $user->status = $this->status;
        $user->permissions = $this->normalizePermissions();
        if (trim($this->password) !== '') {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        ActivityLogger::log('Update', 'Users & Roles', "Updated role/permissions for '{$user->name}' ({$user->role}, {$user->status}).", ['target_user_id' => $user->id]);
        $this->showEditModal = false;
        $this->dispatch('toast', type: 'success', message: "User '{$user->name}' updated!");
    }

    public function deleteUser(int $id): void
    {
        $target = User::findOrFail($id);
        $actor = Auth::user();

        if ($target->id === $actor?->id) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete your own active account.');

            return;
        }

        if ($target->isAdmin() && ! $actor?->isSuperAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'Only Super Admin can delete an Admin account.');

            return;
        }

        $name = $target->name;
        $target->delete();

        ActivityLogger::log('Delete', 'Users & Roles', "Deleted user '{$name}'.", ['deleted_user_id' => $id]);
        $this->dispatch('toast', type: 'success', message: "User '{$name}' deleted.");
    }

    /**
     * @return array<string, bool>
     */
    private function normalizePermissions(): array
    {
        $clean = [];
        foreach (PermissionRegistry::allKeys() as $key) {
            $clean[$key] = (bool) ($this->permissions[$key] ?? false);
        }

        return $clean;
    }

    public function render()
    {
        $query = User::query()->orderByDesc('id');
        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term)->orWhere('role', 'like', $term));
        }

        return view('livewire.shared.settings.user-role-management-page', [
            'users' => $query->paginate($this->perPage),
            'stats' => [
                'total' => User::count(),
                'admins' => User::whereIn('role', ['admin', 'super_admin', 'Admin', 'Super Admin'])->count(),
                'approved' => User::where('status', 'approved')->count(),
                'pending' => User::where('status', '!=', 'approved')->count(),
            ],
            'permissionGroups' => PermissionRegistry::GROUPS,
        ]);
    }
}
