<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Models\User;
use App\Modules\DigitalLibrary\Models\Recommendation;
use App\Modules\DigitalLibrary\Services\RecommendationEngine;
use Livewire\Component;

class Recommendations extends Component
{
    public ?User $targetUser = null;

    public string $tab = 'all';

    public array $predictiveAlert = [];

    public function mount(?int $userId = null)
    {
        if ($userId) {
            $this->targetUser = User::find($userId);
        }

        if (! $this->targetUser && auth()->check()) {
            $this->targetUser = auth()->user();
        }

        if ($this->targetUser) {
            app(RecommendationEngine::class)->generateForUser($this->targetUser);
            $alert = app(RecommendationEngine::class)->predictiveOverdueAlert($this->targetUser);
            if ($alert !== null) {
                $this->predictiveAlert[] = $alert;
            }
        }
    }

    public function getRecommendationsProperty()
    {
        if (! $this->targetUser) {
            return collect();
        }

        $query = Recommendation::active()->forUser($this->targetUser->id);

        if ($this->tab !== 'all') {
            $query->ofType($this->tab);
        }

        return $query->top(20)->get();
    }

    public function render()
    {
        return view('digital-library::livewire.recommendations', [
            'recommendations' => $this->recommendations,
            'predictiveAlert' => $this->predictiveAlert,
        ])->layout('layouts.app');
    }
}
