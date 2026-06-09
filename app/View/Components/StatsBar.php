<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatsBar extends Component
{
    public function __construct(
        public array $stats,
    ) {}

    public function render()
    {
        return view('components.stats-bar');
    }
}
