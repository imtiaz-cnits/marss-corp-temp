<?php

namespace App\Livewire\Shared\Settings;

use App\Models\Shared\User;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('User List & Roles')]
class UserRoleManagementPage extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingId = null;

    public bool $editingIsDeveloper = false;

    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $password = '';

    public string $role = 'staff';

    public string $status = 'approved';

    public $avatar = null;

    public string $currentAvatarUrl = '';

    /** @var array<string, bool> */
    public array $permissions = [];

    public function mount(): void
    {
        if (! Auth::user()?->isDeveloper()) {
            abort(403, 'Only authorized developer can manage users.');
        }
    }

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
        if (! Auth::user()?->isDeveloper()) {
            abort(403);
        }

        $this->reset(['editingId', 'editingIsDeveloper', 'name', 'email', 'mobile', 'password', 'avatar', 'currentAvatarUrl']);
        $this->role = 'staff';
        $this->status = 'approved';
        $this->permissions = PermissionRegistry::roleDefaults('staff');
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        if (! Auth::user()?->isDeveloper()) {
            abort(403);
        }

        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->editingIsDeveloper = $user->isDeveloper();
        $this->name = (string) $user->name;
        $this->email = (string) ($user->email ?? '');
        $this->mobile = (string) ($user->mobile ?? '');
        $this->password = '';
        $this->avatar = null;
        $this->currentAvatarUrl = $user->avatarUrl();
        $this->role = $user->isDeveloper() ? 'super_admin' : strtolower(trim((string) $user->role));
        $this->status = $user->isDeveloper() ? 'approved' : (strtolower(trim((string) $user->status)) ?: 'approved');
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

    public function createUser(SafeImageUploadService $uploads): void
    {
        if (! Auth::user()?->isDeveloper()) {
            abort(403);
        }

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager', 'staff', 'cashier', 'accountant'])],
            'status' => ['required', Rule::in(['approved', 'pending', 'rejected'])],
            'avatar' => ['nullable', 'image', 'max:3072'],
        ]);

        if (strtolower(trim($this->email)) === User::DEVELOPER_EMAIL) {
            $this->dispatch('toast', type: 'error', message: 'This system email is reserved.');

            return;
        }

        $imgUrl = $this->avatar ? $uploads->store($this->avatar, 'user-profiles') : null;

        $user = User::create([
            'img_url' => $imgUrl,
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'mobile' => trim($this->mobile),
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'status' => $this->status,
            'permissions' => $this->normalizePermissions(),
        ]);

        $this->avatar = null;
        ActivityLogger::log('Create', 'Users & Roles', "Created user '{$user->name}' ({$user->role}, {$user->status}).", ['target_user_id' => $user->id]);
        $this->showCreateModal = false;
        $this->dispatch('toast', type: 'success', message: "User '{$user->name}' created successfully!");
    }

    public function updateUser(SafeImageUploadService $uploads): void
    {
        if (! Auth::user()?->isDeveloper()) {
            abort(403);
        }

        $user = User::findOrFail($this->editingId);
        $isDevTarget = $user->isDeveloper();

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [$isDevTarget ? 'nullable' : 'nullable', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager', 'staff', 'cashier', 'accountant'])],
            'status' => ['required', Rule::in(['approved', 'pending', 'rejected'])],
            'avatar' => ['nullable', 'image', 'max:3072'],
        ]);

        if (! $isDevTarget && strtolower(trim($this->email)) === User::DEVELOPER_EMAIL) {
            $this->dispatch('toast', type: 'error', message: 'This system email is reserved.');

            return;
        }

        if ($this->avatar) {
            $user->img_url = $uploads->store($this->avatar, 'user-profiles', $user->img_url);
            $this->avatar = null;
        }

        $user->name = trim($this->name);
        $user->mobile = trim($this->mobile);

        if ($isDevTarget) {
            // Keep developer identity, super_admin role, and full access locked safely
            $user->email = User::DEVELOPER_EMAIL;
            $user->role = 'super_admin';
            $user->status = 'approved';
            $user->permissions = array_fill_keys(PermissionRegistry::allKeys(), true);
        } else {
            $cleanEmail = trim($this->email);
            $user->email = $cleanEmail !== '' ? strtolower($cleanEmail) : null;
            $user->role = $this->role;
            $user->status = $this->status;
            $user->permissions = $this->normalizePermissions();
        }

        if (trim($this->password) !== '') {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        ActivityLogger::log('Update', 'Users & Roles', "Updated profile/permissions for '{$user->name}' ({$user->role}, {$user->status}).", ['target_user_id' => $user->id]);
        $this->showEditModal = false;
        $this->dispatch('toast', type: 'success', message: "User '{$user->name}' updated!");
    }

    public function deleteUser(int $id): void
    {
        if (! Auth::user()?->isDeveloper()) {
            abort(403);
        }

        $target = User::findOrFail($id);
        $actor = Auth::user();

        if ($target->id === $actor?->id || $target->isDeveloper()) {
            $this->dispatch('toast', type: 'error', message: 'This account cannot be deleted.');

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
