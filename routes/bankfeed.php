<?php

use Illuminate\Support\Facades\Route;
use App\Modules\BankFeed\Http\Controllers\BankImportController;
use App\Modules\BankFeed\Http\Controllers\BankRuleController;

// Bank Related Routes
Route::get('/bank-feed/import', [BankImportController::class, 'index'])->name('bankfeed.import.form');
Route::post('/bank-feed/import', [BankImportController::class, 'upload'])->name('bankfeed.import.upload');
Route::post('/bank-feed/post', [BankImportController::class, 'postSelected'])->name('bankfeed.post');
Route::post('/bank-feed/post-one/{id}', [BankImportController::class, 'postOne'])->name('bankfeed.post.one');
Route::post('/accountmasters/quick-add', [BankImportController::class, 'quickAdd'])->name('accountmasters.quickAdd');
Route::post('/bank-feed/rules/from-transaction', [BankImportController::class, 'storeFromTransaction'])->name('bank-rules.store-from-transaction');
Route::post('/bank-feed/apply-rules', [BankImportController::class, 'applyRulesToUnposted'])->name('bankfeed.applyRules');
Route::post('/bank-feed/ignore-selected', [BankImportController::class, 'ignoreSelected'])->name('bankfeed.ignore');

Route::prefix('bank-rules')->name('bank-rules.')->group(function () {
    Route::get('/', [BankRuleController::class, 'index'])->name('index');
    Route::get('/search', [BankRuleController::class, 'BankRulesAccountSearch'])->name('accounts.search');
    Route::post('/', [BankRuleController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [BankRuleController::class, 'edit'])->name('edit');
    Route::put('/{id}', [BankRuleController::class, 'update'])->name('update');
});

// already existing from Learn button:
Route::post('/bank-feed/rules/from-transaction', [BankRuleController::class, 'storeFromTransaction'])
    ->name('bank-rules.store-from-transaction');



