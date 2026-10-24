<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DaycarePharmacistReviewController;

Route::prefix('daycareform')->group(function () {

    // INDEX (MUST be first)
    Route::get('pharmacist-review', [DaycarePharmacistReviewController::class, 'index'])
        ->name('pharmacist_review.index');

    // CREATE
    Route::get('pharmacist-review/create', [DaycarePharmacistReviewController::class, 'create'])
        ->name('pharmacist_review.create');

    // STORE
    Route::post('pharmacist-review/store', [DaycarePharmacistReviewController::class, 'store'])
        ->name('pharmacist_review.store');

    // REPORT (must be before {id})
    Route::get('pharmacist-review/report', [DaycarePharmacistReviewController::class, 'report'])
        ->name('pharmacist_review.report');

    // Copy last review (comments only now)
    Route::get('pharmacist-review/last-review/{participantId}', [DaycarePharmacistReviewController::class, 'lastReview'])
        ->name('pharmacist_review.last_review');

    // AJAX snapshot (must be before {id})
    Route::get('pharmacist-review/member/{participantId}/snapshot', [DaycarePharmacistReviewController::class, 'memberSnapshot'])
        ->name('pharmacist_review.member_snapshot');

    // ✅ NEW: Nurse meds read-only grid refresh (must be before {id})
    Route::get('pharmacist-review/member/{participantId}/nurse-meds', [DaycarePharmacistReviewController::class, 'nurseMedsJson'])
        ->name('pharmacist_review.nurse_meds_json');

    // EDIT
    Route::get('pharmacist-review/{id}/edit', [DaycarePharmacistReviewController::class, 'edit'])
        ->name('pharmacist_review.edit');

    // UPDATE
    Route::post('pharmacist-review/{id}/update', [DaycarePharmacistReviewController::class, 'update'])
        ->name('pharmacist_review.update');

    // ✅ PDF must be ABOVE show
    Route::get('pharmacist-review/{id}/pdf', [DaycarePharmacistReviewController::class, 'pdf'])
        ->name('pharmacist_review.pdf');

    // VIEW (keep last)
    Route::get('pharmacist-review/{id}', [DaycarePharmacistReviewController::class, 'show'])
        ->name('pharmacist_review.show');

});
