<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE books ADD FULLTEXT INDEX ft_books_title_description (title, description)');

        DB::statement('ALTER TABLE members ADD FULLTEXT INDEX ft_members_names (first_name, last_name)');

        DB::statement('ALTER TABLE digital_assets ADD FULLTEXT INDEX ft_digital_assets_title_author (title, author)');

        DB::statement('ALTER TABLE users ADD FULLTEXT INDEX ft_users_name_email (name, email)');

        foreach (['authors', 'publishers', 'categories', 'subjects'] as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ADD FULLTEXT INDEX ft_{$table}_name (name)");
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE books DROP INDEX ft_books_title_description');
        DB::statement('ALTER TABLE members DROP INDEX ft_members_names');
        DB::statement('ALTER TABLE digital_assets DROP INDEX ft_digital_assets_title_author');
        DB::statement('ALTER TABLE users DROP INDEX ft_users_name_email');

        foreach (['authors', 'publishers', 'categories', 'subjects'] as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} DROP INDEX ft_{$table}_name");
            }
        }
    }
};
