<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->text('submission_text')->nullable()->after('notes');
            $table->string('attachment_url')->nullable()->after('submission_text');
            $table->integer('score')->nullable()->after('attachment_url');
            $table->text('feedback')->nullable()->after('score');
            $table->string('subject', 100)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->dropColumn(['submission_text', 'attachment_url', 'score', 'feedback', 'subject']);
        });
    }
};
