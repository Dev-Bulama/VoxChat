<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('status_message')->default('Hey there! I am using VoxChat.');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('subscription_plan', ['free', 'premium', 'business'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->string('ban_reason')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_online')->default(false);
            // Privacy settings
            $table->enum('last_seen_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('profile_photo_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('about_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('status_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->boolean('read_receipts_enabled')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            // Notification preferences
            $table->boolean('message_notifications')->default(true);
            $table->boolean('call_notifications')->default(true);
            $table->boolean('story_notifications')->default(true);
            $table->boolean('group_notifications')->default(true);
            $table->boolean('email_notifications')->default(true);
            // Appearance
            $table->enum('theme', ['light', 'dark', 'system'])->default('system');
            $table->string('language', 10)->default('en');
            $table->string('font_size', 10)->default('medium');
            // Social links
            $table->string('website')->nullable();
            $table->string('social_provider')->nullable();
            $table->string('social_id')->nullable();
            $table->string('social_token')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_online', 'last_seen_at']);
            $table->index(['subscription_plan', 'subscription_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
