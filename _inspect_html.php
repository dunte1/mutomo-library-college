<?php
$base = 'C:\\Users\\Lab IX\\Documents\\proj\\ollmchs-library';
chdir($base);
putenv('APP_ENV=testing');
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->register(\App\Providers\ModulesServiceProvider::class);

auth()->loginUsingId(1);

$lcClass = 'App\\Modules\\Members\\Livewire\\LibraryCard';
$component = new $lcClass(6);
$component->mount();
$view = $component->render();
$html = $view->render();

echo "--- FIRST 800 CHARS ---\n";
echo substr($html, 0, 800) . "\n\n";

echo "--- WIRE CHECKS ---\n";
$checks = ['wire:id', 'wire:click', 'wire:model', 'x-on:click', 'x-model'];
foreach ($checks as $check) {
    $count = substr_count($html, $check);
    echo "$check: $count occurrences\n";
}
// Find wire:id values
preg_match_all('/wire:id\s*=\s*"([^"]*)"/', $html, $ids);
echo "wire:id values: " . implode(', ', $ids[1]) . "\n";
