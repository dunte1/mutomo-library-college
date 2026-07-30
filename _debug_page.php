<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate auth manually
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
auth()->loginUsingId(1);

$memberClass = 'App\\Modules\\Members\\Models\\Member';
$lcClass = 'App\\Modules\\Members\\Livewire\\LibraryCard';

$member = $memberClass::find(6);
$component = new $lcClass(6);
$component->mount();
$view = $component->render();
$html = $view->render();

echo "=== FIRST 500 CHARS ===\n";
echo substr($html, 0, 500) . "\n\n";

echo "=== WIRE:ID CHECK ===\n";
preg_match_all('/wire:id\s*=\s*"([^"]*)"/', $html, $matches);
if (!empty($matches[0])) {
    foreach ($matches[0] as $m) {
        echo "Found: $m\n";
    }
} else {
    echo "NO wire:id FOUND IN COMPONENT VIEW\n";
}
echo "Count: " . count($matches[0]) . "\n\n";

echo "=== WIRE:CLICK CHECK ===\n";
preg_match_all('/wire:click\s*=\s*"([^"]*)"/', $html, $clicks);
if (!empty($clicks[0])) {
    foreach ($clicks[0] as $c) {
        echo "  $c\n";
    }
} else {
    echo "NO wire:click found\n";
}

echo "=== WIRE:MODEL CHECK ===\n";
preg_match_all('/wire:model\s*=\s*"([^"]*)"/', $html, $models);
if (!empty($models[0])) {
    foreach ($models[0] as $m) {
        echo "  $m\n";
    }
} else {
    echo "NO wire:model found\n";
}
