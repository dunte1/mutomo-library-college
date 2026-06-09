<div>
    <div class="page-header">
        <h1 class="page-title">Reports Dashboard</h1>
        <p class="page-subtitle">Overview of library reports and statistics</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('reports.catalog') }}" wire:navigate class="card hover:shadow-lg transition-shadow cursor-pointer">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Catalog Reports</h3>
                        <p class="text-sm text-surface-500">Books, categories, authors</p>
                        <p class="text-2xl font-bold mt-1">{{ $reportStats['total_books'] }}</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.circulation') }}" wire:navigate class="card hover:shadow-lg transition-shadow cursor-pointer">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Circulation Reports</h3>
                        <p class="text-sm text-surface-500">Borrows, returns, fines</p>
                        <p class="text-2xl font-bold mt-1">{{ $reportStats['active_borrows'] }}</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.members') }}" wire:navigate class="card hover:shadow-lg transition-shadow cursor-pointer">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Member Reports</h3>
                        <p class="text-sm text-surface-500">Members, cards, activity</p>
                        <p class="text-2xl font-bold mt-1">{{ $reportStats['total_members'] }}</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.digital-library') }}" wire:navigate class="card hover:shadow-lg transition-shadow cursor-pointer">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Digital Library Reports</h3>
                        <p class="text-sm text-surface-500">Assets, downloads, views</p>
                        <p class="text-2xl font-bold mt-1">{{ $reportStats['total_digital_assets'] }}</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('finance.reports') }}" wire:navigate class="card hover:shadow-lg transition-shadow cursor-pointer">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Finance Reports</h3>
                        <p class="text-sm text-surface-500">Transactions, fines, invoices</p>
                        <p class="text-2xl font-bold mt-1">{{ $reportStats['recent_transactions'] }} today</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
