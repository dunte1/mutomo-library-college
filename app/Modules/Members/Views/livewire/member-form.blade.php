{{-- Member registration form --}}
@section('title', 'Member Form')
<div>
    <x-header :title="$isEditing ? 'Edit Member' : 'Register New Member'" :subtitle="$isEditing ? 'Update member details and information' : 'Add a new member to the library system'">
        <x-slot:actions>
            <a href="{{ route('members.index') }}" wire:navigate class="btn-outline btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                Back
            </a>
        </x-slot:actions>
    </x-header>

    @unless($isEditing)
    {{-- Tab navigation for single vs bulk registration --}}
    <div class="mb-6 border-b border-surface-200 dark:border-surface-700">
        <nav class="flex gap-6 -mb-px" role="tablist">
            <button type="button" role="tab"
                wire:click="$set('mode', 'single')"
                class="pb-3 px-1 text-sm font-medium border-b-2 transition-colors
                    {{ $mode === 'single'
                        ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400'
                        : 'border-transparent text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 hover:border-surface-300' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Single Registration
                </span>
            </button>
            <button type="button" role="tab"
                wire:click="$set('mode', 'bulk')"
                class="pb-3 px-1 text-sm font-medium border-b-2 transition-colors
                    {{ $mode === 'bulk'
                        ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400'
                        : 'border-transparent text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 hover:border-surface-300' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Bulk Import
                </span>
            </button>
        </nav>
    </div>
    @endunless

    @if($mode === 'bulk' && !$isEditing)
        @livewire('member-bulk-import')
    @else
    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Personal Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">First Name *</label>
                        <input type="text" wire:model="first_name" class="input-field" placeholder="First name">
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Last Name *</label>
                        <input type="text" wire:model="last_name" class="input-field" placeholder="Last name">
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" wire:model="email" class="input-field" placeholder="email@example.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="phone" class="input-field" placeholder="+254 7XX XXX XXX">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Date of Birth</label>
                        <input type="date" wire:model="date_of_birth" class="input-field">
                        @error('date_of_birth') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Gender</label>
                        <select wire:model="gender" class="input-field">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">National ID Number</label>
                        <input type="text" wire:model="id_number" class="input-field" placeholder="ID number">
                        @error('id_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Department</label>
                        <select wire:model="department_id" class="input-field">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Program</label>
                        <select wire:model="program_id" class="input-field">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                        @error('program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @if($membership_type === 'student')
                        <div>
                            <label class="label">Student ID</label>
                            <input type="text" value="{{ $student_id }}" readonly disabled class="input-field bg-surface-100 dark:bg-surface-800 text-surface-500 cursor-not-allowed" placeholder="Auto-generated on save">
                        </div>
                        <div>
                            <label class="label">Admission Number</label>
                            <input type="text" wire:model="admission_number" class="input-field" placeholder="e.g. ADM-2026-001">
                            @error('admission_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Year of Study</label>
                            <input type="text" wire:model="class" class="input-field" placeholder="e.g. Year 1, Year 2, Year 3">
                            @error('class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="label">Blood Group</label>
                        <select wire:model="blood_group" class="input-field">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'] as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                        @error('blood_group') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Address</label>
                        <textarea wire:model="address" rows="2" class="input-field" placeholder="Physical address..."></textarea>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Membership Details</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Membership Type *</label>
                        <select wire:model="membership_type" class="input-field">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Staff</option>
                            <option value="external">External</option>
                        </select>
                        @error('membership_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Status *</label>
                        <select wire:model="status" class="input-field">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="expired">Expired</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Joined Date *</label>
                        <input type="date" wire:model="joined_at" class="input-field">
                        @error('joined_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Expiry Date</label>
                        <input type="date" wire:model="expires_at" class="input-field">
                        @error('expires_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="notes" rows="3" class="input-field" placeholder="Additional notes..."></textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Photo</label>

                    {{-- Preview: existing or newly chosen --}}
                    @if($photo)
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ $photo->temporaryUrl() }}" alt="New photo preview"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-primary-200 shadow-sm">
                            <div>
                                <p class="text-sm font-medium text-surface-900 dark:text-white">New photo selected</p>
                                <p class="text-xs text-surface-500">Upload will apply on save</p>
                            </div>
                        </div>
                    @elseif($isEditing && $existingPhotoUrl)
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ $existingPhotoUrl }}" alt="Current photo" loading="lazy"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-surface-200 shadow-sm">
                            <div>
                                <p class="text-sm font-medium text-surface-900 dark:text-white">Current photo</p>
                                <p class="text-xs text-surface-500">Upload a new one to replace it</p>
                            </div>
                        </div>
                    @endif

                    {{-- Upload zone --}}
                    <label class="upload-zone">
                        <svg class="upload-zone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="upload-zone-text">
                            {{ $photo ? 'Replace photo' : ($isEditing ? 'Replace photo' : 'Upload photo') }}
                        </span>
                        <span class="upload-zone-hint">JPG, PNG, WEBP · max 2 MB</span>
                        <input type="file" wire:model="photo" accept="image/*" class="upload-zone-input">
                    </label>
                    @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- A login account is always created automatically --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-start gap-3 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-primary-800 dark:text-primary-200">Login account auto-created</p>
                        <p class="text-xs text-primary-600 dark:text-primary-300 mt-0.5">
                            A user account will be created automatically using the member's email. A random password will be generated and sent via email. The appropriate role is assigned based on the membership type.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 mobile-form-actions">
            <a href="{{ route('members.index') }}" wire:navigate class="btn-outline text-center">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    {{ $isEditing ? 'Update Member' : 'Register Member' }}
                </span>
                <span wire:loading>
                    <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </form>
    @endif
</div>
