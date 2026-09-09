<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->text('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_roles');
    }
};
