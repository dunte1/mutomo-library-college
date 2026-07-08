<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'activity_log';
        $indexes = [
            $tableName . '_created_at_index' => [['created_at']],
            $tableName . '_subject_index' => [['subject_type', 'subject_id']],
            $tableName . '_causer_index' => [['causer_type', 'causer_id']],
            $tableName . '_event_index' => [['event']],
        ];

        Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
            foreach ($indexes as $name => $columns) {
                try {
                    $table->index($columns[0], $name);
                } catch (\Throwable $e) {
                    if (! str_contains($e->getMessage(), 'already exists')) {
                        throw $e;
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_created_at_index');
            $table->dropIndex('activity_log_subject_index');
            $table->dropIndex('activity_log_causer_index');
            $table->dropIndex('activity_log_event_index');
        });
    }
};
