<x-slot name="header">My Profile</x-slot>
<x-slot name="subtitle">Manage your account information, security, and preferences</x-slot>

<div class="max-w-4xl mx-auto space-y-6">
    {{-- Profile Information --}}
    <livewire:profile.update-profile-information-form />

    {{-- Notification Preferences --}}
    <livewire:profile.notification-preferences />

    {{-- Two-Factor Authentication --}}
    <livewire:profile.two-factor-form />

    {{-- Change Password --}}
    <livewire:profile.update-password-form />

    {{-- Active Sessions --}}
    <livewire:profile.active-sessions />

    {{-- Recent Activity --}}
    <livewire:profile.recent-activity />

    {{-- Delete Account --}}
    <livewire:profile.delete-user-form />
</div>
