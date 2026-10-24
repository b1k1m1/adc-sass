<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NutritionQuarterlyAssessmentController;


Route::prefix('daycareform')->group(function () {

    // Dashboard (optional)
    Route::get('nutrition', [NutritionQuarterlyAssessmentController::class, 'NutritionDashboard'])->name('nutrition.dashboard');

    // Index + Create/Edit
    Route::get('nutrition-quarterly-assessment', [NutritionQuarterlyAssessmentController::class, 'index'])->name('nutrition.index');

    Route::get('nutrition-quarterly-assessment/create', [NutritionQuarterlyAssessmentController::class, 'create'])->name('nutrition.create');

    Route::post('nutrition-quarterly-assessment/store', [NutritionQuarterlyAssessmentController::class, 'store'])->name('nutrition.store');

    // Report screen (Morning/Noon + dropdown)
    Route::get('nutrition-quarterly-assessment/report', [NutritionQuarterlyAssessmentController::class, 'report'])->name('nutrition.report');

    Route::get('nutrition-quarterly-assessment/{id}/edit', [NutritionQuarterlyAssessmentController::class, 'edit'])->name('nutrition.edit');

    Route::post('nutrition-quarterly-assessment/{id}/update', [NutritionQuarterlyAssessmentController::class, 'update'])->name('nutrition.update');

    // View + PDF
    Route::get('nutrition-quarterly-assessment/{id}', [NutritionQuarterlyAssessmentController::class, 'show'])->name('nutrition.show');

    Route::get('nutrition-quarterly-assessment/{id}/pdf', [NutritionQuarterlyAssessmentController::class, 'pdf'])->name('nutrition.pdf');
});
