<?php

namespace App\Modules\Reports\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use App\Modules\DigitalLibrary\Models\Recommendation;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Livewire\Component;

class DigitalLibraryReports extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_assets' => DigitalAsset::count(),
            'total_downloads' => DigitalAsset::sum('download_count'),
            'total_views' => ReadingHistory::count(),
            'active_recommendations' => Recommendation::where('is_active', true)->count(),
            'total_categories' => DigitalAssetCategory::count(),
            'most_viewed' => DigitalAsset::orderBy('view_count', 'desc')->first()?->title ?? 'N/A',
            'most_downloaded' => DigitalAsset::orderBy('download_count', 'desc')->first()?->title ?? 'N/A',
        ];
    }

    public function render()
    {
        return view('reports::livewire.digital-library-reports');
    }
}
