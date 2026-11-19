<?php

namespace App\Livewire\Shared\Settings;

use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('User Profile')]
class UserProfilePage extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public $avatar;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = (string) ($user?->name ?? '');
        $this->email = (string) ($user?->email ?? '');
        $this->mobile = (string) ($user?->mobile ?? '');
    }

    public function updateProfile(SafeImageUploadService $uploads): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($this->avatar) {
            $user->img_url = $uploads->store($this->avatar, 'user-profiles', $user->img_url);
            $this->avatar = null;
        }

        $user->name = trim($this->name);
        $user->mobile = trim($this->mobile);
        $user->save();

        ActivityLogger::log('Update', 'Profile', "Updated profile details for '{$user->name}'.");
        $this->dispatch('toast', type: 'success', message: 'Profile updated successfully!');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        if (! Hash::check($this->current_password, (string) $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');

            return;
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        ActivityLogger::log('Update', 'Profile', "Changed account password for '{$user->name}'.");
        $this->dispatch('toast', type: 'success', message: 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.shared.settings.user-profile-page', [
            'user' => Auth::user(),
        ]);
    }
}
