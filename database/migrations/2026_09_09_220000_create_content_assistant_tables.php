<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->text('instructions');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('approved_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('source_type');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('approval_status')->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('applicable_areas')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('content_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->unsignedInteger('expected_revision')->default(0);
            $table->string('content_hash', 64);
            $table->string('status')->default('proposed');
            $table->string('summary');
            $table->json('payload');
            $table->json('validation_errors')->nullable();
            $table->json('sources')->nullable();
            $table->json('warnings')->nullable();
            $table->json('unverified_claims')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();

            $table->index(['target_type', 'target_id', 'status']);
            $table->index(['conversation_id', 'status']);
        });

        Schema::create('content_proposal_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('content_proposals')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('operation');
            $table->timestamps();
        });

        Schema::create('content_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('content_proposals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('token_hash', 64)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->string('revision_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('content_assistant_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_assistant_audit_events');
        Schema::dropIfExists('content_approvals');
        Schema::dropIfExists('content_proposal_operations');
        Schema::dropIfExists('content_proposals');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('approved_sources');
        Schema::dropIfExists('assistant_profiles');
    }
};
