<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use Livewire\Component;
use Livewire\WithPagination;

class DownloadsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sort = 'downloads';
    public string $direction = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'downloads'],
        'direction' => ['except' => 'desc'],
    ];

    public function render()
    {
        $query = DigitalAsset::with('category');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('author', 'like', "%{$this->search}%");
            });
        }

        $assets = $query->orderBy($this->sort, $this->direction)->paginate(15);

        $stats = [
            'total_downloads' => DigitalAsset::sum('times_downloaded'),
            'total_assets' => DigitalAsset::count(),
            'most_downloaded' => DigitalAsset::orderBy('times_downloaded', 'desc')->first(),
        ];

        return view('digital-library::livewire.downloads-list', [
            'assets' => $assets,
            'stats' => $stats,
        ]);
    }
}
