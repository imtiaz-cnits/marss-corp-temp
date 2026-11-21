<div class="space-y-4">
    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">User List &amp; Role Permissions</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage users, approval status, and 33 granular module permissions enforced server-side.</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold shadow-xs flex items-center gap-2 cursor-pointer"
        >
            <i class="fa-solid fa-user-plus"></i> Add User
        </button>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <p class="text-xs font-bold uppercase text-slate-500">Total Users</p>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <p class="text-xs font-bold uppercase text-slate-500">Administrators</p>
            <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-1">{{ $stats['admins'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <p class="text-xs font-bold uppercase text-slate-500">Approved</p>
            <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <p class="text-xs font-bold uppercase text-slate-500">Pending / Inactive</p>
            <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['pending'] }}</p>
        </div>
    </div>

    {{-- Filter & Table Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <select wire:model.live="perPage" class="h-9 px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass text-xs text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, email, role..." class="h-9 pl-8 pr-3 w-56 sm:w-72 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-700">
                </div>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table id="usersTable" data-export-skip-last="true" class="w-full text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Email &amp; Mobile</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($users as $idx => $u)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 font-semibold text-slate-500">{{ $users->firstItem() + $idx }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $u->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover border border-emerald-600">
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 dark:text-slate-200">{{ $u->email }}</div>
                                <div class="text-xs text-slate-400">{{ $u->mobile ?: '—' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase whitespace-nowrap {{ $u->isDeveloper() || strtolower(trim((string) $u->role)) === 'super_admin' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $u->isDeveloper() ? 'SUPER ADMIN' : str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $u->isApproved() ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ $u->status ?: 'pending' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEditModal({{ $u->id }})" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-bold cursor-pointer">
                                    <i class="fa-solid fa-sliders mr-1"></i> Edit / Perms
                                </button>
                                @if(! $u->isDeveloper() && $u->id !== auth()->id())
                                    <button type="button" wire:click="deleteUser({{ $u->id }})" wire:confirm="Delete user '{{ $u->name }}'?" class="ml-1 px-2.5 py-1.5 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 hover:bg-red-100 text-xs font-bold cursor-pointer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
            @foreach($users as $u)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img src="{{ $u->avatarUrl() }}" class="w-9 h-9 rounded-full object-cover border border-emerald-600">
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $u->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap {{ $u->isDeveloper() || strtolower(trim((string) $u->role)) === 'super_admin' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $u->isDeveloper() ? 'SUPER ADMIN' : str_replace('_', ' ', $u->role) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs font-semibold {{ $u->isApproved() ? 'text-green-600' : 'text-amber-600' }}">{{ ucfirst($u->status ?: 'pending') }}</span>
                        <div class="flex gap-1.5">
                            <button type="button" wire:click="openEditModal({{ $u->id }})" class="px-3 py-1 rounded-lg bg-emerald-700 text-white text-xs font-bold">Edit</button>
                            @if(! $u->isDeveloper() && $u->id !== auth()->id())
                                <button type="button" wire:click="deleteUser({{ $u->id }})" wire:confirm="Delete user '{{ $u->name }}'?" class="px-2.5 py-1 rounded-lg bg-red-600 text-white text-xs font-bold"><i class="fa-solid fa-trash"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $users->links() }}</div>
    </div>

    {{-- Create / Edit Modal with 33-Checkbox Granular Permission Matrix --}}
    <x-ui.modal :show="$showCreateModal || $showEditModal" max-width="lg" :title="$showCreateModal ? 'Create User & Assign Permissions' : 'Edit User & Granular Permissions'" close-method="closeModals">
        <form wire:submit="{{ $showCreateModal ? 'createUser' : 'updateUser' }}" class="space-y-4">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <img
                    src="{{ $avatar ? $avatar->temporaryUrl() : ($currentAvatarUrl ?: asset('backend/assets/img/profile-img.png')) }}"
                    alt="Avatar Preview"
                    class="w-12 h-12 rounded-full object-cover border-2 border-emerald-600 shrink-0"
                >
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Profile Image (Synced with User Profile)</label>
                    <input type="file" wire:model="avatar" accept="image/*" class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:file:bg-emerald-100">
                    @error('avatar') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Full Name *</label>
                    <input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Email {{ $editingIsDeveloper ? '(Protected)' : ($showCreateModal ? '*' : '') }}</label>
                    <input type="email" wire:model="email" @readonly($editingIsDeveloper) class="w-full h-10 px-3 rounded-xl border-[1.5px] {{ $editingIsDeveloper ? 'border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60 text-slate-500' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800' }} text-sm">
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Mobile</label>
                    <input type="text" wire:model="mobile" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Password {{ $showEditModal ? '(Leave blank to keep)' : '*' }}</label>
                    <input type="password" wire:model="password" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Role *</label>
                    <select wire:model.live="role" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                        @if(auth()->user()?->isSuperAdmin())
                            <option value="super_admin">Super Admin</option>
                        @endif
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="accountant">Accountant</option>
                        <option value="cashier">Cashier</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Approval Status *</label>
                    <select wire:model="status" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            {{-- 33 Granular Permissions Matrix --}}
            <div class="border-[1.5px] border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 bg-slate-50/70 dark:bg-slate-800/40">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                        Module &amp; Feature Permissions
                    </h4>
                    @if(in_array($role, ['admin', 'super_admin'], true))
                        <span class="text-[11px] font-bold text-emerald-600">Full Administrative Access Enabled</span>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($permissionGroups as $parentKey => $group)
                        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                            <label class="flex items-center gap-2 font-bold text-xs text-slate-800 dark:text-white cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:click="toggleParentPermission('{{ $parentKey }}')"
                                    @checked((bool) ($permissions[$parentKey] ?? false))
                                    class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600"
                                >
                                {{ $group['label'] }}
                            </label>
                            @if(!empty($group['children']))
                                <div class="mt-2 pl-5 space-y-1 border-l border-slate-200 dark:border-slate-700">
                                    @foreach($group['children'] as $childKey => $childLabel)
                                        <label class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                wire:model="permissions.{{ $childKey }}"
                                                class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500"
                                            >
                                            {{ $childLabel }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-sm font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold cursor-pointer">
                    {{ $showCreateModal ? 'Create User' : 'Save Permissions' }}
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>
