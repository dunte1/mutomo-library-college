<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $avatar;
    public ?string $avatarUrl = null;

    public $passportPhoto;
    public ?string $passportPhotoUrl = null;
    public bool $showCropper = false;
    public string $cropperAspectRatio = '0.85'; // passport photo ~ 35mm x 41mm

    public int $totalBorrowed = 0;
    public int $currentlyBorrowed = 0;
    public int $totalFines = 0;
    public int $totalReservations = 0;
    public int $readingProgress = 0;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        if ($user->avatar) {
            $this->avatarUrl = url('storage/' . $user->avatar);
        }
        if ($user->passport_photo) {
            $this->passportPhotoUrl = url('storage/' . $user->passport_photo);
        }

        $this->totalBorrowed = $user->borrowRecords()->count();
        $this->currentlyBorrowed = $user->borrowRecords()->whereNull('returned_at')->count();
        $this->totalFines = (int) $user->fines()
            ->where('status', 'pending')
            ->get()
            ->sum(fn($f) => $f->outstanding_balance);
        $this->totalReservations = $user->reservations()->where('status', 'pending')->count();
        $this->readingProgress = \App\Modules\DigitalLibrary\Models\ReadingHistory::where('user_id', $user->id)->count();
    }

    public function updatedPassportPhoto(): void
    {
        $this->validate([
            'passportPhoto' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB max
        ]);

        $this->showCropper = true;
        $this->dispatch('passport-photo-selected');
    }

    public function cancelCrop(): void
    {
        $this->showCropper = false;
        $this->passportPhoto = null;
        $this->dispatch('destroy-cropper');
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'passportPhoto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->avatarUrl = url('storage/' . $path);
            $this->avatar = null;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('avatar-updated');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = null;
        $user->save();
        $this->avatarUrl = null;
        $this->dispatch('avatar-updated');
    }

    public function removePassportPhoto(): void
    {
        $user = Auth::user();
        if ($user->passport_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->passport_photo);
        }
        $user->passport_photo = null;
        $user->save();
        $this->passportPhotoUrl = null;
    }

    public function savePassportPhoto(): void
    {
        $this->validate([
            'passportPhoto' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $user = Auth::user();

        // Delete old passport photo if exists
        if ($user->passport_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->passport_photo);
        }

        // Store new cropped/optimized photo
        $path = $this->passportPhoto->store('passport-photos', 'public');
        $user->passport_photo = $path;
        $user->save();

        $this->passportPhotoUrl = url('storage/' . $path);
        $this->showCropper = false;
        $this->passportPhoto = null;

        $this->dispatch('destroy-cropper');
        $this->dispatch('notify', type: 'success', message: 'Passport photo updated successfully.');
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Profile Information</h2>
        <p class="mt-1 text-sm text-surface-500">Update your account's profile information and email address.</p>
    </header>

    {{-- Library Activity Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20">
            <p class="text-xs text-primary-600 dark:text-primary-400 font-medium">Total Borrowed</p>
            <p class="text-xl font-bold text-primary-700 dark:text-primary-300">{{ $totalBorrowed }}</p>
        </div>
        <div class="p-3 rounded-xl bg-secondary-50 dark:bg-secondary-900/20">
            <p class="text-xs text-secondary-600 dark:text-secondary-400 font-medium">Currently Borrowed</p>
            <p class="text-xl font-bold text-secondary-700 dark:text-secondary-300">{{ $currentlyBorrowed }}</p>
        </div>
        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20">
            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Unpaid Fines</p>
            <p class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $totalFines }}</p>
        </div>
        <div class="p-3 rounded-xl bg-accent-50 dark:bg-accent-900/20">
            <p class="text-xs text-accent-600 dark:text-accent-400 font-medium">Active Holds</p>
            <p class="text-xl font-bold text-accent-700 dark:text-accent-300">{{ $totalReservations }}</p>
        </div>
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Digital Reading</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $readingProgress }}</p>
        </div>
    </div>

    <form wire:submit="updateProfileInformation" class="space-y-6">
        {{-- Avatar Section --}}
        <div class="card bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-surface-900 dark:text-white mb-4">Profile Photo (Avatar)</h3>
            <div class="flex items-center gap-6">
                <div class="shrink-0">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-surface-200 dark:border-surface-600">
                    @else
                        <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-2xl font-bold border-2 border-surface-200 dark:border-surface-600">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="label">Profile Photo</label>
                    <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                    <div wire:loading wire:target="avatar" class="mt-1 text-sm text-primary-600">Uploading...</div>
                    @error('avatar') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    @if ($avatarUrl)
                        <button type="button" wire:click="removeAvatar" wire:confirm="Remove this photo?" class="mt-1 text-sm text-accent-600 hover:text-accent-700 underline">Remove photo</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Passport Photo Section --}}
        <div class="card bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-surface-900 dark:text-white mb-4">Passport Photo <span class="text-xs font-normal text-surface-400">(for library card &amp; ID documents)</span></h3>

            @if($showCropper && $passportPhoto)
                {{-- Cropper UI --}}
                <div class="space-y-4">
                    <div class="bg-surface-900/5 dark:bg-surface-900/30 rounded-xl overflow-hidden">
                        <div class="max-w-md mx-auto">
                            <img id="passport-cropper-image"
                                 src="{{ $passportPhoto->temporaryUrl() }}"
                                 alt="Crop passport photo"
                                 class="max-w-full">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <p class="text-xs text-surface-400 flex-1">Drag to reposition. Scroll to zoom.</p>
                        <button type="button" wire:click="cancelCrop" class="btn-sm btn-outline">Cancel</button>
                        <button type="button" id="apply-crop-btn" class="btn-primary btn-sm">Apply &amp; Save</button>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-6">
                    <div class="shrink-0">
                        @if ($passportPhotoUrl)
                            <img src="{{ $passportPhotoUrl }}" alt="Passport Photo" class="w-24 h-28 object-cover rounded-xl border-2 border-surface-200 dark:border-surface-600 shadow-sm">
                        @else
                            <div class="w-24 h-28 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500 dark:text-primary-400 border-2 border-dashed border-surface-300 dark:border-surface-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="label">Upload Passport Photo</label>
                        <p class="text-xs text-surface-400 mb-2">Recommended: Passport-style photo (35mm x 41mm ratio). Max 5MB. JPEG, PNG, or WebP.</p>
                        <input type="file" wire:model="passportPhoto" accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-300">
                        <div wire:loading wire:target="passportPhoto" class="mt-1 text-sm text-primary-600">Uploading...</div>
                        @error('passportPhoto') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        @if ($passportPhotoUrl)
                            <button type="button" wire:click="removePassportPhoto" wire:confirm="Remove passport photo? This will also remove it from your library card." class="mt-1 text-sm text-accent-600 hover:text-accent-700 underline">Remove photo</button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Personal Details --}}
        <div class="card bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-surface-900 dark:text-white mb-4">Personal Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label" for="name">Full Name</label>
                    <input wire:model="name" id="name" type="text" class="input-field w-full" required autofocus autocomplete="name">
                    @error('name') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="email">Email</label>
                    <input wire:model="email" id="email" type="email" class="input-field w-full" required autocomplete="username">
                    @error('email') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-sm text-amber-600 dark:text-amber-400">
                                Your email address is unverified.
                                <button wire:click.prevent="sendVerification" class="underline hover:no-underline">Click here to re-send the verification email.</button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-1 text-sm text-emerald-600 dark:text-emerald-400">A new verification link has been sent to your email address.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <label class="label" for="phone">Phone Number</label>
                    <input wire:model="phone" id="phone" type="text" class="input-field w-full" autocomplete="tel">
                    @error('phone') <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Save Changes</span>
                <span wire:loading>Saving...</span>
            </button>

            <div wire:loading.remove wire:target="avatar">
                <x-action-message class="me-3" on="profile-updated">
                    Saved.
                </x-action-message>
            </div>
        </div>
    </form>
</section>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        let cropperInitialized = false;

        // When a passport photo is selected, initialize the cropper
        Livewire.on('passport-photo-selected', () => {
            setTimeout(() => {
                const image = document.getElementById('passport-cropper-image');
                if (!image) return;

                // Destroy any existing cropper
                if (window.__destroyCropper) {
                    window.__destroyCropper();
                }

                // Initialize cropper with passport aspect ratio (35mm x 41mm ≈ 0.85)
                const aspectRatio = parseFloat(document.querySelector('[wire\\:model=cropperAspectRatio]')?.value || '0.85');
                if (window.__initCropper) {
                    window.__initCropper(image, aspectRatio);
                    cropperInitialized = true;
                }
            }, 100);
        });

        // When cropper is cancelled/destroyed
        Livewire.on('destroy-cropper', () => {
            if (window.__destroyCropper) {
                window.__destroyCropper();
            }
            cropperInitialized = false;
        });

        // Apply crop button
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('#apply-crop-btn');
            if (!btn || !cropperInitialized) return;

            btn.disabled = true;
            btn.textContent = 'Processing...';

            try {
                // Get cropped blob (image optimization: max 600x600, 80% quality)
                const blob = await window.__getCroppedBlob('image/jpeg');
                if (!blob) {
                    alert('Cropping failed. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Apply & Save';
                    return;
                }

                // Upload via Livewire's built-in file upload
                // Create a File from the blob
                const file = new File([blob], 'passport.jpg', { type: 'image/jpeg' });

                // Upload using Livewire's upload mechanism
                @this.upload('passportPhoto', file, (uploadedUrl) => {
                    // Success callback
                    @this.call('savePassportPhoto');
                    btn.disabled = false;
                    btn.textContent = 'Apply & Save';
                }, () => {
                    // Error callback
                    btn.disabled = false;
                    btn.textContent = 'Apply & Save';
                    alert('Upload failed. Please try again.');
                }, (event) => {
                    // Progress callback
                    const progress = event.detail.progress;
                    btn.textContent = `Uploading ${progress}%...`;
                });
            } catch (err) {
                console.error('Crop error:', err);
                btn.disabled = false;
                btn.textContent = 'Apply & Save';
                alert('An error occurred. Please try again.');
            }
        });

        // Cleanup cropper on livewire navigation
        document.addEventListener('livewire:navigated', () => {
            if (window.__destroyCropper) {
                window.__destroyCropper();
            }
            cropperInitialized = false;
        });
    });
</script>
@endpush
