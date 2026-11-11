<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeocodeUsSalons extends Command
{
    protected $signature = 'locations:geocode-us-salons {--limit=54} {--slug=} {--retry-failed}';

    protected $description = 'Store Google address Place IDs for US salons without storing Google-derived coordinates';

    public function handle(): int
    {
        $key = config('services.google_maps.geocoding_key');

        if (blank($key)) {
            $this->error('GOOGLE_MAPS_GEOCODING_KEY is required.');

            return self::FAILURE;
        }

        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);

        if ($limit === false) {
            $this->error('--limit must be an integer between 1 and 1000.');

            return self::FAILURE;
        }

        $salons = Location::query()
            ->whereNull('google_address_place_id')
            ->whereNull('latitude')
            ->whereNull('longitude')
            ->when(! $this->option('retry-failed'), fn ($query) => $query->whereNull('place_lookup_attempted_at'))
            ->when($this->option('slug'), fn ($query) => $query->where('slug', $this->option('slug')))
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();

        $resolved = 0;
        $unresolved = 0;

        foreach ($salons as $salon) {
            try {
                $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $salon->address.', USA',
                    'components' => 'country:US',
                    'key' => $key,
                ]);
            } catch (Throwable $exception) {
                $this->warn("{$salon->company}: {$exception->getMessage()}");

                return self::FAILURE;
            }

            if (! $response->successful() || in_array($response->json('status'), ['OVER_QUERY_LIMIT', 'REQUEST_DENIED'], true)) {
                $this->error("Google Geocoding failed for {$salon->company}: ".$response->json('status', 'HTTP '.$response->status()));

                return self::FAILURE;
            }

            $result = $response->json('results.0');
            $placeId = is_array($result) ? ($result['place_id'] ?? null) : null;

            if (is_string($placeId) && $placeId !== '' && ! ($result['partial_match'] ?? false) && ($result['geometry']['location_type'] ?? null) !== 'APPROXIMATE') {
                $salon->google_address_place_id = $placeId;
                $resolved++;
            } else {
                $this->warn("{$salon->company}: no precise match; review the address manually.");
                $unresolved++;
            }

            $salon->place_lookup_attempted_at = now();
            $salon->save();
        }

        $this->info("Place IDs resolved: {$resolved}; unresolved: {$unresolved}.");

        return self::SUCCESS;
    }
}
