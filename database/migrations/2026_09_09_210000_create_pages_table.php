<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('nav_label')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->json('blocks')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('template')->default('default');
            $table->boolean('index')->default(true);
            $table->boolean('follow')->default(true);
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
