<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DietaryInitialAssessmentController;

Route::prefix('daycareform')->group(function () {

    Route::get('dietary-initial-assessment', [DietaryInitialAssessmentController::class, 'index'])
        ->name('dietary.index');

    Route::get('dietary-initial-assessment/create', [DietaryInitialAssessmentController::class, 'create'])
        ->name('dietary.create');

    Route::post('dietary-initial-assessment/store', [DietaryInitialAssessmentController::class, 'store'])
        ->name('dietary.store');

    // Report screen
    Route::get('dietary-initial-assessment/report', [DietaryInitialAssessmentController::class, 'report'])
        ->name('dietary.report');

    Route::get('dietary-initial-assessment/{id}/edit', [DietaryInitialAssessmentController::class, 'edit'])
        ->name('dietary.edit');

    Route::post('dietary-initial-assessment/{id}/update', [DietaryInitialAssessmentController::class, 'update'])
        ->name('dietary.update');

    // View + PDF
    Route::get('dietary-initial-assessment/{id}', [DietaryInitialAssessmentController::class, 'show'])
        ->name('dietary.show');

    Route::get('dietary-initial-assessment/{id}/pdf', [DietaryInitialAssessmentController::class, 'pdf'])
        ->name('dietary.pdf');
});
