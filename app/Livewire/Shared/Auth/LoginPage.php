<?php

namespace App\Livewire\Shared\Auth;

use App\Models\Shared\User;
use App\Services\Shared\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('MARSS CORPORATION | Sign In')]
class LoginPage extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public string $errorMessage = '';

    private const LEGACY_EMAIL_ALIASES = [
        'admin@anisstore.com' => 'admin@gmail.com',
        'admin@marsscorporation.com' => 'admin@gmail.com',
    ];

    public function authenticate(): void
    {
        $this->errorMessage = '';

        $this->validate([
            'email' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string', 'min:3'],
        ]);

        $identifier = strtolower(trim($this->email));
        $resolvedEmail = self::LEGACY_EMAIL_ALIASES[$identifier] ?? $identifier;

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$resolvedEmail])
            ->orWhere('mobile', trim($this->email))
            ->first();

        if (! $user || ! Hash::check($this->password, (string) $user->password)) {
            $this->errorMessage = 'Invalid email/mobile or password. Please try again.';

            return;
        }

        if (! $user->isApproved()) {
            $this->errorMessage = 'Your account is currently pending approval or inactive. Contact an administrator.';

            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        ActivityLogger::log('Login', 'Auth', "User '{$user->name}' signed into MARSS CORPORATION.", null, $user);

        $role = strtolower(trim((string) $user->role));
        $target = $role === 'cashier' ? route('retail.pos') : route('retail.dashboard');

        $this->redirect($target, navigate: true);
    }

    public function render()
    {
        return view('livewire.shared.auth.login-page');
    }
}
