<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

Route::get('/', [DownloadController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/dashboard', [DownloadController::class, 'index'])->name('downloads.index');

Route::middleware(['auth'])->group(function () {
    Route::post('/download', [DownloadController::class, 'store'])->name('downloads.store');
    Route::post('/download/status', [DownloadController::class, 'status'])->name('downloads.status');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
});

Route::post('/webhook/web2m', [WebhookController::class, 'web2m'])->name('webhook.web2m');

Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/google-drive/connect', [\App\Http\Controllers\GoogleDriveController::class, 'redirectToGoogleDrive'])->name('admin.google.drive.connect');
    Route::get('/admin/google-drive/callback', [\App\Http\Controllers\GoogleDriveController::class, 'handleGoogleDriveCallback'])->name('admin.google.drive.callback');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    Route::get('/resources', [AdminController::class, 'resources'])->name('admin.resources');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/settings/google-service-account', [AdminController::class, 'uploadGoogleServiceAccount'])->name('admin.google.upload');
    Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
});
