<?php

namespace App\Modules\DigitalLibrary\Observers;

use App\Modules\DigitalLibrary\Models\DigitalAsset;

class DigitalAssetObserver
{
    public function created(DigitalAsset $asset): void
    {
        activity()
            ->performedOn($asset)
            ->causedBy(auth()->user())
            ->log("Uploaded digital asset: {$asset->title}");
    }

    public function deleted(DigitalAsset $asset): void
    {
        activity()
            ->performedOn($asset)
            ->causedBy(auth()->user())
            ->log("Deleted digital asset: {$asset->title}");
    }
}
