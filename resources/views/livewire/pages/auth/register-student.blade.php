<?php

use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $admission_number = null;
    public ?string $class = null;
    public ?string $phone = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'admission_number' => ['required', 'string', 'max:50', 'unique:' . Member::class],
            'class' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function register(): void
    {
        $validated = $this->validate();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole('student');

        app(MemberService::class)->registerMember([
            'first_name' => explode(' ', $validated['name'], 2)[0],
            'last_name' => explode(' ', $validated['name'], 2)[1] ?? '',
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'admission_number' => $validated['admission_number'],
            'class' => $validated['class'],
            'membership_type' => 'student',
            'status' => 'active',
            'joined_at' => now()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'user_id' => $user->id,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="text-center lg:text-left mb-8">
        <h2 class="text-2xl font-bold text-surface-900 tracking-tight">Student Registration</h2>
        <p class="text-sm text-surface-500 mt-1.5">Register for the OLLMCHS Library</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-input-label for="name" :value="__('Full name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1.5 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="John Doe" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email address')" />
                <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autocomplete="username" placeholder="you@ollmchs.ac.ke" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone (optional)')" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1.5 w-full" type="text" name="phone" placeholder="+254 7XX XXX XXX" />
                <x-input-error :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="admission_number" :value="__('Admission number')" />
                <x-text-input wire:model="admission_number" id="admission_number" class="block mt-1.5 w-full" type="text" name="admission_number" required placeholder="e.g. ADM-2026-001" />
                <x-input-error :messages="$errors->get('admission_number')" />
            </div>

            <div>
                <x-input-label for="class" :value="__('Year of Study')" />
                <x-text-input wire:model="class" id="class" class="block mt-1.5 w-full" type="text" name="class" required placeholder="e.g. Year 1, Year 2, Year 3" />
                <x-input-error :messages="$errors->get('class')" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            <span wire:loading.remove wire:target="register">Create account</span>
            <span wire:loading wire:target="register" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Creating account...
            </span>
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-surface-500 mt-6">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">Sign in</a>
    </p>

    <p class="text-center text-xs text-surface-400 mt-3">
        <a href="{{ route('register') }}" wire:navigate class="hover:text-primary-600 transition-colors">Staff registration</a>
    </p>
</div>
