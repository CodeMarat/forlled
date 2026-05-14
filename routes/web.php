<?php

use App\Http\Controllers\PartnerRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});

Route::view('/api/docs', 'api.docs')
    ->name('api.docs');

Route::get('/api/openapi.json', function (Request $request) {
    /** @var array<string, mixed> $spec */
    $spec = json_decode(
        (string) file_get_contents(base_path('docs/openapi.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $spec['servers'] = [[
        'url' => $request->schemeAndHttpHost().'/api/v1',
        'description' => 'Current application',
    ]];

    return response()->json($spec);
})->name('api.openapi');

Route::post('/partner-requests', [PartnerRequestController::class, 'store'])
    ->name('partner-requests.store');
