<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NursingAssessmentController;

Route::prefix('daycareform/nursing-assessment')->name('nursing-assessment.')->group(function () {

    // List + filter by shift + participant dropdown
    Route::get('/', [NursingAssessmentController::class, 'index'])->name('index');

    // Create/store
    Route::get('/create', [NursingAssessmentController::class, 'create'])->name('create');
    Route::post('/store', [NursingAssessmentController::class, 'store'])->name('store');

    // Report (dropdown + view/print/pdf style page)
    Route::get('/report', [NursingAssessmentController::class, 'report'])->name('report');

    // PDF
    Route::get('/{id}/pdf', [NursingAssessmentController::class, 'pdf'])->name('pdf');

    // Show/Edit/Update  (IMPORTANT: keep these BELOW fixed routes)
    Route::get('/{id}', [NursingAssessmentController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [NursingAssessmentController::class, 'edit'])->name('edit');
    Route::post('/{id}/update', [NursingAssessmentController::class, 'update'])->name('update');
});
