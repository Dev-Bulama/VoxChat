<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'))->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');

    Route::get('/forgot-password',        [PasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',       [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',        [PasswordController::class, 'resetPassword'])->name('password.update');

    Route::get('/auth/{provider}',          [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Chats
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/',              [ChatController::class, 'index'])->name('index');
        Route::post('/',             [ChatController::class, 'startPrivateChat'])->name('create');
        Route::get('/search',        [ChatController::class, 'search'])->name('search');
        Route::get('/group/new',     [ChatController::class, 'showCreateGroup'])->name('group.new');
        Route::post('/group',        [ChatController::class, 'createGroup'])->name('group.create');
        Route::get('/{chat}',        [ChatController::class, 'show'])->name('show');
        Route::post('/{chat}/archive',[ChatController::class, 'archive'])->name('archive');
        Route::post('/{chat}/pin',   [ChatController::class, 'pin'])->name('pin');
        Route::post('/{chat}/mute',  [ChatController::class, 'mute'])->name('mute');
        Route::delete('/{chat}',     [ChatController::class, 'delete'])->name('delete');
    });

    // Messages
    Route::prefix('chats/{chat}/messages')->name('messages.')->group(function () {
        Route::post('/',                    [MessageController::class, 'send'])->name('send');
        Route::patch('/{message}',          [MessageController::class, 'edit'])->name('edit');
        Route::delete('/{message}',         [MessageController::class, 'delete'])->name('delete');
        Route::post('/{message}/react',     [MessageController::class, 'react'])->name('react');
        Route::post('/{message}/forward',   [MessageController::class, 'forward'])->name('forward');
        Route::post('/{message}/pin',       [MessageController::class, 'pin'])->name('pin');
        Route::post('/typing',              [MessageController::class, 'typing'])->name('typing');
        Route::post('/read',                [MessageController::class, 'markRead'])->name('read');
        Route::get('/poll',                 [MessageController::class, 'poll'])->name('poll');
    });

    // Calls
    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/',                     [CallController::class, 'index'])->name('index');
        Route::post('/initiate',            [CallController::class, 'initiate'])->name('initiate');
        Route::get('/pending',              [CallController::class, 'pending'])->name('pending');
        Route::get('/{call}/room',          [CallController::class, 'showRoom'])->name('room');
        Route::post('/{call}/join',         [CallController::class, 'join'])->name('join');
        Route::post('/{call}/answer',       [CallController::class, 'answer'])->name('answer');
        Route::post('/{call}/reject',       [CallController::class, 'reject'])->name('reject');
        Route::post('/{call}/end',          [CallController::class, 'end'])->name('end');
        Route::patch('/{call}/media',       [CallController::class, 'updateMedia'])->name('media.update');
        Route::post('/{call}/ai-face',      [CallController::class, 'enableAiFace'])->name('ai_face');
    });

    // Status/Stories
    Route::prefix('status')->name('status.')->group(function () {
        Route::get('/',                     [StoryController::class, 'index'])->name('index');
        Route::post('/',                    [StoryController::class, 'store'])->name('store');
        Route::get('/{story}/view',         [StoryController::class, 'view'])->name('view');
        Route::post('/{story}/react',       [StoryController::class, 'react'])->name('react');
        Route::post('/{story}/reply',       [StoryController::class, 'reply'])->name('reply');
        Route::delete('/{story}',           [StoryController::class, 'destroy'])->name('destroy');
        Route::get('/{story}/viewers',      [StoryController::class, 'viewers'])->name('viewers');
    });

    // Communities
    Route::resource('communities', CommunityController::class);
    Route::post('/communities/{community}/join',  [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/communities/{community}/leave', [CommunityController::class, 'leave'])->name('communities.leave');

    // Profile
    Route::get('/profile/edit',                     [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',                          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar',                  [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profile/cover',                   [ProfileController::class, 'updateCover'])->name('profile.cover');
    Route::put('/profile/privacy',                  [ProfileController::class, 'updatePrivacy'])->name('profile.privacy');
    Route::put('/profile/notifications',            [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    Route::post('/profile/theme',                   [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::post('/contacts/{user}',                 [ProfileController::class, 'addContact'])->name('contacts.add');
    Route::delete('/contacts/{user}',               [ProfileController::class, 'removeContact'])->name('contacts.remove');
    Route::post('/contacts/{user}/block',           [ProfileController::class, 'blockUser'])->name('contacts.block');
    Route::post('/contacts/{user}/unblock',         [ProfileController::class, 'unblockUser'])->name('contacts.unblock');
    Route::get('/u/{username}',                     [ProfileController::class, 'show'])->name('profile.show');

    // Settings
    Route::get('/settings',             [ProfileController::class, 'settings'])->name('settings.index');
    Route::get('/settings/password',    [PasswordController::class, 'showChangeForm'])->name('settings.password');
    Route::post('/settings/password',   [PasswordController::class, 'changePassword'])->name('password.change');

    // Subscription
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/',                     [SubscriptionController::class, 'index'])->name('index');
        Route::post('/checkout',            [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::get('/success',              [SubscriptionController::class, 'success'])->name('success');
        Route::get('/cancel',               [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('cancel-sub');
        Route::get('/billing',              [SubscriptionController::class, 'billing'])->name('billing');
    });

    // Webhooks (no CSRF)
    Route::post('/webhooks/stripe',      [SubscriptionController::class, 'stripeWebhook'])->name('webhooks.stripe')->withoutMiddleware(['verified']);
    Route::post('/webhooks/paystack',    [SubscriptionController::class, 'paystackWebhook'])->name('webhooks.paystack')->withoutMiddleware(['verified']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',            [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                 [Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}',           [Admin\UserController::class, 'show'])->name('show');
        Route::patch('/{user}/ban',     [Admin\UserController::class, 'ban'])->name('ban');
        Route::patch('/{user}/unban',   [Admin\UserController::class, 'unban'])->name('unban');
        Route::patch('/{user}/verify',  [Admin\UserController::class, 'verify'])->name('verify');
        Route::delete('/{user}',        [Admin\UserController::class, 'destroy'])->name('destroy');
    });

    // Subscriptions
    Route::get('/subscriptions',        [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::resource('plans',            Admin\PlanController::class);

    // Calls
    Route::get('/calls',                [Admin\CallController::class, 'index'])->name('calls.index');

    // API Management
    Route::get('/api-config',           [Admin\ApiConfigController::class, 'index'])->name('api-config.index');
    Route::put('/api-config/{provider}', [Admin\ApiConfigController::class, 'update'])->name('api-config.update');

    // Settings
    Route::get('/settings',             [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings',            [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Analytics
    Route::get('/analytics',            [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data',       [Admin\AnalyticsController::class, 'data'])->name('analytics.data');

    // Reports
    Route::get('/reports',              [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}',   [Admin\ReportController::class, 'resolve'])->name('reports.resolve');
});
