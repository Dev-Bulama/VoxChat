<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // did, heygen, tavus, simli, openai
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('api_config')->nullable(); // encrypted API keys and settings
            $table->unsignedInteger('monthly_limit')->nullable();
            $table->unsignedInteger('monthly_usage')->default(0);
            $table->unsignedInteger('daily_limit')->nullable();
            $table->unsignedInteger('daily_usage')->default(0);
            $table->timestamp('usage_reset_at')->nullable();
            $table->json('capabilities')->nullable();
            $table->integer('priority')->default(0);
            $table->string('fallback_provider')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('call_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('feature'); // face_replacement, avatar, lip_sync
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->enum('status', ['success', 'failed', 'timeout'])->default('success');
            $table->string('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider', 'created_at']);
            $table->index(['provider', 'status', 'created_at']);
        });

        Schema::create('user_ai_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('portrait_url')->nullable();
            $table->string('face_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_avatar_id')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_processed')->default(false);
            $table->string('processing_status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ai_avatars');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_provider_settings');
    }
};
