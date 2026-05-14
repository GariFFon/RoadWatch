<?php

use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ComplaintOptionsController;
use App\Http\Controllers\Api\V1\Citizen\ComplaintController;
use App\Http\Controllers\Api\V1\Citizen\FeedbackController;
use App\Http\Controllers\Api\V1\Citizen\MediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (prefixed: /api/v1/...)
| Auth:  Laravel session cookie (auth:sanctum → same session as web)
| Guard: role middleware uses user.role column via CheckRole middleware
|--------------------------------------------------------------------------
*/

// ── Public endpoints ────────────────────────────────────────────────────────
Route::get('/categories',        [CategoryController::class, 'index']);         // GET /api/v1/categories
Route::get('/complaint-options', [ComplaintOptionsController::class, 'index']); // GET /api/v1/complaint-options

// ── Authenticated endpoints ─────────────────────────────────────────────────
Route::middleware(['auth:web'])->group(function () {

    // Who am I?
    Route::get('/me', [MeController::class, 'show']);  // GET /api/v1/me

    // ── Citizen ────────────────────────────────────────────────────────────
    Route::middleware('role:citizen')->prefix('citizen')->name('api.citizen.')->group(function () {

        // Complaints CRUD
        Route::get   ('complaints',                  [ComplaintController::class, 'index']);   // GET    /api/v1/citizen/complaints
        Route::post  ('complaints',                  [ComplaintController::class, 'store']);   // POST   /api/v1/citizen/complaints
        Route::get   ('complaints/{complaint}',      [ComplaintController::class, 'show']);    // GET    /api/v1/citizen/complaints/{id}
        Route::delete('complaints/{complaint}',      [ComplaintController::class, 'destroy']); // DELETE /api/v1/citizen/complaints/{id}

        // Media upload (separate endpoint for chunked / multiple files)
        Route::post('complaints/{complaint}/media', [MediaController::class, 'store']);       // POST   /api/v1/citizen/complaints/{id}/media
        Route::delete('media/{media}',              [MediaController::class, 'destroy']);     // DELETE /api/v1/citizen/media/{id}

        // Feedback on resolved complaints
        Route::post('complaints/{complaint}/feedback', [FeedbackController::class, 'store']); // POST   /api/v1/citizen/complaints/{id}/feedback
    });

    // ── Engineer ───────────────────────────────────────────────────────────
    Route::middleware('role:engineer')->prefix('engineer')->name('api.engineer.')->group(function () {
        Route::get('complaints',              [\App\Http\Controllers\Api\V1\Engineer\ComplaintController::class, 'index']);
        Route::get('complaints/{complaint}',  [\App\Http\Controllers\Api\V1\Engineer\ComplaintController::class, 'show']);
        Route::patch('complaints/{complaint}/status', [\App\Http\Controllers\Api\V1\Engineer\ComplaintController::class, 'updateStatus']);
        // Work evidence upload
        Route::post('complaints/{complaint}/media',   [\App\Http\Controllers\Api\V1\Engineer\MediaController::class, 'store']);
        Route::delete('media/{media}',                [\App\Http\Controllers\Api\V1\Engineer\MediaController::class, 'destroy']);
    });

    // ── Admin ──────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('complaints',                        [\App\Http\Controllers\Api\V1\Admin\ComplaintController::class, 'index']);
        Route::get('complaints/{complaint}',            [\App\Http\Controllers\Api\V1\Admin\ComplaintController::class, 'show']);
        Route::patch('complaints/{complaint}/assign',   [\App\Http\Controllers\Api\V1\Admin\ComplaintController::class, 'assign']);
        Route::patch('complaints/{complaint}/status',   [\App\Http\Controllers\Api\V1\Admin\ComplaintController::class, 'updateStatus']);
        Route::post ('complaints/{complaint}/rate',     [\App\Http\Controllers\Api\V1\Admin\ComplaintController::class, 'rateEngineer']); // POST /api/v1/admin/complaints/{id}/rate
        Route::get('users',                             [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
        Route::patch('users/{user}/role',               [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'updateRole']);
    });
});
