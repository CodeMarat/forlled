<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Locations\VisitorRegionResolver;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VisitorRegionController extends Controller
{
    public function __invoke(Request $request, Factory $http): JsonResponse
    {
        $ip = $request->ip();
        $cacheKey = 'visitor-region:'.hash('sha256', (string) $ip);
        $region = Cache::get($cacheKey);

        if ($region === null) {
            $region = (new VisitorRegionResolver($http, config('services.ipinfo.token')))->resolve($ip);

            if ($region !== null) {
                Cache::put($cacheKey, $region, now()->addDay());
            }
        }

        return response()->json([
            'region' => $region,
            'cache_ttl_seconds' => $region === null ? 0 : 86400,
        ])->header('Cache-Control', 'private, no-store');
    }
}
