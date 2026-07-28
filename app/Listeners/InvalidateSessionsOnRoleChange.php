<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;

class InvalidateSessionsOnRoleChange implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RoleAttachedEvent|RoleDetachedEvent|PermissionAttachedEvent|PermissionDetachedEvent $event): void
    {
        $model = $event->model;

        if (!method_exists($model, 'id') || !$model->id) {
            return;
        }

        $currentSessionId = Session::getId();
        $currentUserId = Auth::id();

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $model->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}
