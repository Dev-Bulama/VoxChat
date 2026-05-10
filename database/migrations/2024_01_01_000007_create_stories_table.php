<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video', 'text', 'music'])->default('text');
            $table->text('content')->nullable();      // text content or caption
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('font_style')->nullable();
            $table->string('link')->nullable();
            $table->string('music_url')->nullable();
            $table->string('music_title')->nullable();
            $table->string('music_artist')->nullable();
            $table->enum('privacy', ['everyone', 'contacts', 'close_friends', 'custom'])->default('everyone');
            $table->json('allowed_viewers')->nullable();
            $table->json('excluded_viewers')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'expires_at']);
            $table->index(['expires_at']);
        });

        Schema::create('story_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('reaction')->nullable();
            $table->string('reply_text')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['story_id', 'viewer_id']);
            $table->index(['story_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_views');
        Schema::dropIfExists('stories');
    }
};
