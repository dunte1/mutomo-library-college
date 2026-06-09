<?php

namespace App\Traits;

trait Auditable
{
    protected function logActivity(string $event, string $description, $performedOn = null): void
    {
        $properties = [];
        if (!app()->runningInConsole()) {
            $properties = [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        $activity = activity()->event($event)->withProperties($properties);

        if ($performedOn) {
            $activity->performedOn($performedOn);
        }

        if (auth()->check()) {
            $activity->causedBy(auth()->user());
        }

        $activity->log($description);
    }
}
