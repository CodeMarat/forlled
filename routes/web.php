<?php

use App\Http\Controllers\PartnerRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});

Route::post('/partner-requests', [PartnerRequestController::class, 'store'])
    ->name('partner-requests.store');
