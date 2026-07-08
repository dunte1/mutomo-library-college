<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class ScheduledTokenCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_expired_tokens_is_scheduled(): void
    {
        $events = Schedule::events();
        $found = false;

        foreach ($events as $event) {
            $command = $event->command ?? '';
            if (str_contains($command, 'sanctum:prune-expired')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'sanctum:prune-expired should be scheduled');
    }
}
