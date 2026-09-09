<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_revisions', function (Blueprint $table) {
            $table->string('label')->nullable()->after('user_id');
            $table->string('source')->nullable()->after('label');
            $table->json('snapshot')->nullable()->after('blocks');
        });

        Schema::table('post_revisions', function (Blueprint $table) {
            $table->string('label')->nullable()->after('user_id');
            $table->string('source')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('page_revisions', function (Blueprint $table) {
            $table->dropColumn(['label', 'source', 'snapshot']);
        });

        Schema::table('post_revisions', function (Blueprint $table) {
            $table->dropColumn(['label', 'source']);
        });
    }
};
