<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PainAssessmentController;

Route::prefix('daycareform')->group(function () {
    // Dashboard (optional)
    Route::get('pain', [PainAssessmentController::class, 'PainDashboard'])->name('pain.dashboard');

    // Index + Create/Edit
    Route::get('pain-assessment', [PainAssessmentController::class, 'index'])->name('pain.index');
    Route::get('pain-assessment/create', [PainAssessmentController::class, 'create'])->name('pain.create');
    Route::post('pain-assessment/store', [PainAssessmentController::class, 'store'])->name('pain.store');
     // Report screen (Morning/Noon + dropdown)
    Route::get('pain-assessment/report', [PainAssessmentController::class, 'report'])->name('pain.report');

    Route::get('pain-assessment/{id}/edit', [PainAssessmentController::class, 'edit'])->name('pain.edit');
    Route::post('pain-assessment/{id}/update', [PainAssessmentController::class, 'update'])->name('pain.update');

    // View + PDF
    Route::get('pain-assessment/{id}', [PainAssessmentController::class, 'show'])->name('pain.show');
    Route::get('pain-assessment/{id}/pdf', [PainAssessmentController::class, 'pdf'])->name('pain.pdf');


});
