<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('display_updated_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('featured')->default(false);
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();
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
        Schema::dropIfExists('posts');
    }
};
