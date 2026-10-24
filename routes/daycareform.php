<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialServiceQuarterlyAssessmentController;

Route::prefix('daycareform')->group(function () {

    Route::get('/ss-quarterly/dashboard', [SocialServiceQuarterlyAssessmentController::class,'SSDashboard'])->name('social.dashboard');

    Route::get('ss-quarterly', [SocialServiceQuarterlyAssessmentController::class, 'index'])->name('ss-quarterly.index');

    // New assessment (member selected via query string)
    Route::get('ss-quarterly/create', [SocialServiceQuarterlyAssessmentController::class, 'create'])->name('ss-quarterly.create');

    // Store
    Route::post('ss-quarterly', [SocialServiceQuarterlyAssessmentController::class, 'store'])->name('ss-quarterly.store');

    Route::get('ss-quarterly/report', [SocialServiceQuarterlyAssessmentController::class, 'report'])->name('ss-quarterly.report');

    // show (view-only screen)
    Route::get('ss-quarterly/{id}', [SocialServiceQuarterlyAssessmentController::class, 'show'])->name('ss-quarterly.show');

    // Edit existing
    Route::get('ss-quarterly/{id}/edit', [SocialServiceQuarterlyAssessmentController::class, 'edit'])->name('ss-quarterly.edit');

    // Update
    Route::put('ss-quarterly/{id}', [SocialServiceQuarterlyAssessmentController::class, 'update'])->name('ss-quarterly.update');

    Route::get('ss-quarterly/{id}/pdf', [SocialServiceQuarterlyAssessmentController::class, 'pdf'])->name('ss-quarterly.pdf');



});
