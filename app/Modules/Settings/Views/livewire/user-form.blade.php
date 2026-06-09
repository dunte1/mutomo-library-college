<div>
    <x-header :title="$isEditing ? 'Edit User' : 'Create User'"
        :subtitle="$isEditing ? 'Update user details and roles' : 'Add a new system user'">
        <x-slot:actions>
            <x-btn :href="route('settings.users')" wire:navigate>Back to Users</x-btn>
        </x-slot:actions>
    </x-header>

    <x-card>
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-field label="Full Name" required>
                    <x-input wire:model="name" placeholder="Enter full name" />
                    <x-input-error for="name" />
                </x-field>

                <x-field label="Email Address" required>
                    <x-input type="email" wire:model="email" placeholder="user@example.com" />
                    <x-input-error for="email" />
                </x-field>

                <x-field label="Phone Number">
                    <x-input wire:model="phone" placeholder="+254 7XX XXX XXX" />
                    <x-input-error for="phone" />
                </x-field>

                <x-field label="Admission Number">
                    <x-input wire:model="admission_number" placeholder="For students" />
                    <x-input-error for="admission_number" />
                </x-field>

                <x-field label="Employee ID">
                    <x-input wire:model="employee_id" placeholder="For staff" />
                    <x-input-error for="employee_id" />
                </x-field>

                <x-field label="Department">
                    <x-select wire:model="department_id">
                        <option value="">Select department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error for="department_id" />
                </x-field>

                <x-field label="Program">
                    <x-select wire:model="program_id">
                        <option value="">Select program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error for="program_id" />
                </x-field>

                <x-field label="Status">
                    <x-select wire:model="is_active">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </x-select>
                    <x-input-error for="is_active" />
                </x-field>

                @if(!$isEditing)
                    <x-field label="Password" required>
                        <x-input type="password" wire:model="password" placeholder="Min 8 characters" />
                        <x-input-error for="password" />
                    </x-field>

                    <x-field label="Confirm Password" required>
                        <x-input type="password" wire:model="password_confirmation" placeholder="Repeat password" />
                        <x-input-error for="password_confirmation" />
                    </x-field>
                @else
                    <x-field label="New Password">
                        <x-input type="password" wire:model="password" placeholder="Leave blank to keep current" />
                        <x-input-error for="password" />
                    </x-field>

                    <x-field label="Confirm New Password">
                        <x-input type="password" wire:model="password_confirmation" placeholder="Repeat new password" />
                        <x-input-error for="password_confirmation" />
                    </x-field>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Roles</label>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                            <input type="checkbox" value="{{ $role->id }}"
                                wire:model="selectedRoles"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($role->name) }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error for="selectedRoles" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-btn :href="route('settings.users')" wire:navigate>Cancel</x-btn>
                <x-btn primary type="submit">
                    {{ $isEditing ? 'Update User' : 'Create User' }}
                </x-btn>
            </div>
        </form>
    </x-card>
</div>
