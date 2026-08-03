<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_assets', function (Blueprint $table) {
            $table->boolean('is_external')->default(false)->after('is_featured');
            $table->string('source_url', 1024)->nullable()->after('is_external');

            $table->index('is_external');
        });
    }

    public function down(): void
    {
        Schema::table('digital_assets', function (Blueprint $table) {
            $table->dropIndex(['is_external']);
            $table->dropColumn(['is_external', 'source_url']);
        });
    }
};
