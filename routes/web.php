<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\EmergencyFundController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Google Auth
Route::get('auth/google', [App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback']);
Route::post('auth/google/onetap', [App\Http\Controllers\Auth\SocialiteController::class, 'handleOneTapCallback'])->name('auth.google.onetap');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');


// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cashflow', [DashboardController::class, 'getCashflowData'])->name('dashboard.cashflow');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // Notifications
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // Finance Module
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/create', [FinanceController::class, 'create'])->name('create');
        Route::post('/', [FinanceController::class, 'store'])->name('store');
        Route::get('/{transaction}', [FinanceController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [FinanceController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [FinanceController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [FinanceController::class, 'destroy'])->name('destroy');
        Route::get('/analytics', [FinanceController::class, 'analytics'])->name('analytics');
    });

    // Emergency Fund
    Route::prefix('emergency-fund')->name('emergency-fund.')->group(function () {
        Route::get('/', [EmergencyFundController::class, 'index'])->name('index');
        Route::post('/update-target', [EmergencyFundController::class, 'updateTarget'])->name('update-target');
        Route::post('/add-contribution', [EmergencyFundController::class, 'addContribution'])->name('add-contribution');
        Route::post('/withdraw', [EmergencyFundController::class, 'withdraw'])->name('withdraw');
        Route::post('/auto-update', [EmergencyFundController::class, 'autoUpdate'])->name('auto-update');
    });

    // Recurring Expenses (Kebutuhan Pokok)
    Route::prefix('recurring')->name('recurring.')->group(function () {
        Route::get('/', [RecurringExpenseController::class, 'index'])->name('index');
        Route::get('/create', [RecurringExpenseController::class, 'create'])->name('create');
        Route::post('/', [RecurringExpenseController::class, 'store'])->name('store');
        Route::get('/{recurring}/edit', [RecurringExpenseController::class, 'edit'])->name('edit');
        Route::put('/{recurring}', [RecurringExpenseController::class, 'update'])->name('update');
        Route::delete('/{recurring}', [RecurringExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/{recurring}/mark-paid', [RecurringExpenseController::class, 'markAsPaid'])->name('mark-paid');
        Route::post('/{recurring}/toggle-active', [RecurringExpenseController::class, 'toggleActive'])->name('toggle-active');
    });

    // Vault (Password Manager)
    Route::prefix('vault')->name('vault.')->group(function () {
        Route::get('/', [VaultController::class, 'index'])->name('index');
        Route::get('/create', [VaultController::class, 'create'])->name('create');
        Route::post('/', [VaultController::class, 'store'])->name('store');
        Route::get('/{vault}', [VaultController::class, 'show'])->name('show');
        Route::get('/{vault}/edit', [VaultController::class, 'edit'])->name('edit');
        Route::put('/{vault}', [VaultController::class, 'update'])->name('update');
        Route::delete('/{vault}', [VaultController::class, 'destroy'])->name('destroy');
        Route::post('/generate-password', [VaultController::class, 'generatePassword'])->name('generate-password');
        Route::post('/check-strength', [VaultController::class, 'checkStrength'])->name('check-strength');
        Route::get('/export/data', [VaultController::class, 'export'])->name('export');
    });

    // Notes
    Route::prefix('notes')->name('notes.')->group(function () {
        Route::get('/', [NoteController::class, 'index'])->name('index');
        Route::get('/create', [NoteController::class, 'create'])->name('create');
        Route::post('/', [NoteController::class, 'store'])->name('store');
        Route::get('/{note}', [NoteController::class, 'show'])->name('show');
        Route::get('/{note}/edit', [NoteController::class, 'edit'])->name('edit');
        Route::put('/{note}', [NoteController::class, 'update'])->name('update');
        Route::delete('/{note}', [NoteController::class, 'destroy'])->name('destroy');
        Route::post('/{note}/toggle-pin', [NoteController::class, 'togglePin'])->name('toggle-pin');
        Route::post('/{note}/add-tag', [NoteController::class, 'addTag'])->name('add-tag');
        Route::post('/{note}/remove-tag', [NoteController::class, 'removeTag'])->name('remove-tag');
        Route::get('/trash/list', [NoteController::class, 'trash'])->name('trash');
        Route::post('/trash/{id}/restore', [NoteController::class, 'restore'])->name('restore');
        Route::delete('/trash/{id}/force-delete', [NoteController::class, 'forceDelete'])->name('force-delete');
    });

    // Tasks (Agenda)
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/{task}/complete', [TaskController::class, 'complete'])->name('complete');
        Route::post('/{task}/start', [TaskController::class, 'start'])->name('start');
        Route::get('/calendar/view', [TaskController::class, 'calendar'])->name('calendar');
    });

    // AI Assistant
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AIController::class, 'index'])->name('index');
        Route::post('/chat', [AIController::class, 'chat'])->name('chat');
        Route::post('/voice-chat', [App\Http\Controllers\AIVoiceController::class, 'chat'])->name('voice-chat');
        Route::post('/financial-advice', [AIController::class, 'financialAdvice'])->name('financial-advice');
        Route::post('/purchase-advice', [AIController::class, 'purchaseAdvice'])->name('purchase-advice');
        Route::get('/history', [AIController::class, 'history'])->name('history');
        Route::get('/sessions', [AIController::class, 'sessions'])->name('sessions');
        Route::post('/delete-session', [AIController::class, 'deleteSession'])->name('delete-session');
        Route::post('/pin-session', [AIController::class, 'togglePinSession'])->name('pin-session');
        Route::post('/voice-command', [App\Http\Controllers\VoiceCommandController::class, 'process'])->name('voice-command');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/general/ajax', [SettingController::class, 'updateGeneralAjax'])->name('update-general-ajax');
        Route::post('/general', [SettingController::class, 'updateGeneral'])->name('update-general');
        Route::post('/notifications', [SettingController::class, 'updateNotifications'])->name('update-notifications');
        Route::post('/security', [SettingController::class, 'updateSecurity'])->name('update-security');
        Route::post('/profile', [SettingController::class, 'updateProfile'])->name('update-profile');
        Route::post('/change-password', [SettingController::class, 'changePassword'])->name('change-password');
        Route::post('/preference', [SettingController::class, 'setPreference'])->name('set-preference');
        Route::get('/preference', [SettingController::class, 'getPreference'])->name('get-preference');
    });

    // Categories
    Route::resource('categories', App\Http\Controllers\ExpenseCategoryController::class);

    // Digital Accounts
    Route::resource('digital-accounts', App\Http\Controllers\DigitalAccountController::class)->except(['create', 'edit', 'show']);
    Route::post('digital-accounts/{digitalAccount}/withdraw', [App\Http\Controllers\DigitalAccountController::class, 'withdraw'])->name('digital-accounts.withdraw');

    // Private Links
    Route::post('/private-links/check-password', [App\Http\Controllers\PrivateLinkController::class, 'checkPassword'])->name('private-links.check-password');
    Route::get('/private-links', [App\Http\Controllers\PrivateLinkController::class, 'index'])->name('private-links.index');
    Route::post('/private-links', [App\Http\Controllers\PrivateLinkController::class, 'store'])->name('private-links.store');
    Route::delete('/private-links/{privateLink}', [App\Http\Controllers\PrivateLinkController::class, 'destroy'])->name('private-links.destroy');
});

