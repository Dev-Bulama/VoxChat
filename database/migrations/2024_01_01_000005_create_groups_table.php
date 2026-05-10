<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('invite_link')->unique()->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('max_members')->default(100);
            // Permissions
            $table->boolean('only_admins_can_send')->default(false);
            $table->boolean('only_admins_can_edit_info')->default(true);
            $table->boolean('only_admins_can_add_members')->default(false);
            $table->boolean('approval_required')->default(false);
            // Call settings
            $table->boolean('voice_calls_enabled')->default(true);
            $table->boolean('video_calls_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_public', 'created_at']);
        });

        Schema::create('group_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('question');
            $table->json('options');
            $table->boolean('is_multiple_choice')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('group_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('group_polls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('selected_options');
            $table->timestamps();

            $table->unique(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_poll_votes');
        Schema::dropIfExists('group_polls');
        Schema::dropIfExists('groups');
    }
};
