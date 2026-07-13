<?php

use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\LibraryCardService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $cardService = app(LibraryCardService::class);
            $now = now();

            $users = User::whereDoesntHave('member')->get();

            foreach ($users as $user) {
                $nameParts = explode(' ', $user->name, 2);

                try {
                    $member = Member::create([
                        'user_id' => $user->id,
                        'first_name' => $nameParts[0] ?: $user->name,
                        'last_name' => $nameParts[1] ?? '',
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'admission_number' => $user->admission_number,
                        'department_id' => $user->department_id,
                        'program_id' => $user->program_id,
                        'membership_type' => $user->hasRole('student') ? 'student'
                            : ($user->hasRole('lecturer') ? 'teacher' : 'staff'),
                        'status' => Member::STATUS_ACTIVE,
                        'joined_at' => $user->created_at ?? $now,
                        'registered_by' => $user->id,
                    ]);

                    $cardService->autoIssueCard($member);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function down(): void
    {
        // No rollback — this is a data backfill; removing Member records
        // would break dependent LibraryCard records.
    }
};
