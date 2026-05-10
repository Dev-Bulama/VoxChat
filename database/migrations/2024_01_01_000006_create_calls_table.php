<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->foreignId('chat_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['voice', 'video'])->default('voice');
            $table->enum('status', ['ringing', 'ongoing', 'ended', 'missed', 'rejected', 'busy'])->default('ringing');
            $table->string('provider')->default('webrtc'); // webrtc, agora, livekit, twilio
            $table->string('provider_room_id')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_group_call')->default(false);
            // Recording
            $table->boolean('is_recorded')->default(false);
            $table->string('recording_url')->nullable();
            // AI Features
            $table->boolean('ai_face_enabled')->default(false);
            $table->string('ai_provider')->nullable();
            // Stats
            $table->json('quality_stats')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('call_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['ringing', 'joined', 'left', 'rejected', 'missed', 'busy'])->default('ringing');
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_off')->default(false);
            $table->boolean('is_screen_sharing')->default(false);
            $table->boolean('ai_face_enabled')->default(false);
            $table->string('ai_avatar_url')->nullable();
            $table->string('ai_provider')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['call_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_participants');
        Schema::dropIfExists('calls');
    }
};
