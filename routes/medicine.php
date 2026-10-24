<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DaycareMedicineMaintenanceController;

Route::prefix('daycareform')->group(function () {

    // INDEX
    Route::get('medicine-maintenance', [DaycareMedicineMaintenanceController::class, 'index'])
        ->name('medicine_maint.index');

    // SAVE (replaces rows for selected member)
    Route::post('medicine-maintenance/store', [DaycareMedicineMaintenanceController::class, 'store'])
        ->name('medicine_maint.store');

});
