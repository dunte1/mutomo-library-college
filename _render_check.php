<?php
$base = 'C:\\Users\\Lab IX\\Documents\\proj\\ollmchs-library';
chdir($base);
require $base . '/vendor/autoload.php';

$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Register needed providers
$app->register(Illuminate\Auth\AuthServiceProvider::class);
$app->register(Livewire\LivewireServiceProvider::class);

// Login
auth()->loginUsingId(1);

// Create LibraryCard component and render
$ns = 'App\\Modules\\Members\\Livewire\\';
$class = $ns . 'LibraryCard';
$component = new $class(6);
$component->mount();
$html = $component->render()->render();

// Check wire:id
if (preg_match('/wire:id\s*=\s*"([^"]+)"/', $html, $m)) {
    echo "wire:id: " . $m[1] . "\n";
} else {
    echo "NO wire:id found!\n";
}

// Find what element has wire:id
if (preg_match('/<([a-zA-Z0-9_-]+)[^>]*wire:id\s*=\s*"/', $html, $tag)) {
    echo "wire:id is on element: <" . $tag[1] . ">\n";
}

echo "\n--- FIRST 500 chars of HTML ---\n";
echo substr($html, 0, 500) . "\n";

echo "\n--- wire:click count: " . substr_count($html, 'wire:click') . "\n";
echo "--- wire:model count: " . substr_count($html, 'wire:model') . "\n";
echo "--- x-on:click count: " . substr_count($html, 'x-on:click') . "\n";
echo "--- x-model count: " . substr_count($html, 'x-model') . "\n";
