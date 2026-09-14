<?php

namespace App\Livewire\Pages\Auth;

use App\Enums\UserApprovalStatus;
use App\Helpers\ConfigHelper;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        if (! ConfigHelper::isRegistrationOpen()) {
            $this->dispatch('notify',
                type: 'error',
                message: ConfigHelper::getRegistrationClosedMessage()
            );
            $this->redirect(route('login'), navigate: true);

            return;
        }

        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $requiresApproval = ConfigHelper::isRegistrationApprovalRequired();
            $defaultRole = ConfigHelper::getDefaultRegistrationRole();

            if ($requiresApproval) {
                // User pending approval — tidak auto-login, tidak assign role, tidak kirim email verifikasi dulu.
                // Email verifikasi akan dikirim setelah admin menyetujui pendaftaran (lihat UserService::approveUser).
                $validated['is_active'] = false;
                $validated['approval_status'] = UserApprovalStatus::Pending->value;

                User::create($validated);

                $this->dispatch('notify',
                    type: 'success',
                    message: 'Pendaftaran berhasil! Akun Anda menunggu approval dari administrator. Anda akan mendapat email verifikasi setelah akun disetujui.'
                );
                $this->redirect(route('login'), navigate: true);
            } else {
                // Langsung aktif — auto-login seperti sebelumnya
                $validated['is_active'] = true;
                $validated['approval_status'] = UserApprovalStatus::Approved->value;

                event(new Registered($user = User::create($validated)));
                $user->assignRole($defaultRole);

                Auth::login($user);

                $this->dispatch('notify',
                    type: 'success',
                    message: 'Pendaftaran berhasil! Email verifikasi telah dikirim.'
                );

                $this->redirect(route('dashboard', absolute: false), navigate: true);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'
            );
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.register');
    }
}
