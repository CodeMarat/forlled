<?php

namespace App\Support\Locations;

use Illuminate\Http\Client\Factory;
use Throwable;

class VisitorRegionResolver
{
    public function __construct(private Factory $http, private ?string $token) {}

    public function resolve(?string $ip = '192.168.0.1'): ?string
    {
        if (! $this->token || ! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        try {
            $response = $this->http->withToken($this->token)
                ->timeout(3)
                ->get('https://api.ipinfo.io/lite/'.$ip);

            if (! $response->successful()) {
                return null;
            }

            $countryCode = $response->json('country_code');

            if (! is_string($countryCode) || ! preg_match('/^[A-Z]{2}$/', $countryCode)) {
                return null;
            }

            return $countryCode === 'US' ? 'us' : 'world';
        } catch (Throwable) {
            return null;
        }
    }
}
