{{--
    Dashboard: Shows admin stats for staff roles, patron stats for students/lecturers.
--}}
@php
    use App\Models\User;
    use App\Modules\Assignments\Models\ReadingAssignment;
    use App\Modules\Catalog\Models\Book;
    use App\Modules\Circulation\Models\BorrowRecord;
    use App\Modules\Circulation\Models\Fine;
    use App\Modules\Circulation\Models\Reservation;
    use App\Modules\DigitalLibrary\Models\ReadingHistory;
    use App\Modules\DigitalLibrary\Services\DigitalLibraryService;

    $user = auth()->user();
    $isStaff = $user->hasAnyPermission([
        'create-books', 'edit-books', 'delete-books', 'import-books', 'export-books',
        'manage-inventory', 'borrow-books', 'return-books', 'manage-reservations',
        'create-members', 'edit-members', 'delete-members', 'suspend-members',
        'upload-digital-assets', 'manage-fines', 'collect-payments', 'generate-invoices',
        'generate-receipts', 'process-refunds', 'view-financial-reports',
        'manage-settings', 'manage-roles', 'manage-permissions',
        'manage-announcements', 'manage-bulletins', 'manage-events',
        'send-notifications', 'manage-templates', 'manage-broadcasts',
        'generate-reports', 'schedule-reports',
        'create-assignments', 'manage-departments', 'manage-programs',
        'manage-library-cards', 'manage-membership-requests',
        'manage-subscriptions', 'manage-pricing', 'manage-system-optimization',
    ]);
@endphp

@if($isStaff)
    {{-- ======================== STAFF DASHBOARD ======================== --}}
    @php
        $totalBooks = Book::count();
        $activeBorrows = BorrowRecord::whereNull('returned_at')->count();
        $overdueBooks = BorrowRecord::whereNull('returned_at')->where('due_at', '<', now())->count();
        $recentBorrows = BorrowRecord::with('user', 'copy.book')->latest()->take(5)->get();
        $monthDriver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $monthExpr = match ($monthDriver) {
            'mysql' => "DATE_FORMAT(created_at, '%m')",
            'pgsql' => "TO_CHAR(created_at, 'MM')",
            'sqlite' => "strftime('%m', created_at)",
            default => "strftime('%m', created_at)",
        };
        $monthlyBorrows = BorrowRecord::selectRaw("{$monthExpr} as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')->orderBy('month')->pluck('total', 'month');
        $categoryCounts = Book::selectRaw('category_id, count(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')->orderByDesc('total')->take(5)
            ->with('category')->get()
            ->mapWithKeys(fn($item) => [$item->category?->name ?? 'Uncategorized' => $item->total]);

        $canCreateAssignments = $user->can('create-assignments');
        if ($canCreateAssignments) {
            $myAssignments = ReadingAssignment::forTeacher($user->id);
            $totalAssignments = (clone $myAssignments)->count();
            $viewedAssignments = (clone $myAssignments)->whereNotNull('viewed_at')->count();
            $completedAssignments = (clone $myAssignments)->where('status', ReadingAssignment::STATUS_COMPLETED)->count();
        }
    @endphp

    <x-app-layout>
        <x-slot name="header">Dashboard</x-slot>
        <x-slot name="subtitle">Welcome back, {{ $user->name }}. Here's your library overview.</x-slot>

        <div class="stat-carousel mb-6">
            @can('view-books')
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Total Books</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-primary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ number_format($totalBooks) }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Across all categories</p>
            </div>
            @endcan

            @can('view-circulation')
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Active Borrows</p>
                    <div class="stat-icon bg-gradient-to-br from-secondary-500 to-secondary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ number_format($activeBorrows) }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Currently checked out</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Overdue Books</p>
                    <div class="stat-icon bg-gradient-to-br from-amber-500 to-amber-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ number_format($overdueBooks) }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Requires attention</p>
            </div>
            @endcan

            @can('view-members')
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Registered Members</p>
                    <div class="stat-icon bg-gradient-to-br from-accent-500 to-accent-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ number_format(User::count()) }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Active library members</p>
            </div>
            @endcan

            @if($canCreateAssignments)
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Assignments</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-accent-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $totalAssignments }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                    {{ $viewedAssignments }} viewed &middot; {{ $completedAssignments }} completed
                </p>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="open = !open" class="collapsible-trigger md:hidden p-1 -ml-1" :class="{ 'open': open }">
                                <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <h3 class="font-semibold text-surface-900 dark:text-white">Recent Activity</h3>
                        </div>
                        @can('view-circulation')
                        <a href="{{ route('circulation.index') }}" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                        @endcan
                    </div>
                    <div class="card-body" x-show="open">
                        @forelse($recentBorrows as $borrow)
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400">{{ substr($borrow->user->name ?? '?', 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">
                                    {{ $borrow->user->name ?? 'Unknown' }} borrowed
                                    <span class="text-primary-600 dark:text-primary-400">{{ $borrow->copy->book->title ?? 'Unknown Book' }}</span>
                                </p>
                                <p class="text-xs text-surface-500">{{ $borrow->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge @if($borrow->due_at && $borrow->due_at->isPast()) badge-danger @else badge-info @endif text-xs">
                                {{ $borrow->due_at ? $borrow->due_at->diffForHumans() : 'No due date' }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-surface-500 dark:text-surface-400 text-center py-8">
                            No recent activity to display.
                        </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="quick-actions-desktop">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Quick Actions</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-3" x-show="open">
                        @can('create-books')
                        <a href="{{ route('catalog.books.create') }}" wire:navigate class="btn-primary w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Book
                        </a>
                        @endcan
                        @can('borrow-books')
                        <a href="{{ route('circulation.issue') }}" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Issue Book
                        </a>
                        @endcan
                        @can('return-books')
                        <a href="{{ route('circulation.return') }}" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Return Book
                        </a>
                        @endcan
                        @can('generate-reports')
                        <a href="{{ route('finance.reports') }}" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate Report
                        </a>
                        @endcan
                    </div>
                </div>

                @if($overdueBooks > 0)
                <div class="card mt-4 border-accent-200 dark:border-accent-800" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-accent-600 dark:text-accent-400">Overdue Alerts</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        <p class="text-sm text-surface-500 dark:text-surface-400">
                            {{ $overdueBooks }} book{{ $overdueBooks !== 1 ? 's' : '' }} currently overdue.
                            <a href="{{ route('circulation.index') }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline">View details</a>
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card" x-data="{ open: true }">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Borrowing Trends (6 Months)</h3>
                    <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                        <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <div class="card-body h-48 md:h-64" x-show="open">
                    @if($monthlyBorrows->isNotEmpty())
                    <div class="flex items-end gap-2 h-32 md:h-48">
                        @php $max = max($monthlyBorrows->toArray()) ?: 1; @endphp
                        @foreach($monthlyBorrows as $month => $count)
                        <div class="flex-1 flex flex-col items-center gap-1 h-full">
                            <span class="text-xs font-medium text-surface-600 dark:text-surface-400">{{ $count }}</span>
                            <div class="w-full bg-primary-200 dark:bg-primary-900/30 rounded-t-lg flex-1 min-h-0 w-full" style="max-height: {{ ($count / $max) * 100 }}%">
                                <div class="w-full h-full bg-primary-500 dark:bg-primary-400 rounded-t-lg opacity-80 hover:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-xs text-surface-500">{{ DateTime::createFromFormat('!m', $month)->format('M') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="h-full flex items-center justify-center">
                        <p class="text-sm text-surface-400 dark:text-surface-500">No borrowing data yet</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card" x-data="{ open: true }">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Top Categories</h3>
                    <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                        <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <div class="card-body h-64" x-show="open">
                    @if($categoryCounts->isNotEmpty())
                    <div class="space-y-3">
                        @php $catMax = max($categoryCounts->toArray()) ?: 1; @endphp
                        @foreach($categoryCounts as $category => $count)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-surface-700 dark:text-surface-300">{{ $category }}</span>
                                <span class="font-medium text-surface-900 dark:text-white">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-surface-200 dark:bg-surface-700 rounded-full h-2.5">
                                <div class="bg-primary-500 dark:bg-primary-400 h-2.5 rounded-full transition-all" style="width: {{ ($count / $catMax) * 100 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="h-full flex items-center justify-center">
                        <p class="text-sm text-surface-400 dark:text-surface-500">No categories yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MOBILE FAB --}}
        <div x-data="{ fabOpen: false }" class="md:hidden">
            <div x-show="fabOpen" x-cloak
                 @click="fabOpen = false"
                 class="fab-menu-backdrop" :class="{ 'show': fabOpen }"></div>
            <div class="fab-menu" :class="{ 'show': fabOpen }">
                @can('create-books')
                <div class="fab-menu-item" style="transition-delay: 0.05s">
                    <span class="fab-label">Add New Book</span>
                    <a href="{{ route('catalog.books.create') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #1E4FA3, #153168);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                </div>
                @endcan
                @can('borrow-books')
                <div class="fab-menu-item" style="transition-delay: 0.1s">
                    <span class="fab-label">Issue Book</span>
                    <a href="{{ route('circulation.issue') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #059669, #047857);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </a>
                </div>
                @endcan
                @can('return-books')
                <div class="fab-menu-item" style="transition-delay: 0.15s">
                    <span class="fab-label">Return Book</span>
                    <a href="{{ route('circulation.return') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #D62839, #b91c1c);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                </div>
                @endcan
            </div>
            <button @click="fabOpen = !fabOpen"
                    class="fab" :class="{ 'active': fabOpen }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
    </x-app-layout>
@else
    {{-- ======================== PATRON DASHBOARD ======================== --}}
    @php
        $activeBorrows = BorrowRecord::with('copy.book')
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->latest('borrowed_at')
            ->get();

        $overdueBorrows = $activeBorrows->filter(fn($b) => $b->isOverdue());
        $readingHistory = BorrowRecord::with('copy.book')
            ->where('user_id', $user->id)
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->take(5)
            ->get();

        $pendingFines = Fine::with('borrowRecord.copy.book')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();
        $totalFines = $pendingFines->sum(fn($f) => $f->outstanding_balance);

        $activeReservations = Reservation::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('reserved_at')
            ->get();

        $digitalProgress = ReadingHistory::with('asset')
            ->where('user_id', $user->id)
            ->where('progress', '<', 100)
            ->whereNotNull('last_read_at')
            ->latest('last_read_at')
            ->take(5)
            ->get();
    @endphp

    <x-app-layout>
        <x-slot name="header">My Dashboard</x-slot>
        <x-slot name="subtitle">Welcome back, {{ $user->name }}. Here's your library activity.</x-slot>

        {{-- Summary Stats --}}
        <div class="stat-carousel mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Currently Borrowed</p>
                    <div class="stat-icon bg-gradient-to-br from-secondary-500 to-secondary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $activeBorrows->count() }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                    @if($overdueBorrows->count() > 0)
                        <span class="text-red-500">{{ $overdueBorrows->count() }} overdue</span>
                    @else
                        All returned on time
                    @endif
                </p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Reservations</p>
                    <div class="stat-icon bg-gradient-to-br from-accent-500 to-accent-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $activeReservations->count() }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Active holds</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Pending Fines</p>
                    <div class="stat-icon bg-gradient-to-br from-amber-500 to-amber-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">${{ number_format($totalFines, 2) }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">{{ $pendingFines->count() }} unpaid</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Digital Reading</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-primary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $digitalProgress->count() }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">In progress</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Currently Borrowed Books --}}
            <div class="lg:col-span-2">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="open = !open" class="collapsible-trigger md:hidden p-1 -ml-1" :class="{ 'open': open }">
                                <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <h3 class="font-semibold text-surface-900 dark:text-white">Currently Borrowed</h3>
                        </div>
                        <a href="{{ route('circulation.index') }}" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Circulation</a>
                    </div>
                    <div class="card-body" x-show="open">
                        @forelse($activeBorrows as $borrow)
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-10 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">
                                    {{ $borrow->copy?->book?->title ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-surface-500">
                                    Borrowed {{ $borrow->borrowed_at->diffForHumans() }}
                                    @if($borrow->due_at) &middot; Due {{ $borrow->due_at->format('M d, Y') }} @endif
                                </p>
                            </div>
                            <span class="badge {{ $borrow->isOverdue() ? 'badge-danger' : 'badge-info' }} text-xs">
                                {{ $borrow->isOverdue() ? $borrow->daysOverdue() . ' days overdue' : ($borrow->due_at ? $borrow->due_at->diffForHumans(null, true) . ' left' : 'No due date') }}
                            </span>
                            @if(!$borrow->isOverdue() && $borrow->renewal_count < $borrow->max_renewals)
                                <button wire:click="{{ $borrow->id }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline shrink-0"
                                        @click="fetch('{{ route('circulation.renew', $borrow->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(() => { location.reload() }).catch(e => {})">
                                    Renew
                                </button>
                            @endif
                        </div>
                        @empty
                        <p class="text-sm text-surface-500 dark:text-surface-400 text-center py-8">
                            You haven't borrowed any books yet.
                            <a href="{{ route('catalog.books.index') }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline block mt-1">Browse the catalog</a>
                        </p>
                        @endforelse
                    </div>
                </div>

                {{-- Reservations --}}
                @if($activeReservations->isNotEmpty())
                <div class="card mt-6" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">My Reservations</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        @foreach($activeReservations as $reservation)
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-10 h-12 rounded-lg bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $reservation->book->title ?? 'Unknown' }}</p>
                                <p class="text-xs text-surface-500">Reserved {{ $reservation->reserved_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge badge-warning text-xs">Pending</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Reading History --}}
                @if($readingHistory->isNotEmpty())
                <div class="card mt-6" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Reading History</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        @foreach($readingHistory as $borrow)
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $borrow->copy?->book?->title ?? 'Unknown' }}</p>
                                <p class="text-xs text-surface-500">Returned {{ $borrow->returned_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge badge-success text-xs">Returned</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div>
                {{-- In-Progress Digital Reading --}}
                @if($digitalProgress->isNotEmpty())
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Continue Reading</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-3" x-show="open">
                        @foreach($digitalProgress as $history)
                        <a href="{{ route('digital-library.read', $history->asset) }}" wire:navigate
                           class="flex items-center gap-3 p-2 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">{{ $history->asset->title ?? 'Unknown' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 bg-surface-200 dark:bg-surface-700 rounded-full h-1.5">
                                        <div class="bg-primary-500 rounded-full h-1.5" style="width: {{ $history->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-surface-500">{{ $history->progress }}%</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Pending Fines --}}
                @if($pendingFines->isNotEmpty())
                <div class="card mt-4 border-accent-200 dark:border-accent-800" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-accent-600 dark:text-accent-400">Pending Fines</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        @foreach($pendingFines as $fine)
                        <div class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="min-w-0">
                                <p class="text-sm text-surface-900 dark:text-white truncate">{{ $fine->reason }}</p>
                                <p class="text-xs text-surface-500">Assessed {{ $fine->assessed_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-semibold text-red-500">${{ number_format($fine->outstanding_balance, 2) }}</span>
                        </div>
                        @endforeach
                        <div class="flex items-center justify-between pt-3 mt-2 border-t border-surface-100 dark:border-surface-700">
                            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Total Outstanding</span>
                            <span class="text-lg font-bold text-red-500">${{ number_format($totalFines, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Quick Links --}}
                <div class="card mt-4" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Quick Links</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-2" x-show="open">
                        <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-outline w-full justify-center text-sm">
                            Browse Catalog
                        </a>
                        <a href="{{ route('digital-library.index') }}" wire:navigate class="btn-outline w-full justify-center text-sm">
                            Digital Library
                        </a>
                        <a href="{{ route('profile') }}" wire:navigate class="btn-outline w-full justify-center text-sm">
                            My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE FAB --}}
        <div x-data="{ fabOpen: false }" class="md:hidden">
            <div x-show="fabOpen" x-cloak
                 @click="fabOpen = false"
                 class="fab-menu-backdrop" :class="{ 'show': fabOpen }"></div>
            <div class="fab-menu" :class="{ 'show': fabOpen }">
                <div class="fab-menu-item" style="transition-delay: 0.05s">
                    <span class="fab-label">Browse Catalog</span>
                    <a href="{{ route('catalog.books.index') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #1E4FA3, #153168);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.1s">
                    <span class="fab-label">Digital Library</span>
                    <a href="{{ route('digital-library.index') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #059669, #047857);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.15s">
                    <span class="fab-label">My Profile</span>
                    <a href="{{ route('profile') }}" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #D62839, #b91c1c);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                </div>
            </div>
            <button @click="fabOpen = !fabOpen"
                    class="fab" :class="{ 'active': fabOpen }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
    </x-app-layout>
@endif
