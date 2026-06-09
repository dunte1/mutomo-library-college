<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Card Verification | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            @if($valid)
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Valid Library Card</h1>
                <p class="text-sm text-gray-500 mb-6">{{ $message }}</p>

                <div class="border-t border-gray-100 pt-6 space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Card Number</span>
                        <span class="text-sm font-mono font-medium text-gray-900">{{ $card->card_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Member Name</span>
                        <span class="text-sm font-medium text-gray-900">{{ $member->full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Membership Type</span>
                        <span class="text-sm font-medium capitalize text-gray-900">{{ $member->membership_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Issued</span>
                        <span class="text-sm text-gray-900">{{ $card->issued_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Expires</span>
                        <span class="text-sm text-gray-900">{{ $card->expires_at?->format('d M Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                <p class="text-xs text-gray-400 mt-6">Verified at {{ now()->format('d M Y H:i:s') }}</p>
            @else
                <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Invalid Card</h1>
                <p class="text-sm text-gray-500">{{ $message }}</p>
            @endif

            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400">{{ config('app.name') }} &middot; Library Card Verification</p>
            </div>
        </div>
    </div>
</body>
</html>
