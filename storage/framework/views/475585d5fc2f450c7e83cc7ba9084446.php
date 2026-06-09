<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="<?php echo e(session('theme', 'light')); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', config('app.name') . ' - Library Management System'); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'library, books, OLLMCHS, education, digital library, Kenya'); ?>">
    <meta name="theme-color" content="#153168">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo e(config('app.name')); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/icon-72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="/icons/icon-96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="/icons/icon-128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="/icons/icon-384.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512.png">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">

    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> | <?php echo e(config('app.name', 'OLLMCHS Library')); ?></title>

    <meta property="og:title" content="<?php echo $__env->yieldContent('title', 'Dashboard'); ?> | <?php echo e(config('app.name', 'OLLMCHS Library')); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('meta_description', config('app.name') . ' - Library Management System'); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">
    <meta property="og:locale" content="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('title', 'Dashboard'); ?> | <?php echo e(config('app.name', 'OLLMCHS Library')); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('meta_description', config('app.name') . ' - Library Management System'); ?>">

    <?php
        $faviconPath = null;
        try { $faviconPath = \App\Modules\Settings\Models\Setting::value('favicon'); } catch (\Throwable $e) {}
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($faviconPath): ?>
        <link rel="icon" type="image/<?php echo e(str_ends_with($faviconPath, '.svg') ? 'svg+xml' : (str_ends_with($faviconPath, '.ico') ? 'x-icon' : 'png')); ?>" href="<?php echo e(asset('storage/' . $faviconPath)); ?>">
        <meta property="og:image" content="<?php echo e(asset('storage/' . $faviconPath)); ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>📚</text></svg>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="font-sans antialiased text-surface-700 dark:text-surface-200 bg-surface-50 dark:bg-surface-900"
      x-data="{ mobileMenuOpen: false }"
      @toggle-sidebar.window="mobileMenuOpen = !mobileMenuOpen"
      @keydown.escape.window="mobileMenuOpen = false">

    <div class="flex h-screen overflow-hidden">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.sidebar', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3257821637-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.header', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3257821637-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

            <main class="flex-1 overflow-y-auto scrollbar-thin p-4 md:p-6 lg:p-8 pb-24 md:pb-6 lg:pb-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                    <div class="page-header flex items-center justify-between">
                        <div>
                            <h1 class="page-title"><?php echo e($header); ?></h1>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($subtitle)): ?>
                                <p class="page-subtitle"><?php echo e($subtitle); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actions)): ?>
                            <div class="shrink-0"><?php echo e($actions); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo e($slot); ?>

            </main>
        </div>
    </div>

    <?php echo $__env->make('components.layout.mobile-drawer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.layout.mobile-bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.notifications', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3257821637-2', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('global-search');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3257821637-3', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <script>
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                Livewire.dispatch('openGlobalSearch');
            }
            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                e.preventDefault();
                Livewire.dispatch('openGlobalSearch');
            }
        });
    </script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\resources\views/layouts/app.blade.php ENDPATH**/ ?>