<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DaycareMedicineAssessmentController;

Route::prefix('daycareform')->group(function () {

    Route::get('medicine-assessment', [DaycareMedicineAssessmentController::class, 'index'])
        ->name('medicine_assessment.index');

    Route::get('medicine-assessment/create', [DaycareMedicineAssessmentController::class, 'create'])
        ->name('medicine_assessment.create');

    Route::post('medicine-assessment/store', [DaycareMedicineAssessmentController::class, 'store'])
        ->name('medicine_assessment.store');

    Route::get('medicine-assessment/{id}/edit', [DaycareMedicineAssessmentController::class, 'edit'])
        ->name('medicine_assessment.edit');

    Route::post('medicine-assessment/{id}/update', [DaycareMedicineAssessmentController::class, 'update'])
        ->name('medicine_assessment.update');

    // PDF must be before show
    Route::get('medicine-assessment/{id}/pdf', [DaycareMedicineAssessmentController::class, 'pdf'])
        ->name('medicine_assessment.pdf');

    Route::get('medicine-assessment/{id}', [DaycareMedicineAssessmentController::class, 'show'])
        ->name('medicine_assessment.show');
});
