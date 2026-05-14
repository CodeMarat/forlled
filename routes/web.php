<?php

use App\Http\Controllers\PartnerRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});

Route::view('/api/docs', 'api.docs')
    ->name('api.docs');

Route::get('/api/openapi.json', function () {
    return response()->file(
        base_path('docs/openapi.json'),
        [
            'Content-Type' => 'application/json',
        ],
    );
})->name('api.openapi');

Route::post('/partner-requests', [PartnerRequestController::class, 'store'])
    ->name('partner-requests.store');
