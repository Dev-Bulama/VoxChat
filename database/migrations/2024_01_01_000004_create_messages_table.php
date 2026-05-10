<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->enum('type', [
                'text', 'image', 'video', 'audio', 'voice_note',
                'file', 'location', 'contact', 'gif', 'sticker',
                'call_log', 'system'
            ])->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_thumbnail')->nullable();
            $table->unsignedInteger('media_size')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->unsignedInteger('media_duration')->nullable(); // seconds for audio/video
            $table->json('metadata')->nullable();        // extra data (location coords, gif data, etc.)
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->enum('deleted_type', ['for_me', 'for_everyone'])->nullable();
            $table->boolean('is_forwarded')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->timestamp('expires_at')->nullable(); // for disappearing messages
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_id', 'created_at']);
            $table->index(['sender_id', 'chat_id']);
            $table->index('reply_to_id');
        });

        // Track message read/delivery status per user
        Schema::create('message_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        // Message reactions
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 20);
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('message_receipts');
        Schema::dropIfExists('messages');
    }
};
