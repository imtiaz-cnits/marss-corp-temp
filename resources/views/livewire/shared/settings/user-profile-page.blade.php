<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">My Profile</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                {{ $user->isDeveloper() ? 'Manage your account identity, avatar, and password security.' : 'Manage your profile photo, name, and email address.' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Profile Summary Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-6 flex flex-col items-center text-center shadow-xs">
            <img
                src="{{ $avatar ? $avatar->temporaryUrl() : $user->avatarUrl() }}"
                alt="{{ $user->name }}"
                class="w-28 h-28 rounded-full object-cover border-4 border-emerald-600 shadow-md"
            >
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
            <div class="mt-3 flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-bold uppercase">{{ $user->role }}</span>
                <span class="px-2.5 py-1 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 text-xs font-bold uppercase">{{ $user->status }}</span>
            </div>
        </div>

        {{-- Profile Edit & Password Forms --}}
        <div class="lg:col-span-2 space-y-4">
            <form wire:submit="updateProfile" class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-slate-200 dark:border-slate-800 pb-2">
                    <i class="fa-solid fa-user-pen mr-1.5"></i> Profile Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Full Name *</label>
                        <input type="text" wire:model="name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                            Email {{ $user->isDeveloper() ? '(Protected)' : '' }}
                        </label>
                        @if($user->isDeveloper())
                            <input type="email" value="{{ $email }}" readonly class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60 text-sm text-slate-500">
                        @else
                            <input type="email" wire:model="email" placeholder="Enter email address" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Mobile</label>
                        <input type="text" wire:model="mobile" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                        @error('mobile') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Profile Photo (Max 3MB)</label>
                        <input type="file" wire:model="avatar" accept="image/*" class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:file:bg-emerald-100">
                        @error('avatar') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="h-10 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold shadow-xs cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save Changes
                    </button>
                </div>
            </form>

            @if($user->isDeveloper())
            <form wire:submit="updatePassword" class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-slate-200 dark:border-slate-800 pb-2">
                    <i class="fa-solid fa-shield-halved mr-1.5"></i> Change Password
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Current Password *</label>
                        <input type="password" wire:model="current_password" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                        @error('current_password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">New Password *</label>
                        <input type="password" wire:model="new_password" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                        @error('new_password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Confirm Password *</label>
                        <input type="password" wire:model="new_password_confirmation" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-emerald-700 outline-none">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="h-10 px-5 rounded-xl bg-slate-800 dark:bg-emerald-700 hover:bg-slate-900 dark:hover:bg-emerald-800 text-white text-sm font-bold shadow-xs cursor-pointer">
                        <i class="fa-solid fa-key mr-1.5"></i> Update Password
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
