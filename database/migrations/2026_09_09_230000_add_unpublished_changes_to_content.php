<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('has_unpublished_changes')->default(false)->after('status');
            $table->foreignId('published_revision_id')->nullable()->after('has_unpublished_changes')->constrained('page_revisions')->nullOnDelete();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('has_unpublished_changes')->default(false)->after('status');
            $table->foreignId('published_revision_id')->nullable()->after('has_unpublished_changes')->constrained('post_revisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_revision_id');
            $table->dropColumn('has_unpublished_changes');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_revision_id');
            $table->dropColumn('has_unpublished_changes');
        });
    }
};
