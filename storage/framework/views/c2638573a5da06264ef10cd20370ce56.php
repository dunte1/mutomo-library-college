<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="description" content="<?php echo e(config('app.name')); ?> - Library Management System">

    <title><?php echo e(config('app.name', 'OLLMCHS Library')); ?> | <?php echo e($title ?? 'Authentication'); ?></title>

    <?php
        $faviconPath = null;
        try { $faviconPath = \App\Modules\Settings\Models\Setting::value('favicon'); } catch (\Throwable $e) {}
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($faviconPath): ?>
        <link rel="icon" type="image/<?php echo e(str_ends_with($faviconPath, '.svg') ? 'svg+xml' : (str_ends_with($faviconPath, '.ico') ? 'x-icon' : 'png')); ?>" href="<?php echo e(asset('storage/' . $faviconPath)); ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>📚</text></svg>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="font-sans antialiased bg-surface-50">
    <div class="flex min-h-screen">
        <div class="hidden lg:flex lg:w-[55%] relative bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700 items-center justify-center p-12 overflow-hidden">
            <div class="absolute inset-0 opacity-[0.03] bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAyNHYySDI0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] bg-repeat"></div>
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>

            <div class="relative max-w-lg text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm shadow-soft-lg mb-8 ring-1 ring-white/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-white mb-3 tracking-tight">
                    <?php echo e(config('app.name', 'OLLMCHS Library')); ?>

                </h1>
                <p class="text-primary-200/80 text-base leading-relaxed mb-12">
                    Our Lady of Lourdes Mutomo College of Health Sciences
                </p>

                <div class="grid grid-cols-3 gap-6 text-left">
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/10">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <p class="text-white text-sm font-semibold">10,000+</p>
                        <p class="text-primary-200/60 text-xs mt-0.5">Books & Resources</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/10">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-white text-sm font-semibold">5,000+</p>
                        <p class="text-primary-200/60 text-xs mt-0.5">Active Members</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/10">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="text-white text-sm font-semibold">Digital</p>
                        <p class="text-primary-200/60 text-xs mt-0.5">Library Access</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <?php echo e($slot); ?>


                <p class="text-center text-xs text-surface-400 mt-10">
                    &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\resources\views/layouts/guest.blade.php ENDPATH**/ ?>