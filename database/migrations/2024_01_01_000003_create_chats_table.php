<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group', 'broadcast'])->default('private');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('last_message_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'created_at']);
        });

        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['member', 'admin', 'owner'])->default('member');
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->foreignId('last_read_message_id')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_id', 'user_id']);
            $table->index(['user_id', 'is_archived', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chats');
    }
};
