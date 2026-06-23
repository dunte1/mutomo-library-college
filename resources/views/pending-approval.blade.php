@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-surface-50 dark:bg-surface-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-6">
            <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-surface-900 dark:text-white mb-2">Account Pending Approval</h2>
        <p class="text-surface-500 dark:text-surface-400 mb-4">
            Your email has been verified. Your account is awaiting approval from the library administrator.
        </p>
        <p class="text-sm text-surface-400 dark:text-surface-500 mb-8">
            You will receive a notification once your account has been activated. If you have any questions, please contact the library staff.
        </p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-surface-200 dark:bg-surface-700 text-surface-700 dark:text-surface-300 rounded-lg hover:bg-surface-300 dark:hover:bg-surface-600 transition-colors text-sm font-medium">
                Sign Out
            </button>
        </form>
    </div>
</div>
@endsection
