<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('student_id')->nullable()->unique()->after('member_id');
            $table->string('blood_group')->nullable()->after('class');
        });

        // Backfill student_id for existing members (OLLMCHS-{year}-{seq})
        DB::table('members')
            ->whereNull('student_id')
            ->whereNotNull('id')
            ->orderBy('id')
            ->chunkById(500, function ($members) {
                foreach ($members as $member) {
                    $year = $member->created_at
                        ? Carbon::parse($member->created_at)->format('Y')
                        : now()->format('Y');

                    $studentId = 'OLLMCHS-'.$year.'-'.str_pad($member->id, 4, '0', STR_PAD_LEFT);

                    DB::table('members')
                        ->where('id', $member->id)
                        ->update(['student_id' => $studentId]);
                }
            }, 'id');
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->dropColumn(['student_id', 'blood_group']);
        });
    }
};
