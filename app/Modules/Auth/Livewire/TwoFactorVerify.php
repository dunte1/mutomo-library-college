<?php

namespace App\Modules\Auth\Livewire;

use Livewire\Component;

class TwoFactorVerify extends Component
{
    public string $code = '';

    protected $rules = [
        'code' => 'required|string|size:6',
    ];

    public function verify(): void
    {
        $this->validate();

        $user = auth()->user();

        if (!$user || !$user->two_factor_secret) {
            session()->flash('error', 'Two-factor authentication is not configured.');
            return;
        }

        if ($user->verifyTwoFactorCode($this->code)) {
            session(['two_factor_verified' => true]);
            $this->redirect(route('dashboard'), navigate: true);
        } else {
            $this->addError('code', 'Invalid verification code.');
        }
    }

    public function render()
    {
        return view('auth::livewire.two-factor-verify')->layout('layouts.guest');
    }
}
