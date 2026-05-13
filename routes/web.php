<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Citizen\ComplaintController as CitizenComplaintController;
use App\Http\Controllers\Citizen\FeedbackController as CitizenFeedbackController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Public landing page ────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Google OAuth ───────────────────────────────────────────────────────────
Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// ── Mandatory set-password (Google new users) ──────────────────────────────
// Must be auth'd but NOT behind EnsurePasswordIsSet (would loop)
Route::middleware('auth')->group(function () {
    Route::get( '/set-password', [SetPasswordController::class, 'show']) ->name('password.setup');
    Route::post('/set-password', [SetPasswordController::class, 'store'])->name('password.setup.store');
});

// ── Authenticated routes (password setup enforced) ─────────────────────────
// Note: 'verified' is omitted — Google OAuth users have email pre-verified by Google.
// Email verification is required only for email/password registrations via a middleware check.
Route::middleware(['auth', 'password.setup'])->group(function () {

    // Profile (Breeze default)
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Citizen panel ──────────────────────────────────────────────────
    Route::middleware('role:citizen')->prefix('citizen')->name('citizen.')->group(function () {
        Route::get('complaints',             [CitizenComplaintController::class, 'index']) ->name('complaints.index');
        Route::get('complaints/create',      [CitizenComplaintController::class, 'create'])->name('complaints.create');
        Route::post('complaints',            [CitizenComplaintController::class, 'store']) ->name('complaints.store');
        Route::get('complaints/{complaint}', [CitizenComplaintController::class, 'show'])  ->name('complaints.show');
        Route::post('complaints/{complaint}/feedback', [CitizenFeedbackController::class, 'store'])->name('complaints.feedback');
        Route::get('profile',               fn() => view('citizen.profile'))->name('profile');
    });

    // ── Engineer panel ─────────────────────────────────────────────────
    Route::middleware('role:engineer')->prefix('engineer')->name('engineer.')->group(function () {
        Route::get('complaints',             fn() => view('engineer.complaints.index'))->name('complaints.index');
        Route::get('complaints/{complaint}', fn() => view('engineer.complaints.show')) ->name('complaints.show');
    });

    // ── Admin panel ────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard',   fn() => view('admin.dashboard'))          ->name('dashboard');
        Route::get('complaints',  fn() => view('admin.complaints.index'))   ->name('complaints.index');
        Route::get('users',       fn() => view('admin.users.index'))        ->name('users.index');
        Route::get('categories',  fn() => view('admin.categories.index'))   ->name('categories.index');
    });
});

require __DIR__.'/auth.php';

