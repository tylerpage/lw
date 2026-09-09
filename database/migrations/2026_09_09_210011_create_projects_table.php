<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('card_summary')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('project_date')->nullable();
            $table->boolean('confidential')->default(false);
            $table->string('client_display_name')->nullable();
            $table->string('role')->nullable();
            $table->text('collaborators')->nullable();
            $table->json('blocks')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('index')->default(true);
            $table->boolean('follow')->default(true);
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
