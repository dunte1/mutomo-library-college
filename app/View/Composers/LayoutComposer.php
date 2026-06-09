<?php

namespace App\View\Composers;

use App\Modules\Notifications\Models\InAppNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with('unreadNotificationCount', $user
            ? InAppNotification::where('user_id', $user->id)->where('is_read', false)->count()
            : 0);
    }
}
